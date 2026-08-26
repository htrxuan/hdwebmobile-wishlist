<?php

namespace htrxuan\hdwish;

if (!defined('ABSPATH')) {
    exit;
}

class HDWISH_Shortcode
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
        add_shortcode('hdwish_wishlist', array($this, 'render_wishlist_page'));
        add_shortcode('hdwish_button', array($this, 'render_button_shortcode'));
    }

    public function render_button_shortcode($atts)
    {
        $atts       = shortcode_atts(array('id' => 0), $atts, 'hdwish_button');
        $product_id = (int) $atts['id'];

        if (!$product_id) {
            global $product;

            if ($product instanceof \WC_Product) {
                $product_id = $product->get_id();
            } elseif (get_the_ID() && 'product' === get_post_type()) {
                $product_id = get_the_ID();
            }
        }

        return HDWISH_Frontend::render_button_html($product_id);
    }

    public function render_wishlist_page()
    {
        $product_ids = HDWISH_Storage::get_instance()->get_products();

        if (empty($product_ids)) {
            return '<p class="hdwish-empty">' . esc_html__('Your wishlist is empty.', 'hdwebmobile-wishlist') . '</p>';
        }

        $products = array_filter(array_map('wc_get_product', $product_ids));

        if (empty($products)) {
            return '<p class="hdwish-empty">' . esc_html__('Your wishlist is empty.', 'hdwebmobile-wishlist') . '</p>';
        }

        ob_start();
        ?>
        <table class="hdwish-table">
            <thead>
                <tr>
                    <th class="hdwish-table__col-product"><?php esc_html_e('Product', 'hdwebmobile-wishlist'); ?></th>
                    <th class="hdwish-table__col-price"><?php esc_html_e('Price', 'hdwebmobile-wishlist'); ?></th>
                    <th class="hdwish-table__col-stock"><?php esc_html_e('Stock', 'hdwebmobile-wishlist'); ?></th>
                    <th class="hdwish-table__col-actions"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product) : ?>
                    <?php $this->render_wishlist_row($product); ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    private function render_wishlist_row($product)
    {
        $is_variable   = $product->is_type('variable');
        $can_add_direct = !$is_variable && $product->is_purchasable() && $product->is_in_stock();
        ?>
        <tr class="hdwish-table__row" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
            <td class="hdwish-table__col-product">
                <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>" class="hdwish-table__link">
                    <?php echo wp_kses_post($product->get_image('thumbnail')); ?>
                    <span><?php echo esc_html($product->get_name()); ?></span>
                </a>
            </td>
            <td class="hdwish-table__col-price"><?php echo wp_kses_post($product->get_price_html()); ?></td>
            <td class="hdwish-table__col-stock"><?php echo esc_html(wc_get_stock_html($product) ? wp_strip_all_tags(wc_get_stock_html($product)) : ($product->is_in_stock() ? __('In stock', 'hdwebmobile-wishlist') : __('Out of stock', 'hdwebmobile-wishlist'))); ?></td>
            <td class="hdwish-table__col-actions">
                <?php if ($can_add_direct) : ?>
                    <button type="button" class="hdwish-add-to-cart button" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                        <?php esc_html_e('Add to Cart', 'hdwebmobile-wishlist'); ?>
                    </button>
                <?php else : ?>
                    <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>" class="button">
                        <?php esc_html_e('View Product', 'hdwebmobile-wishlist'); ?>
                    </a>
                <?php endif; ?>
                <button type="button" class="hdwish-remove" data-product-id="<?php echo esc_attr($product->get_id()); ?>" aria-label="<?php esc_attr_e('Remove from wishlist', 'hdwebmobile-wishlist'); ?>">&times;</button>
            </td>
        </tr>
        <?php
    }
}
