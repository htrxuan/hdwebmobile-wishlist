<?php

namespace htrxuan\hdwish;

if (!defined('ABSPATH')) {
    exit;
}

class HDWISH_Ajax
{

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
        add_action('wp_ajax_hdwish_toggle', array($this, 'toggle'));
        add_action('wp_ajax_nopriv_hdwish_toggle', array($this, 'toggle'));
        add_action('wp_ajax_hdwish_remove', array($this, 'remove'));
        add_action('wp_ajax_nopriv_hdwish_remove', array($this, 'remove'));
    }

    public function toggle()
    {
        check_ajax_referer('hdwish_frontend_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;

        if (!$product_id || !get_post($product_id) || 'product' !== get_post_type($product_id)) {
            wp_send_json_error(array('message' => __('Invalid product.', 'hdwebmobile-wishlist')));
        }

        $result = HDWISH_Storage::get_instance()->toggle($product_id);

        wp_send_json_success($result);
    }

    public function remove()
    {
        check_ajax_referer('hdwish_frontend_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;

        if (!$product_id) {
            wp_send_json_error(array('message' => __('Invalid product.', 'hdwebmobile-wishlist')));
        }

        $count = HDWISH_Storage::get_instance()->remove($product_id);

        wp_send_json_success(array('count' => $count));
    }
}
