<?php
/**
 * Plugin Name:       Honeypot for WP Comment
 * Plugin URI:        https://wordpress.org/plugins/honeypot-for-wp-comment/
 * Description:       Simple plugin to trap the spam comments using honeypot technique.
 * Version:           2.2.3
 * Author:            Prasidhda Malla
 * Author URI:        https://prasidhda.com.np
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       honeypot-for-wp-comment
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Define constants
 *
 * @since    2.0.0
 */
define( 'HONEYPOT_WP_COMMENT_VERSION', '2.2.3' );
define( 'HONEYPOT_WP_COMMENT_FILE', __FILE__ );
/**
 * The code that runs during plugin activation.
 *
 * @since    2.0.0
 */
function activate_honeypot_wp_comment() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-honeypot-wp-comment-activator.php';
	Honeypot_Wp_Comment_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_honeypot_wp_comment' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-honeypot-wp-comment.php';

/**
 * Begins execution of the plugin.
 *
 * @since    2.0.0
 */
function run_honeypot_wp_comment() {

	$plugin = new Honeypot_Wp_Comment();
	$plugin->run();

}

run_honeypot_wp_comment();



