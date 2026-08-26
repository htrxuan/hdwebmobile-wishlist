<?php

namespace htrxuan\hdwish;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Auto-creates the "Wishlist" page on activation, the same way WooCommerce itself
 * auto-creates the Cart/Checkout/My Account pages.
 */
class HDWISH_Page_Installer
{

    public static function maybe_create_page()
    {
        $existing_id = (int) get_option('hdwish_wishlist_page_id');

        if ($existing_id && 'publish' === get_post_status($existing_id)) {
            return $existing_id;
        }

        $existing = get_posts(
            array(
                'post_type'   => 'page',
                'post_status' => array('publish', 'draft', 'pending', 'private'),
                's'           => '[hdwish_wishlist]',
                'numberposts' => 1,
                'fields'      => 'ids',
            )
        );

        if (!empty($existing)) {
            update_option('hdwish_wishlist_page_id', $existing[0]);
            return $existing[0];
        }

        $page_id = wp_insert_post(
            array(
                'post_title'   => __('Wishlist', 'hdwebmobile-wishlist'),
                // Wrapped in an alignwide group so the table renders at the theme's wider
                // content width (matching WooCommerce's own Cart/My Account pages) instead
                // of the narrow default text column a bare shortcode string would fall back
                // to on block themes -- confirmed live that a raw shortcode alone renders at
                // ~645px here versus ~1000px for WooCommerce's own pages, causing the table
                // to wrap awkwardly.
                'post_content' => "<!-- wp:group {\"align\":\"wide\"} -->\n<div class=\"wp-block-group alignwide\">[hdwish_wishlist]</div>\n<!-- /wp:group -->",
                'post_status'  => 'publish',
                'post_type'    => 'page',
            )
        );

        if ($page_id && !is_wp_error($page_id)) {
            update_option('hdwish_wishlist_page_id', $page_id);
        }

        return $page_id;
    }
}
