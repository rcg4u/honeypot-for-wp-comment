<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Main Settings class
 *
 * @since      2.0.0
 *
 * @package    Honeypot_Wp_Comment
 * @subpackage Honeypot_Wp_Comment/admin
 * @author     Prasidhda <pranuk.prwnsi@gmail.com>
 */
if ( ! class_exists( 'Honeypot_Wp_Comment_Settings' ) ):
	class Honeypot_Wp_Comment_Settings {
		private $settings_api;

		function __construct() {
			$this->settings_api = new Honeypot_Settings_API();
		}

		/**
		 * Add setting sections and
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function admin_init() {

			//set the settings
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );

			//initialize settings
			$this->settings_api->admin_init();
		}

		/**
		 * Add setting menu
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function admin_menu() {
			add_menu_page( __( 'Honeypot', 'honeypot-for-wp-comment' ), __( 'Honeypot', 'honeypot-for-wp-comment' ), 'manage_options', 'honeypot-wp-comment-settings', '', 'dashicons-buddicons-replies', 26 );
			add_submenu_page( 'honeypot-wp-comment-settings', __( 'Settings', 'honeypot-for-wp-comment' ), __( 'Settings', 'honeypot-for-wp-comment' ),
				'manage_options', 'honeypot-wp-comment-settings', array( $this, 'plugin_page' ), 1 );
			add_submenu_page( 'honeypot-wp-comment-settings', __( 'Logs', 'honeypot-for-wp-comment' ), __( 'Logs', 'honeypot-for-wp-comment' ),
				'manage_options', 'honeypot-wp-comment-logs', array( $this, 'render_logs' ), 2 );
		}

		/**
		 * @return array[]
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function get_settings_sections() {
			return array(
				array(
					'id'    => 'honeypot_basics',
					'class' => 'active',
					'title' => __( 'Basic Settings', 'honeypot-for-wp-comment' ),
				),
				array(
					'id'    => 'honeypot_advanced',
					'title' => __( 'Advanced Settings', 'honeypot-for-wp-comment' ),
				)
			);
		}

		/**
		 * Returns all the settings fields
		 *
		 * @return array settings fields
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function get_settings_fields() {
			return array(
				'honeypot_basics'   => array(
					array(
						'name'    => 'enabled',
						'label'   => __( 'Enable / Disable', 'honeypot-for-wp-comment' ),
						'desc'    => __( 'Please check this to enable the honeypot', 'honeypot-for-wp-comment' ),
						'type'    => 'checkbox',
						'default' => 'on',
					),
					array(
						'name'  => 'input_fields',
						'label' => __( 'Input Fields', 'honeypot-for-wp-comment' ),
						'desc'  => __( 'Please put the input field name for honeypot. Please make sure to use very common names like <strong>"name", "phone","email", "confirm-email"</strong> etc.<br> If left empty, default fields "phone" & "confirm-email" will be used.<br> Example: <strong>name : Please enter your name</strong>.<br> Use new line for multiple field names.',
							'honeypot-for-wp-comment'
						),
						'type'  => 'textarea',
					),
					array(
						'name'    => 'log_enabled',
						'label'   => __( 'Enable Log', 'honeypot-for-wp-comment' ),
						'desc'    => __( 'Check this to enable the log.', 'honeypot-for-wp-comment' ),
						'type'    => 'checkbox',
						'default' => 'on'
					)
				),
				'honeypot_advanced' => array(
					array(
						'name'    => 'comment_status',
						'label'   => __( 'Comment status', 'honeypot-for-wp-comment' ),
						'desc'    => __( 'Please select the comment status to be set for trapped spam comments. <br>This setting will only work if you disable the <strong>"Block Completely"</strong> feature.', 'honeypot-for-wp-comment' ),
						'type'    => 'select',
						'options' => array(
							'spam'  => __( 'Spam', 'honeypot-for-wp-comment' ),
							'trash' => __( 'Trash', 'honeypot-for-wp-comment' )
						)
					),
					array(
						'name'  => 'restricted_email_domains',
						'label' => __( 'Email domain restriction', 'honeypot-for-wp-comment' ),
						'desc'  => __( 'Please put the email domain without "@".<br> Example: <strong>"mailinator.com"</strong>. <br>You can put multi domains in new line.', 'honeypot-for-wp-comment' ),
						'type'  => 'textarea'
					),
					array(
						'name'    => 'ip_restriction_enabled',
						'label'   => __( 'Enable IP restriction', 'honeypot-for-wp-comment' ),
						'desc'    => __( 'Check this to enable the IP restriction. By enabling this, It will add the commenter\'s IP to the restriction list if trapped as spam.', 'honeypot-for-wp-comment' ),
						'type'    => 'checkbox',
						'default' => 'on'
					),
					array(
						'name'  => 'restricted_ips',
						'label' => __( 'IP restriction', 'honeypot-for-wp-comment' ),
						'desc'  => __( 'Please put the IP address to be blocked from commenting.You can manually put multi IPs in new line.', 'honeypot-for-wp-comment' ),
						'type'  => 'textarea'
					),
					array(
						'name'    => 'common_spam_words_restriction',
						'label'   => __( 'Common spam words restriction', 'honeypot-for-wp-comment' ),
						'desc'    => sprintf( __( 'Please check this to enable <a href="%s" target="_blank">common spam words </a> restriction.', 'honeypot-for-wp-comment' ), 'https://gist.github.com/prasidhda/13c9303be3cbc4228585a7f1a06040a3' ),
						'type'    => 'checkbox',
						'default' => 'off'
					),
					array(
						'name'  => 'additional_spam_words',
						'label' => __( 'Additional spam words', 'honeypot-for-wp-comment' ),
						'desc'  => __( 'Please put the additional spam words here. Please each words in new line.', 'honeypot-for-wp-comment' ),
						'type'  => 'textarea'
					),
					array(
						'name'    => 'comment_content_link_restriction',
						'label'   => __( 'Any link restriction', 'honeypot-for-wp-comment' ),
						'desc'    => __( 'Please check this to enable the comment restriction if comment contains any link', 'honeypot-for-wp-comment' ),
						'type'    => 'checkbox',
						'default' => 'off'
					),
					array(
						'name'    => 'block_completely',
						'label'   => __( 'Block completely', 'honeypot-for-wp-comment' ),
						'desc'    => __( 'By enabling this, spam comments will not be saved in WP comments database.', 'honeypot-for-wp-comment' ),
						'type'    => 'checkbox',
						'default' => 'off'
					),
				),
			);

		}

		/**
		 * Render setting form
		 *
		 * @since  2.0.0
		 * @access public
		 */
		function plugin_page() {
			require_once plugin_dir_path( HONEYPOT_WP_COMMENT_FILE ) . 'admin/partials/settings-form.php';
		}

		/**
		 * Function to render the logs
		 *
		 * @since  2.0.0
		 * @access public
		 */
		public function render_logs() {
			$logs = new Honeypot_Wp_Comment_Logs_Table();
			$logs->prepare_items();
			?>
            <div class="wrap">
                <form method="post">
                    <h2><?php _e( 'Logs of Honeypot trapped comments.', 'honeypot-for-wp-comment' ) ?></h2>
					<?php $logs->display(); ?>
                </form>
            </div>
			<?php
		}
	}
endif;