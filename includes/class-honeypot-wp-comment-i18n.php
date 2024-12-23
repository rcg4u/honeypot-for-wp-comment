<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      2.0.0
 *
 * @package    Honeypot_Wp_Comment
 * @subpackage Honeypot_Wp_Comment/includes
 * @author     Prasidhda <pranuk.prwnsi@gmail.com>
 */
class Honeypot_Wp_Comment_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since  2.0.0
	 * @access public
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'honeypot-for-wp-comment',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}


}
