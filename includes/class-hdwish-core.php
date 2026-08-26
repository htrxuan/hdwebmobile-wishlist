<?php

namespace htrxuan\hdwish;

if (!defined('ABSPATH')) {
    exit;
}

final class HDWISH_Core
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
        $this->includes();
        $this->init_hooks();
    }

    private function __clone()
    {
    }

    private function includes()
    {
        require_once HDWISH_PLUGIN_DIR . 'includes/class-hdwish-storage.php';
        require_once HDWISH_PLUGIN_DIR . 'includes/class-hdwish-page-installer.php';
        require_once HDWISH_PLUGIN_DIR . 'includes/class-hdwish-ajax.php';
        require_once HDWISH_PLUGIN_DIR . 'includes/class-hdwish-frontend.php';
        require_once HDWISH_PLUGIN_DIR . 'includes/class-hdwish-shortcode.php';
        require_once HDWISH_PLUGIN_DIR . 'includes/class-hdwish-block.php';
        require_once HDWISH_PLUGIN_DIR . 'includes/class-hdwish-nav-block.php';

        // Required unconditionally (not just in is_admin()) because HDWISH_Admin::get_options()
        // is also read by Frontend/Ajax on regular front-end requests; only the settings-page
        // hook registration in init_hooks() below is actually gated to admin context.
        require_once HDWISH_PLUGIN_DIR . 'includes/class-hdwish-admin.php';
    }

    private function init_hooks()
    {
        add_action('admin_notices', array($this, 'render_missing_woocommerce_notice'));

        if (!class_exists('WooCommerce')) {
            return;
        }

        HDWISH_Storage::get_instance();
        HDWISH_Frontend::get_instance();
        HDWISH_Ajax::get_instance();
        HDWISH_Shortcode::get_instance();
        HDWISH_Block::get_instance();
        HDWISH_Nav_Block::get_instance();

        if (is_admin()) {
            HDWISH_Admin::get_instance();
        }
    }

    public function render_missing_woocommerce_notice()
    {
        $screen = get_current_screen();
        if (!$screen || 'plugins' !== $screen->id) {
            return;
        }

        if (!get_transient('hdwish_wc_missing_notice')) {
            return;
        }
        delete_transient('hdwish_wc_missing_notice');
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php esc_html_e('HDWebmobile Wishlist requires WooCommerce to be installed and active. The plugin has been deactivated.', 'hdwebmobile-wishlist'); ?>
            </p>
        </div>
        <?php
    }
}
