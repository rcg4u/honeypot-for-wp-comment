<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * class to handle the Logs
 * Class Honeypot_WP_Comment_Logs_Handler
 *
 * @since      2.0.0
 *
 * @package    Honeypot_Wp_Comment
 * @subpackage Honeypot_Wp_Comment/includes
 * @author     Prasidhda <pranuk.prwnsi@gmail.com>
 */
class Honeypot_WP_Comment_Logs_Handler {
	public $table = 'honeypot_wp_comments_logs';

	/**
	 * Function to add the log in table
	 *
	 * @param array $data
	 *
	 * @since    2.0.0
	 */
	public function add_log( $data = array() ) {

		global $wpdb;
		$response = $wpdb->insert(
			$wpdb->prefix . $this->table,
			$data
		);

	}

	/**
	 * Delete a log record.
	 *
	 * @param int $id ID
	 *
	 * @since    2.0.0
	 */
	public function delete_log( $id ) {
		global $wpdb;
		$wpdb->delete( "{$wpdb->prefix}{$this->table}", array( 'id' => $id ), array( '%d' ) );
	}
}