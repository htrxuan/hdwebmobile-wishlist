( function ( blocks, element, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;

	// Static placeholder in the editor -- the real markup (icon and/or text, per the
	// "Header display" setting) is server-rendered on the front end.
	blocks.registerBlockType( 'hdwish/wishlist-count', {
		apiVersion: 3,
		title: __( 'Wishlist Count', 'hdwebmobile-wishlist' ),
		description: __(
			'Shows a heart icon and the visitor’s wishlist item count.',
			'hdwebmobile-wishlist'
		),
		icon: 'heart',
		category: 'woocommerce',
		edit: function () {
			return el(
				'div',
				{ className: 'hdwish-block-editor-placeholder' },
				el( 'span', { className: 'hdwish-block-editor-placeholder__icon' }, '♥' ),
				el( 'span', { className: 'hdwish-block-editor-placeholder__label' }, '2' )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.i18n );
