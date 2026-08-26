<?php

namespace htrxuan\hdwish;

if (!defined('ABSPATH')) {
    exit;
}

class HDWISH_Activator
{

    public static function activate()
    {
        if (!self::is_woocommerce_active()) {
            deactivate_plugins(plugin_basename(HDWISH_PLUGIN_FILE));
            set_transient('hdwish_wc_missing_notice', true, 30);
            return;
        }

        require_once HDWISH_PLUGIN_DIR . 'includes/class-hdwish-page-installer.php';
        HDWISH_Page_Installer::maybe_create_page();
    }

    public static function is_woocommerce_active()
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active('woocommerce/woocommerce.php') || class_exists('WooCommerce');
    }
}
