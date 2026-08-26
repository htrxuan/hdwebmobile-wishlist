( function () {
	'use strict';

	if ( typeof window.hdwishParams === 'undefined' ) {
		return;
	}

	var params = window.hdwishParams;

	function postAjax( action, data ) {
		var body = new URLSearchParams( Object.assign( { action: action, nonce: params.nonce }, data ) );

		return fetch( params.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
		} ).then( function ( response ) {
			return response.json();
		} );
	}

	function setButtonState( button, inWishlist ) {
		button.classList.toggle( 'is-in-wishlist', inWishlist );
		button.setAttribute( 'aria-pressed', inWishlist ? 'true' : 'false' );
		var label = button.querySelector( '.hdwish-button__label' );
		if ( label ) {
			label.textContent = inWishlist
				? button.getAttribute( 'data-label-remove' )
				: button.getAttribute( 'data-label-add' );
		}
	}

	// Keeps the header Wishlist Count block's badge (if placed on the page) in sync with
	// every toggle/remove response, the same way WooCommerce's own Mini-Cart badge updates
	// without a page reload.
	function updateNavBadges( count ) {
		if ( typeof count === 'undefined' ) {
			return;
		}
		document.querySelectorAll( '[data-hdwish-nav-badge]' ).forEach( function ( badge ) {
			badge.textContent = count;
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.hdwish-button' );
		if ( button ) {
			event.preventDefault();
			var productId = button.getAttribute( 'data-product-id' );
			button.disabled = true;

			postAjax( 'hdwish_toggle', { product_id: productId } )
				.then( function ( response ) {
					if ( response && response.success ) {
						setButtonState( button, response.data.in_wishlist );
						updateNavBadges( response.data.count );
					}
				} )
				.finally( function () {
					button.disabled = false;
				} );
			return;
		}

		var removeButton = event.target.closest( '.hdwish-remove' );
		if ( removeButton ) {
			event.preventDefault();
			var row = removeButton.closest( '.hdwish-table__row' );
			var removeId = removeButton.getAttribute( 'data-product-id' );

			postAjax( 'hdwish_remove', { product_id: removeId } ).then( function ( response ) {
				if ( response && response.success ) {
					if ( row ) {
						row.remove();
					}
					updateNavBadges( response.data.count );
				}
			} );
			return;
		}

		var addToCartButton = event.target.closest( '.hdwish-add-to-cart' );
		if ( addToCartButton ) {
			event.preventDefault();
			addToCart( addToCartButton );
		}
	} );

	/**
	 * Adds a product to cart via the real Store API (not a classic redirect), so the
	 * Mini-Cart block's own client-side state stays correct without a full page reload.
	 * The Store API signs requests with its own Nonce header (not a wp_create_nonce()
	 * action) -- fetched fresh from a GET request before the first cart mutation, the same
	 * pattern the official Store API client uses.
	 */
	var storeApiNonce = null;

	function getStoreApiNonce() {
		if ( storeApiNonce ) {
			return Promise.resolve( storeApiNonce );
		}

		return fetch( params.cartUrl, { credentials: 'same-origin' } ).then( function ( response ) {
			storeApiNonce = response.headers.get( 'Nonce' ) || response.headers.get( 'X-WC-Store-API-Nonce' );
			return storeApiNonce;
		} );
	}

	function addToCart( button ) {
		var productId = button.getAttribute( 'data-product-id' );
		button.disabled = true;

		getStoreApiNonce()
			.then( function ( nonce ) {
				var headers = { 'Content-Type': 'application/json' };
				if ( nonce ) {
					headers.Nonce = nonce;
				}

				return fetch( params.cartAddItemUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: headers,
					body: JSON.stringify( { id: parseInt( productId, 10 ), quantity: 1 } ),
				} );
			} )
			.then( function ( response ) {
				return response.json().then( function ( data ) {
					return { ok: response.ok, data: data };
				} );
			} )
			.then( function ( result ) {
				if ( result.ok ) {
					// Notify any listening cart UI (Mini-Cart block and classic
					// fragment-based themes both listen for one of these).
					document.body.dispatchEvent(
						new CustomEvent( 'wc-blocks_added_to_cart', { detail: { preserveCartData: true } } )
					);
					if ( window.jQuery ) {
						window.jQuery( document.body ).trigger( 'added_to_cart' );
					}
				} else if ( result.data && result.data.message ) {
					window.alert( result.data.message.replace( /<[^>]*>/g, '' ) );
				}
			} )
			.finally( function () {
				button.disabled = false;
			} );
	}
} )();
