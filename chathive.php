<?php
/**
 * Plugin Name:       ChatHive – AI Chatbot for your website
 * Plugin URI:        https://chathive.ca/wordpress
 * Description:       Put your ChatHive AI chatbot on your WordPress site. It answers visitors from your own documents, collects leads and books meetings. Needs a ChatHive account.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      8.2
 * Author:            ChatHive
 * Author URI:        https://chathive.ca
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       chathive
 *
 * @package ChatHive
 */

// Nothing here runs outside WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CHATHIVE_VERSION', '1.0.0' );
define( 'CHATHIVE_FILE', __FILE__ );
define( 'CHATHIVE_DIR', plugin_dir_path( __FILE__ ) );

require_once CHATHIVE_DIR . 'includes/class-chathive-settings.php';
require_once CHATHIVE_DIR . 'includes/class-chathive-embed.php';
require_once CHATHIVE_DIR . 'includes/class-chathive-block.php';
require_once CHATHIVE_DIR . 'includes/class-chathive-admin.php';

ChatHive_Embed::init();
ChatHive_Block::init();

if ( is_admin() ) {
	ChatHive_Admin::init();
}
