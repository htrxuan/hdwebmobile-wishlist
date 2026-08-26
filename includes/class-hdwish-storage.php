<?php

namespace htrxuan\hdwish;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The only class in this plugin that touches wishlist storage. Logged-in visitors are
 * stored in a single user meta key (mirrors how WooCommerce itself stores small per-user
 * lists -- no custom table needed for this). Guests are stored entirely in a first-party
 * cookie -- no server-side row at all for anonymous visitors. On login, the guest cookie
 * is merged into the account's wishlist and cleared, mirroring WooCommerce's own
 * cart-merge-on-login behavior.
 */
class HDWISH_Storage
{

    const META_KEY  = '_hdwish_products';
    const COOKIE_KEY = 'hdwish_wishlist';

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('wp_login', array($this, 'merge_guest_into_user'), 10, 2);
    }

    public function get_products()
    {
        if (is_user_logged_in()) {
            $products = get_user_meta(get_current_user_id(), self::META_KEY, true);
            return is_array($products) ? array_map('absint', $products) : array();
        }

        return $this->get_guest_products();
    }

    public function toggle($product_id)
    {
        $product_id = absint($product_id);
        $products   = $this->get_products();
        $index      = array_search($product_id, $products, true);

        if (false !== $index) {
            unset($products[$index]);
            $products  = array_values($products);
            $in_wishlist = false;
        } else {
            $products[]  = $product_id;
            $in_wishlist = true;
        }

        $this->save_products($products);

        return array(
            'in_wishlist' => $in_wishlist,
            'count'       => count($products),
        );
    }

    public function remove($product_id)
    {
        $product_id = absint($product_id);
        $products   = array_values(array_diff($this->get_products(), array($product_id)));
        $this->save_products($products);
        return count($products);
    }

    private function save_products($products)
    {
        $products = array_values(array_unique(array_map('absint', $products)));

        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), self::META_KEY, $products);
            return;
        }

        $this->set_guest_cookie($products);
    }

    private function get_guest_products()
    {
        if (empty($_COOKIE[self::COOKIE_KEY])) {
            return array();
        }

        $cookie_value = sanitize_text_field(wp_unslash($_COOKIE[self::COOKIE_KEY]));
        $raw          = explode(',', $cookie_value);
        return array_values(array_unique(array_filter(array_map('absint', $raw))));
    }

    private function set_guest_cookie($products)
    {
        $days  = HDWISH_Admin::get_options()['guest_cookie_days'];
        $value = implode(',', $products);
        $expire = empty($products) ? time() - DAY_IN_SECONDS : time() + ($days * DAY_IN_SECONDS);

        setcookie(self::COOKIE_KEY, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true);
        $_COOKIE[self::COOKIE_KEY] = $value;
    }

    public function merge_guest_into_user($user_login, $user)
    {
        $guest_products = $this->get_guest_products();

        if (empty($guest_products)) {
            return;
        }

        $existing = get_user_meta($user->ID, self::META_KEY, true);
        $existing = is_array($existing) ? array_map('absint', $existing) : array();
        $merged   = array_values(array_unique(array_merge($existing, $guest_products)));

        update_user_meta($user->ID, self::META_KEY, $merged);

        setcookie(self::COOKIE_KEY, '', time() - DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true);
        unset($_COOKIE[self::COOKIE_KEY]);
    }
}
