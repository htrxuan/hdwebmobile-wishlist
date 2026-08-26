<?php

namespace htrxuan\hdwish;

if (!defined('ABSPATH')) {
    exit;
}

class HDWISH_Frontend
{

    private static $instance = null;

    /**
     * Tracks which product IDs have already had an automatic button rendered during this
     * request. WooCommerce's Product Collection block can run its "Legacy Template" classic
     * hooks (woocommerce_after_shop_loop_item) on the very same product card where the
     * wishlist-button block is also inserted -- confirmed live in this environment's Site
     * Editor -- so without this guard the button would render twice per product. Manual
     * placements ([hdwish_button], the wishlist page rows) call render_button_html()
     * directly and are intentionally not deduped here.
     */
    private static $auto_rendered = array();

    /**
     * Guards wp_localize_script() so it only ever runs once per request -- both the normal
     * wp_enqueue_scripts hook below and the wishlist-count block's render() (which can't rely
     * on that hook since header/template-part blocks render outside the usual page-type
     * checks) call into localize_script_once().
     */
    private static $localized = false;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Fires on this environment's real single-product template (confirmed live: the
        // "Add to Cart + Options" block wraps the classic single_add_to_cart_button markup
        // and its surrounding classic hooks).
        add_action('woocommerce_after_add_to_cart_button', array($this, 'render_single_product_button'));

        // Classic shop-loop hook, kept for themes/templates that still use the classic PHP
        // loop instead of the block-based Product Collection (confirmed live in this
        // environment: the Shop archive is block-based and this hook never fires there --
        // the wishlist-button block is what covers that case instead).
        add_action('woocommerce_after_shop_loop_item', array($this, 'render_loop_button'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function render_single_product_button()
    {
        global $product;

        if (!$product instanceof \WC_Product) {
            return;
        }

        echo self::render_auto_button_once($product->get_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_button_html() escapes its own output.
    }

    public function render_loop_button()
    {
        global $product;

        if (!$product instanceof \WC_Product) {
            return;
        }

        echo self::render_auto_button_once($product->get_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_button_html() escapes its own output.
    }

    /**
     * Shared by the classic hooks above and the wishlist-button block's render callback --
     * whichever fires first for a given product ID in this request wins, so a Product
     * Collection template that keeps both the classic Legacy Template compatibility and the
     * dedicated block doesn't show the button twice on the same card.
     */
    public static function render_auto_button_once($product_id)
    {
        $product_id = absint($product_id);

        if (isset(self::$auto_rendered[$product_id])) {
            return '';
        }

        self::$auto_rendered[$product_id] = true;

        return self::render_button_html($product_id);
    }

    /**
     * The single shared button renderer -- used by the classic hooks above, the
     * [hdwish_button] shortcode, and the wishlist-button block's server render callback,
     * so the markup only lives in one place.
     */
    public static function render_button_html($product_id)
    {
        $product_id = absint($product_id);

        if (!$product_id) {
            return '';
        }

        $options     = HDWISH_Admin::get_options();
        $in_wishlist = in_array($product_id, HDWISH_Storage::get_instance()->get_products(), true);
        $label       = $in_wishlist ? $options['button_text_remove'] : $options['button_text_add'];

        return sprintf(
            '<button type="button" class="hdwish-button%s" data-product-id="%d" data-label-add="%s" data-label-remove="%s" aria-pressed="%s">
                <span class="hdwish-button__icon" aria-hidden="true"></span>
                <span class="hdwish-button__label">%s</span>
            </button>',
            $in_wishlist ? ' is-in-wishlist' : '',
            $product_id,
            esc_attr($options['button_text_add']),
            esc_attr($options['button_text_remove']),
            $in_wishlist ? 'true' : 'false',
            esc_html($label)
        );
    }

    public function enqueue_assets()
    {
        if (!function_exists('is_woocommerce') || (!is_woocommerce() && !is_product() && !self::is_wishlist_page())) {
            return;
        }

        wp_enqueue_style('hdwish-frontend-css', HDWISH_PLUGIN_URL . 'assets/css/hdwish-frontend.css', array(), self::asset_version('assets/css/hdwish-frontend.css'));
        wp_enqueue_script('hdwish-frontend-js', HDWISH_PLUGIN_URL . 'assets/js/hdwish-frontend.js', array(), self::asset_version('assets/js/hdwish-frontend.js'), true);

        self::localize_script_once();
    }

    /**
     * Uses the file's own last-modified time as the cache-busting version instead of the
     * static plugin version -- so every CSS/JS edit is picked up by browsers immediately on
     * the next request, without needing a manual version bump for what are otherwise
     * frequently-iterated frontend assets.
     */
    public static function asset_version($relative_path)
    {
        $path = HDWISH_PLUGIN_DIR . $relative_path;
        return file_exists($path) ? (string) filemtime($path) : HDWISH_VERSION;
    }

    public static function localize_script_once()
    {
        if (self::$localized) {
            return;
        }
        self::$localized = true;

        wp_localize_script('hdwish-frontend-js', 'hdwishParams', array(
            'ajaxUrl'     => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('hdwish_frontend_nonce'),
            'wishlistUrl' => self::get_wishlist_page_url(),
            // The Store API manages its own request-signing nonce via a response header
            // (not a wp_create_nonce() action) -- the frontend JS fetches a fresh one from
            // the Store API itself before its first cart request, per the official client's
            // own pattern, rather than guessing a nonce action name here.
            'cartAddItemUrl' => esc_url_raw(rest_url('wc/store/v1/cart/add-item')),
            'cartUrl'        => esc_url_raw(rest_url('wc/store/v1/cart')),
        ));
    }

    public static function is_wishlist_page()
    {
        $page_id = (int) HDWISH_Admin::get_options()['wishlist_page_id'];
        return $page_id && is_page($page_id);
    }

    public static function get_wishlist_page_url()
    {
        $page_id = (int) HDWISH_Admin::get_options()['wishlist_page_id'];
        return $page_id ? get_permalink($page_id) : '';
    }
}
