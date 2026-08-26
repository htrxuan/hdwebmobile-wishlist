<?php

namespace htrxuan\hdwish;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers the wishlist-count block -- a heart icon + item count meant to sit in the
 * header next to WooCommerce's own Mini-Cart, the same way a store's cart icon shows a
 * badge. Placement is manual (Site Editor), matching how Mini-Cart itself has to be added
 * to a header template; there is no reliable way to detect "is this in the active header"
 * ahead of render, so assets are enqueued from render() itself rather than a global
 * wp_enqueue_scripts check, guaranteeing they load only when the block actually renders.
 */
class HDWISH_Nav_Block
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
        wp_register_script(
            'hdwish-wishlist-count-editor',
            HDWISH_PLUGIN_URL . 'blocks/wishlist-count/index.js',
            array('wp-blocks', 'wp-element', 'wp-i18n'),
            HDWISH_VERSION,
            true
        );

        register_block_type(
            HDWISH_PLUGIN_DIR . 'blocks/wishlist-count',
            array(
                'render_callback' => array($this, 'render'),
            )
        );
    }

    public function render()
    {
        wp_enqueue_style('hdwish-frontend-css', HDWISH_PLUGIN_URL . 'assets/css/hdwish-frontend.css', array(), HDWISH_Frontend::asset_version('assets/css/hdwish-frontend.css'));
        wp_enqueue_script('hdwish-frontend-js', HDWISH_PLUGIN_URL . 'assets/js/hdwish-frontend.js', array(), HDWISH_Frontend::asset_version('assets/js/hdwish-frontend.js'), true);
        HDWISH_Frontend::localize_script_once();

        $options = HDWISH_Admin::get_options();
        $count   = count(HDWISH_Storage::get_instance()->get_products());
        $url     = HDWISH_Frontend::get_wishlist_page_url();
        $mode    = $options['header_display_mode'];

        return sprintf(
            '<a href="%1$s" class="hdwish-nav-count hdwish-nav-count--%2$s" aria-label="%3$s">
                <span class="hdwish-nav-count__icon-wrap">
                    <span class="hdwish-nav-count__icon" aria-hidden="true">&#9825;</span>
                    <span class="hdwish-nav-count__badge" data-hdwish-nav-badge>%4$d</span>
                </span>
                %5$s
            </a>',
            esc_url($url),
            esc_attr($mode),
            esc_attr__('View your wishlist', 'hdwebmobile-wishlist'),
            $count,
            'text' === $mode ? '<span class="hdwish-nav-count__label">' . esc_html__('Wishlist', 'hdwebmobile-wishlist') . '</span>' : ''
        );
    }
}
