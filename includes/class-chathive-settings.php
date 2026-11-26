<?php
/**
 * The plugin's settings: one option, read and cleaned in one place.
 *
 * @package ChatHive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads, cleans and answers questions about the saved settings.
 */
final class ChatHive_Settings {

	const OPTION = 'chathive_settings';

	/** Where the chatbot is shown: on every page. */
	const MODE_ALL = 'all';

	/** Where the chatbot is shown: only where the block or shortcode is placed. */
	const MODE_SELECTED = 'selected';

	/**
	 * The address of the ChatHive service the widget talks to. Developers and
	 * self-hosters can set CHATHIVE_API_URL in wp-config.php.
	 */
	const DEFAULT_API_URL = 'https://api.chathive.ca';

	/** The ChatHive website: sign-up, dashboard, terms, privacy. */
	const DEFAULT_APP_URL = 'https://chathive.ca';

	/**
	 * The settings nobody has changed yet.
	 *
	 * @return array{site_id: string, mode: string}
	 */
	public static function defaults() {
		return array(
			'site_id' => '',
			'mode'    => self::MODE_ALL,
		);
	}

	/**
	 * The saved settings, with defaults for anything missing.
	 *
	 * @return array{site_id: string, mode: string}
	 */
	public static function get() {
		$saved = get_option( self::OPTION, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return self::clean( $saved );
	}

	/**
	 * A Site ID is "site_" followed by at least six letters, digits, "_" or "-"
	 * (the same rule the ChatHive service uses).
	 *
	 * @param string $value Text to check.
	 * @return bool
	 */
	public static function is_valid_site_id( $value ) {
		return is_string( $value ) && 1 === preg_match( '/^site_[A-Za-z0-9_-]{6,}$/', $value );
	}

	/**
	 * Cleans whatever was submitted into settings that are safe to save and use.
	 * A Site ID that is not valid is dropped, so a typo never turns the chat on.
	 *
	 * @param mixed $input Submitted values.
	 * @return array{site_id: string, mode: string}
	 */
	public static function clean( $input ) {
		$defaults = self::defaults();
		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		$site_id = isset( $input['site_id'] ) ? trim( sanitize_text_field( (string) $input['site_id'] ) ) : '';
		$mode    = isset( $input['mode'] ) ? (string) $input['mode'] : $defaults['mode'];

		return array(
			'site_id' => self::is_valid_site_id( $site_id ) ? $site_id : '',
			'mode'    => in_array( $mode, array( self::MODE_ALL, self::MODE_SELECTED ), true ) ? $mode : $defaults['mode'],
		);
	}

	/**
	 * Is there a usable Site ID? Until there is, the plugin contacts nobody and
	 * adds nothing to the site.
	 *
	 * @return bool
	 */
	public static function is_configured() {
		$settings = self::get();
		return '' !== $settings['site_id'];
	}

	/**
	 * The service address, without a trailing slash.
	 *
	 * @return string
	 */
	public static function api_url() {
		$url = defined( 'CHATHIVE_API_URL' ) ? (string) CHATHIVE_API_URL : self::DEFAULT_API_URL;
		return untrailingslashit( esc_url_raw( $url ) );
	}

	/**
	 * The ChatHive website address, without a trailing slash.
	 *
	 * @return string
	 */
	public static function app_url() {
		$url = defined( 'CHATHIVE_APP_URL' ) ? (string) CHATHIVE_APP_URL : self::DEFAULT_APP_URL;
		return untrailingslashit( esc_url_raw( $url ) );
	}
}
