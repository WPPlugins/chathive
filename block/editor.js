/**
 * The ChatHive Chat block in the editor: a note, because the chat itself is a
 * floating button that only shows on the published page.
 */
( function ( blocks, element, blockEditor, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;
	var settings = window.chathiveBlock || { configured: false, settingsUrl: '' };

	blocks.registerBlockType( 'chathive/chat', {
		edit: function () {
			var props = blockEditor.useBlockProps( { className: 'chathive-block' } );
			var children = [
				el( 'strong', { key: 'title' }, __( 'ChatHive Chat', 'chathive' ) ),
				el(
					'p',
					{ key: 'text' },
					__( 'The chat button appears at the bottom of this page when it is published.', 'chathive' )
				),
			];
			if ( ! settings.configured ) {
				children.push(
					el(
						'p',
						{ key: 'setup' },
						el(
							'a',
							{ href: settings.settingsUrl },
							__( 'Enter your ChatHive Site ID to turn it on.', 'chathive' )
						)
					)
				);
			}
			return el( 'div', props, children );
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.i18n );
