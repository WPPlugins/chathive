<?php
/**
 * The "ChatHive Chat" block: puts the chat on the page that holds it.
 *
 * @package ChatHive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block. It prints nothing; the chat is a floating button.
 */
final class ChatHive_Block {

	/**
	 * Hooks the block into WordPress.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Registers the block and gives its editor script the plugin's state.
	 *
	 * @return void
	 */
	public static function register() {
		register_block_type(
			CHATHIVE_DIR . 'block',
			array(
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);

		wp_localize_script(
			'chathive-chat-editor-script',
			'chathiveBlock',
			array(
				'configured'  => ChatHive_Settings::is_configured(),
				'settingsUrl' => admin_url( 'options-general.php?page=chathive' ),
			)
		);
	}

	/**
	 * Adds the widget to this page. The block has no visible output.
	 *
	 * @return string
	 */
	public static function render() {
		ChatHive_Embed::enqueue();
		return ChatHive_Embed::MARKER;
	}
}
