<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://prasidhda.com.np
 * @since      2.0.0
 *
 * @package    Honeypot_Wp_Comment
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
// Drop the log table
global $wpdb;
$table = $wpdb->prefix . 'honeypot_wp_comments_logs';
$sql   = "DROP TABLE IF EXISTS $table";
$wpdb->query( $sql );

// Delete the options
$plugin_options = array(
	'honeypot_basics',
	'honeypot_advanced',
	'honeypot_wp_comment_db_version',
);

foreach ( $plugin_options as $option ) {
	delete_option( $option );
	delete_site_option( $option );
}


