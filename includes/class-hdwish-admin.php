<?php

namespace htrxuan\hdwish;

if (!defined('ABSPATH')) {
    exit;
}

class HDWISH_Admin
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
        require_once HDWISH_PLUGIN_DIR . 'includes/class-hdwish-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));
        add_action('admin_init', array($this, 'page_init'));
    }

    public function register_hub_tabs($tabs)
    {
        $tabs['wishlist'] = array(
            'label'  => __('Wishlist', 'hdwebmobile-wishlist'),
            'order'  => 130,
            'render' => array($this, 'render_settings_page'),
        );
        return $tabs;
    }

    public function render_settings_page()
    {
        ?>
        <p><?php esc_html_e('A WooCommerce wishlist that actually works with block-based Shop pages, guest browsing, and the Mini-Cart block.', 'hdwebmobile-wishlist'); ?></p>
        <form method="post" action="options.php">
            <?php
            settings_fields('hdwish_option_group');
            do_settings_sections('hdwish-settings');
            submit_button();
            ?>
        </form>
        <?php
    }

    public function page_init()
    {
        register_setting(
            'hdwish_option_group',
            'hdwish_options',
            array(
                'type'              => 'array',
                'sanitize_callback' => array($this, 'sanitize'),
                'default'           => array(),
            )
        );

        add_settings_section(
            'hdwish_section_general',
            __('General', 'hdwebmobile-wishlist'),
            '__return_false',
            'hdwish-settings'
        );

        add_settings_field('enabled', __('Enable Wishlist', 'hdwebmobile-wishlist'), array($this, 'enabled_callback'), 'hdwish-settings', 'hdwish_section_general');
        add_settings_field('guest_cookie_days', __('Guest wishlist remembered for (days)', 'hdwebmobile-wishlist'), array($this, 'guest_cookie_days_callback'), 'hdwish-settings', 'hdwish_section_general');
        add_settings_field('button_text_add', __('"Add to wishlist" button text', 'hdwebmobile-wishlist'), array($this, 'button_text_add_callback'), 'hdwish-settings', 'hdwish_section_general');
        add_settings_field('button_text_remove', __('"Remove from wishlist" button text', 'hdwebmobile-wishlist'), array($this, 'button_text_remove_callback'), 'hdwish-settings', 'hdwish_section_general');
        add_settings_field('wishlist_page_id', __('Wishlist page', 'hdwebmobile-wishlist'), array($this, 'wishlist_page_callback'), 'hdwish-settings', 'hdwish_section_general');
        add_settings_field('header_display_mode', __('Header "Wishlist Count" block', 'hdwebmobile-wishlist'), array($this, 'header_display_mode_callback'), 'hdwish-settings', 'hdwish_section_general');
    }

    public static function get_options()
    {
        $defaults = array(
            'enabled'             => 1,
            'guest_cookie_days'   => 30,
            'button_text_add'     => 'Add to Wishlist',
            'button_text_remove'  => 'Remove from Wishlist',
            'wishlist_page_id'    => (int) get_option('hdwish_wishlist_page_id'),
            'header_display_mode' => 'icon',
        );

        return wp_parse_args(get_option('hdwish_options', array()), $defaults);
    }

    public function sanitize($input)
    {
        $new_input = array();

        $new_input['enabled']            = isset($input['enabled']) ? 1 : 0;
        $new_input['guest_cookie_days']   = isset($input['guest_cookie_days']) ? max(1, absint($input['guest_cookie_days'])) : 30;
        $new_input['button_text_add']     = isset($input['button_text_add']) ? sanitize_text_field($input['button_text_add']) : __('Add to Wishlist', 'hdwebmobile-wishlist');
        $new_input['button_text_remove']  = isset($input['button_text_remove']) ? sanitize_text_field($input['button_text_remove']) : __('Remove from Wishlist', 'hdwebmobile-wishlist');
        $new_input['wishlist_page_id']    = isset($input['wishlist_page_id']) ? absint($input['wishlist_page_id']) : 0;
        $new_input['header_display_mode'] = isset($input['header_display_mode']) && 'text' === $input['header_display_mode'] ? 'text' : 'icon';

        return $new_input;
    }

    public function enabled_callback()
    {
        $options = self::get_options();
        printf(
            '<input type="checkbox" name="hdwish_options[enabled]" value="1" %s />',
            checked(1, $options['enabled'], false)
        );
    }

    public function guest_cookie_days_callback()
    {
        $options = self::get_options();
        printf(
            '<input type="number" min="1" name="hdwish_options[guest_cookie_days]" value="%s" class="small-text" />',
            esc_attr($options['guest_cookie_days'])
        );
    }

    public function button_text_add_callback()
    {
        $options = self::get_options();
        printf(
            '<input type="text" name="hdwish_options[button_text_add]" value="%s" class="regular-text" />',
            esc_attr($options['button_text_add'])
        );
    }

    public function button_text_remove_callback()
    {
        $options = self::get_options();
        printf(
            '<input type="text" name="hdwish_options[button_text_remove]" value="%s" class="regular-text" />',
            esc_attr($options['button_text_remove'])
        );
    }

    public function header_display_mode_callback()
    {
        $options = self::get_options();
        $mode    = $options['header_display_mode'];
        ?>
        <fieldset>
            <label>
                <input type="radio" name="hdwish_options[header_display_mode]" value="icon" <?php checked('icon', $mode); ?> />
                <?php esc_html_e('Icon only (heart + count)', 'hdwebmobile-wishlist'); ?>
            </label>
            <br />
            <label>
                <input type="radio" name="hdwish_options[header_display_mode]" value="text" <?php checked('text', $mode); ?> />
                <?php esc_html_e('Icon + "Wishlist" text', 'hdwebmobile-wishlist'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('Controls the Wishlist Count block. Add it to your header template (Appearance > Editor) next to the Mini-Cart to show visitors how many products they\'ve saved.', 'hdwebmobile-wishlist'); ?>
            </p>
        </fieldset>
        <?php
    }

    public function wishlist_page_callback()
    {
        $options = self::get_options();
        wp_dropdown_pages(
            array(
                'name'             => 'hdwish_options[wishlist_page_id]',
                'selected'         => absint($options['wishlist_page_id']),
                'show_option_none' => esc_html__('&mdash; Select a page &mdash;', 'hdwebmobile-wishlist'),
            )
        );
    }
}
