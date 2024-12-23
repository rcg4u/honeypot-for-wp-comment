<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Fired during plugin activation.
 *
 * @since      2.0.0
 *
 * @package    Honeypot_Wp_Comment
 * @subpackage Honeypot_Wp_Comment/includes
 * @author     Prasidhda <pranuk.prwnsi@gmail.com>
 */
class Honeypot_Wp_Comment_Activator {

	/**
	 * Create the custom DB table for logs
	 *
	 *
	 * @since  2.0.0
	 * @access public
	 */
	public static function activate() {

		global $wpdb;

		$table           = $wpdb->prefix . 'honeypot_wp_comments_logs';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            comment_post text NOT NULL,
            comment_author varchar(255) DEFAULT '' NOT NULL,
            comment_author_email varchar(255) DEFAULT '' NOT NULL,
            comment_content text NOT NULL,
            comment_author_IP varchar(255) DEFAULT '' NOT NULL,
            comment_agent varchar(255) DEFAULT '' NOT NULL,
            remarks varchar(255) DEFAULT '' NOT NULL,
            comment_date varchar(255) DEFAULT '' NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";


		$wpdb->query( $sql );

		update_option( 'honeypot_wp_comment_db_version', HONEYPOT_WP_COMMENT_VERSION );
	}

}
