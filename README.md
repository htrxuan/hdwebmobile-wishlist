# HDWebmobile Wishlist

A WooCommerce wishlist that actually works with block-based Shop pages, guest browsing, and the Mini-Cart block.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-wishlist/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

HDWebmobile Wishlist lets visitors save products to a wishlist and buy them later. It's built specifically to work with modern WooCommerce block themes: it ships a real Gutenberg block you can insert directly into your Shop page's Product Collection template, so the "Add to Wishlist" button actually appears on block-based archives — not just classic PHP-loop themes, which is where several other wishlist plugins fall short. Guests get a wishlist too, stored in a cookie with no account required, and it merges automatically into their account the moment they log in. Adding a saved product to cart from the wishlist page goes through WooCommerce's real Store API, so the Mini-Cart block updates instantly without a page reload.

Recent WooCommerce versions ship an experimental, opt-in "Wishlists" feature (WooCommerce > Settings > Features) — it's off by default, requires the Add to Cart + Options block, and only works for logged-in customers. This plugin works immediately with no feature flag to enable, supports guests with automatic merge-on-login, and also works on classic (non-block) themes.

## Features

* Real Gutenberg block for the wishlist button — insert it into the Product Collection template in the Site Editor so it shows on block-based Shop/archive pages, not just single product pages
* Classic-theme compatible too: also hooks the traditional single-product and shop-loop actions for non-block themes
* Guest wishlists via cookie, no account required, with automatic merge into the account wishlist on login
* "Add to Cart" from the wishlist page uses the real WooCommerce Store API, keeping the Mini-Cart block's total in sync without a full page reload
* Auto-creates a dedicated Wishlist page on activation, the same way WooCommerce creates its own Cart/Checkout pages
* `[hdwish_wishlist]` and `[hdwish_button]` shortcodes for manual placement anywhere on your site — `[hdwish_button]` auto-detects the current product when used on a product page or in the loop, or takes an explicit `id="123"` anywhere else
* A header "Wishlist Count" block — a heart icon with a live item-count badge, just like WooCommerce's own Mini-Cart. Insert it into your header template next to the Mini-Cart, and choose icon-only or icon + "Wishlist" text under WooCommerce > Wishlist
* Configurable button text and guest cookie duration under WooCommerce > Wishlist

## Development

Standard WordPress plugin structure:

```
hdwebmobile-wishlist.php    Bootstrap
includes/class-hdwish-activator.php
includes/class-hdwish-admin.php
includes/class-hdwish-ajax.php
includes/class-hdwish-block.php
includes/class-hdwish-core.php
includes/class-hdwish-frontend.php
includes/class-hdwish-hub.php
includes/class-hdwish-nav-block.php
includes/class-hdwish-page-installer.php
includes/class-hdwish-shortcode.php
includes/class-hdwish-storage.php
```

Part of the [HDWebmobile](https://hdwebmobile.com/plugins/) suite of focused, single-purpose WooCommerce plugins.

## License

GPLv2 or later. See [LICENSE](LICENSE).

