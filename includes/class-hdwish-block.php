<?php

namespace htrxuan\hdwish;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers the wishlist-button block -- the actual fix for wishlist buttons never
 * appearing on block-based Shop/archive pages (confirmed live in this environment that
 * classic loop hooks never fire there). Declares the same usesContext/ancestor pair
 * WooCommerce's own Product Button block uses, confirmed directly from WooCommerce core's
 * assets/client/blocks/product-button/block.json, so it slots into the Product Collection
 * template the same correct way WooCommerce's own per-product blocks do.
 */
class HDWISH_Block
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
        add_action('init', array($this, 'register_block'));
    }

    public function register_block()
    {
        // Registered manually (no bundler/asset.php in this codebase) so block.json can
        // reference this handle by name instead of a "file:" path requiring a build step.
        wp_register_script(
            'hdwish-wishlist-button-editor',
            HDWISH_PLUGIN_URL . 'blocks/wishlist-button/index.js',
            array('wp-blocks', 'wp-element', 'wp-i18n'),
            HDWISH_VERSION,
            true
        );

        register_block_type(
            HDWISH_PLUGIN_DIR . 'blocks/wishlist-button',
            array(
                'render_callback' => array($this, 'render'),
            )
        );
    }

    public function render($attributes, $content, $block)
    {
        $product_id = isset($block->context['postId']) ? absint($block->context['postId']) : 0;

        if (!$product_id) {
            return '';
        }

        return HDWISH_Frontend::render_auto_button_once($product_id);
    }
}
