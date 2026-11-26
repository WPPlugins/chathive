<?php
/**
 * Removes what the plugin saved when it is deleted from the Plugins screen.
 *
 * @package ChatHive
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'chathive_settings' );
