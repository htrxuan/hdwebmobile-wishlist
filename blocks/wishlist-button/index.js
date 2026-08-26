( function ( blocks, element, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;

	// The editor preview intentionally stays a simple static placeholder rather than a
	// ServerSideRender call: this block's usesContext/postId is resolved by WordPress core's
	// own dynamic-block rendering pipeline on the real frontend (render_callback in PHP),
	// which is what actually matters for shoppers. Verified live during build that this
	// placeholder is enough to drag-and-drop the block into the Product Collection template
	// in the Site Editor -- the real button only needs to be correct on the front end.
	blocks.registerBlockType( 'hdwish/wishlist-button', {
		apiVersion: 3,
		title: __( 'Wishlist Button', 'hdwebmobile-wishlist' ),
		description: __(
			'Adds an Add to Wishlist button for the current product.',
			'hdwebmobile-wishlist'
		),
		icon: 'heart',
		category: 'woocommerce-product-elements',
		usesContext: [ 'postId', 'query', 'queryId' ],
		ancestor: [
			'woocommerce/product-template',
			'woocommerce/single-product',
			'core/post-template',
		],
		edit: function () {
			return el(
				'div',
				{ className: 'hdwish-block-editor-placeholder' },
				el( 'span', { className: 'hdwish-block-editor-placeholder__icon' }, '♥' ),
				el(
					'span',
					{ className: 'hdwish-block-editor-placeholder__label' },
					__( 'Add to Wishlist', 'hdwebmobile-wishlist' )
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.i18n );
