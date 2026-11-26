<?php
/**
 * Puts the ChatHive widget on the site's pages.
 *
 * The widget is the same two scripts a ChatHive customer would paste by hand:
 * a small configuration with the Site ID, and widget.js from the ChatHive
 * service. Nothing is added, and nothing contacts ChatHive, until a Site ID has
 * been saved.
 *
 * @package ChatHive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the widget to the front end, on every page or only where it is placed.
 */
final class ChatHive_Embed {

	const HANDLE = 'chathive-widget';

	/**
	 * Hooks the plugin into WordPress.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_everywhere' ) );
		add_shortcode( 'chathive', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * "Every page" mode: add the widget to each page.
	 *
	 * @return void
	 */
	public static function maybe_enqueue_everywhere() {
		$settings = ChatHive_Settings::get();
		if ( ChatHive_Settings::MODE_ALL === $settings['mode'] ) {
			self::enqueue();
		}
	}

	/**
	 * What the shortcode and the block print: an HTML comment, nothing visible
	 * (the chat is a floating button). It is not empty on purpose: since
	 * WordPress 6.9 the scripts a block enqueues are dropped when the block
	 * prints nothing.
	 */
	const MARKER = '<!-- ChatHive chat -->';

	/**
	 * The [chathive] shortcode: add the widget to the page that holds it.
	 *
	 * @return string
	 */
	public static function shortcode() {
		self::enqueue();
		return self::MARKER;
	}

	/**
	 * Adds the widget scripts to the page once.
	 *
	 * @return void
	 */
	public static function enqueue() {
		if ( ! ChatHive_Settings::is_configured() ) {
			return;
		}
		if ( is_admin() || is_feed() || is_customize_preview() ) {
			return;
		}
		/**
		 * Lets a site turn the chat off on some pages (for example a checkout).
		 *
		 * @param bool $load Whether to load the widget on this request.
		 */
		if ( ! apply_filters( 'chathive_should_load', true ) ) {
			return;
		}
		if ( wp_script_is( self::HANDLE, 'enqueued' ) ) {
			return;
		}

		$settings = ChatHive_Settings::get();
		$api_url  = ChatHive_Settings::api_url();

		// The widget script comes from the ChatHive service, as the readme says.
		// No version number: the service sends the current script.
		wp_register_script(
			self::HANDLE,
			$api_url . '/widget.js',
			array(),
			null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			array(
				'in_footer' => true,
				'strategy'  => 'async',
			)
		);
		wp_add_inline_script(
			self::HANDLE,
			'window.ChatHiveWidgetConfig = ' . wp_json_encode(
				array(
					'siteId'  => $settings['site_id'],
					'apiBase' => $api_url,
				)
			) . ';',
			'before'
		);
		wp_enqueue_script( self::HANDLE );
	}
}
