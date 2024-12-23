<?php
/**
 * Plugin Name:       Honeypot for WP Comment
 * Plugin URI:        https://wordpress.org/plugins/honeypot-for-wp-comment/
 * Description:       Simple plugin to trap the spam comments using honeypot technique.
 * Version:           2.2.5
 * Author:            narcolepticnerd
 * Author URI:        https://narcolepticnerd.com
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
define( 'HONEYPOT_WP_COMMENT_VERSION', '2.2.5' );
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

/**
 * Secure file deletion function
 *
 * @since    2.2.4
 */
function secure_file_deletion() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'honeypot-for-wp-comment' ) ); // Escaped output
    }

    if ( ! isset( $_GET['file'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'delete_file' ) ) {
        wp_die( esc_html__( 'Invalid request.', 'honeypot-for-wp-comment' ) ); // Escaped output
    }

    $file = basename( sanitize_file_name( wp_unslash( $_GET['file'] ) ) ); // Enhanced sanitization

    // Ensure the file is within the allowed directory
    $allowed_dir = plugin_dir_path( __FILE__ ) . 'uploads/';
    $file_path   = realpath( $allowed_dir . $file );

    // Check file extension to allow only specific types
    $allowed_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'pdf' );
    $file_extension = pathinfo( $file_path, PATHINFO_EXTENSION );
    if ( ! in_array( strtolower( $file_extension ), $allowed_extensions, true ) ) {
        wp_die( esc_html__( 'Invalid file type.', 'honeypot-for-wp-comment' ) ); // Escaped output
    }

    if ( strpos( $file_path, $allowed_dir ) !== 0 || ! file_exists( $file_path ) ) {
        wp_die( esc_html__( 'Invalid file path.', 'honeypot-for-wp-comment' ) ); // Escaped output
    }

    unlink( $file_path );
    wp_redirect( admin_url( 'admin.php?page=honeypot-wp-comment-settings' ) );
    exit;
}

add_action( 'admin_post_delete_file', 'secure_file_deletion' );
