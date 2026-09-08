=== HDWebmobile Wishlist ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, wishlist, favorites, save for later, save products
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WooCommerce wishlist that actually works with block-based Shop pages, guest browsing, and the Mini-Cart block.

== Description ==

HDWebmobile Wishlist lets visitors save products to a wishlist and buy them later. It's built specifically to work with modern WooCommerce block themes: it ships a real Gutenberg block you can insert directly into your Shop page's Product Collection template, so the "Add to Wishlist" button appears on block-based archives, not just single product pages. It also works on classic (non-block) themes via the traditional template hooks. Guests get a wishlist too, stored in a cookie with no account required, and it merges automatically into their account the moment they log in. Adding a saved product to cart from the wishlist page goes through WooCommerce's real Store API, so the Mini-Cart block updates instantly without a page reload.

Recent WooCommerce versions ship an experimental, opt-in "Wishlists" feature (WooCommerce > Settings > Features) — it's off by default, requires the Add to Cart + Options block, and only works for logged-in customers. This plugin works immediately with no feature flag to enable, supports guests with automatic merge-on-login, and also works on classic (non-block) themes.

= Key Features =
* Real Gutenberg block for the wishlist button — insert it into the Product Collection template in the Site Editor so it shows on block-based Shop/archive pages, not just single product pages
* Classic-theme compatible too: also hooks the traditional single-product and shop-loop actions for non-block themes
* Guest wishlists via cookie, no account required, with automatic merge into the account wishlist on login
* "Add to Cart" from the wishlist page uses the real WooCommerce Store API, keeping the Mini-Cart block's total in sync without a full page reload
* Auto-creates a dedicated Wishlist page on activation, the same way WooCommerce creates its own Cart/Checkout pages
* `[hdwish_wishlist]` and `[hdwish_button]` shortcodes for manual placement anywhere on your site — `[hdwish_button]` auto-detects the current product when used on a product page or in the loop, or takes an explicit `id="123"` anywhere else
* A header "Wishlist Count" block — a heart icon with a live item-count badge, just like WooCommerce's own Mini-Cart. Insert it into your header template next to the Mini-Cart, and choose icon-only or icon + "Wishlist" text under WooCommerce > Wishlist
* Configurable button text and guest cookie duration under WooCommerce > Wishlist

= Limitations (please read before installing) =
* One wishlist per visitor — no named/multiple wishlists and no "share my wishlist" link in this version
* For variable products, the wishlist stores the parent product only; "Add to Cart" for a variable product links to the product page to choose options rather than guessing a variation
* Guest wishlists are stored in a browser cookie — if a visitor blocks or clears cookies, their guest wishlist won't persist between visits (logging in avoids this entirely)
* No "most wishlisted products" analytics dashboard in this version

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-wishlist` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. A "Wishlist" page is created automatically on activation. Configure button text and guest cookie duration under WooCommerce > Wishlist.

== How to Use ==

= 1. Turn on the settings you want =
Go to **WooCommerce > Wishlist** in your admin menu. Set how long a guest's wishlist should be remembered, customize the "Add to Wishlist" / "Remove from Wishlist" button text, and confirm which page hosts the `[hdwish_wishlist]` shortcode (Screenshot 1).

= 2. The button on single product pages =
No setup needed — the wishlist heart button appears automatically under the Add to Cart area on every single product page (Screenshot 2). Visitors, including guests, click it to toggle the product in or out of their wishlist.

= 3. Add the button to your Shop page (block themes) =
Classic shop-loop themes get the button automatically. If your Shop page uses the block-based Product Collection (the WordPress default for new stores), open the Site Editor, edit the Product Collection's product template, and insert the **Wishlist Button** block from the WooCommerce category (Screenshot 3). It uses the same product context as WooCommerce's own Product Price and Add to Cart blocks, so it automatically renders correctly for every product card.

= 4. Guest wishlists and merging on login =
Visitors don't need an account to build a wishlist — it's stored in their browser. If they later log in or register, anything in their guest wishlist is automatically merged into their account wishlist, and the guest cookie is cleared.

= 5. The Wishlist page =
Visitors view their saved products, prices, and stock status on the auto-created Wishlist page (Screenshot 4). Clicking **Add to Cart** adds the product straight to their real WooCommerce cart via the Store API — the Mini-Cart block updates immediately, with no page reload. Clicking **Remove** takes the product off the list instantly.

= 6. Variable products =
If a saved product has variations (size, color, etc.), its wishlist row shows a **View Product** link instead of a direct Add to Cart button, so the visitor can pick the exact variation they want.

= 7. Show a wishlist count in your header =
Open the Site Editor, edit your header template, and insert the **Wishlist Count** block (WooCommerce category) next to your Mini-Cart. It shows a heart icon with a live badge of how many products the visitor has saved, and updates instantly whenever they add or remove one — no page reload, the same way the Mini-Cart itself updates. Choose between icon-only or icon + "Wishlist" text under WooCommerce > Wishlist.

== Screenshots ==

1. The Wishlist settings screen under WooCommerce > Wishlist.
2. The wishlist heart button on a single product page.
3. Inserting the Wishlist Button block into the Product Collection template in the Site Editor.
4. The Wishlist page showing saved products with Add to Cart and Remove actions.
5. The header Wishlist Count block, showing a live item-count badge next to the Mini-Cart.

== Changelog ==

= 1.0.0 =
* Initial release: block-theme-compatible wishlist button block, classic hook fallback, guest cookie wishlist with merge-on-login, Store-API-driven add to cart, auto-created Wishlist page, configurable settings.
