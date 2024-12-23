<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Main class for public side functionalities
 *
 * @since      2.0.0
 *
 * @package    Honeypot_Wp_Comment
 * @subpackage Honeypot_Wp_Comment/public
 * @author     Prasidhda <pranuk.prwnsi@gmail.com>
 */
class Honeypot_Wp_Comment_Public {

	private $plugin_name;

	private $version;

	public $honeypot_settings;

	public $honeypot_fields;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->honeypot_settings = array(
			'basics'   => get_option( 'honeypot_basics', false ),
			'advanced' => get_option( 'honeypot_advanced', false ),
		);

		$this->get_honeypot_fields();
	}

	/**
	 * Get honeypot input fields
	 *
	 * @since    2.0.0
	 */
	public function get_honeypot_fields() {
		// Default setting fields
		$this->honeypot_fields = array(
			'phone'         => __( 'Enter your phone' ),
			'confirm-email' => __( 'Confirm your email' ),
		);
		$basic_settings        = $this->honeypot_settings['basics'];

		if ( $basic_settings != false || $basic_settings != '' ) {

			if ( $basic_settings['enabled'] == 'on' ) {
				if ( $basic_settings['input_fields'] != '' ) {
					$this->honeypot_fields = array(); // Reset
					$honeypot_fields_raw   = explode( PHP_EOL, $basic_settings['input_fields'] );

					foreach ( $honeypot_fields_raw as $index => $setting_field ) {
						if ( trim( $setting_field ) == '' ) {
							continue;
						}
						$temp                                      = explode( ':', $setting_field );
						$this->honeypot_fields[ trim( $temp[0] ) ] = isset( $temp[1] ) ? trim( $temp[1] ) : '';
					}
				}
			}

		}

	}

	/**
	 * Render honeypot styles
	 * It is deliberately being hooked here instead of wp_enqueue_scripts
	 *
	 * @since    2.0.0
	 */
	public function render_style() {
		?>
        <style>
            .hpwc {
                opacity: 0;
                position: absolute;
                top: 0;
                left: 0;
                height: 0;
                width: 0;
                z-index: -1;
            }
        </style>
		<?php
	}

	/**
	 * Function to render the Trap HTML elements in comment form
	 *
	 * @since    2.0.0
	 */
	public function render_honeypot() {
		require_once plugin_dir_path( HONEYPOT_WP_COMMENT_FILE ) . '/public/partials/honeypot-fields.php';
	}

	/**
	 *  Function to check the honeypot value
	 * If any of the "honeypot" fields came filled. If yes, congrats, you trapped a spam.
	 *
	 * @param $approved
	 * @param $comment
	 *
	 * @return string
	 *
	 * @since    2.0.0
	 */
	public function filter_spam( $approved, $comment ) {
		// if honeypot feature is turned off, return the current comment status
		if ( $this->honeypot_settings['basics'] != false && $this->honeypot_settings['basics']['enabled'] == 'off' ) {
			return $approved;
		}
		$trapped = false;

		// This message wil be shown as a cover message to spam commenter
		$comment_duplicate_message = apply_filters( 'comment_duplicate_message', __( 'Duplicate comment detected; it looks as though you&#8217;ve already said that!', 'honeypot-for-wp-comment' ) );
		// Get the use defined trapped comment status
		// Default will be spam
		$trapped_status = 'spam';
		if ( isset( $this->honeypot_settings['advanced']['comment_status'] ) ) {
			$trapped_status = $this->honeypot_settings['advanced']['comment_status'];
		}

		// Get the completely blocked setting
		$block_completely = 'off';
		if ( isset( $this->honeypot_settings['advanced']['block_completely'] ) ) {
			$block_completely = $this->honeypot_settings['advanced']['block_completely'];
		}

		// Check for the IP restriction
		$ip_restriction_enabled = 'off';
		if ( isset( $this->honeypot_settings['advanced']['ip_restriction_enabled'] ) ) {
			$ip_restriction_enabled = 'on';
		}
		if ( $ip_restriction_enabled == 'on' ) {
			if ( $this->check_ip_restriction() ) {

				if ( $block_completely == 'on' ) {
					$this->log_trapped_comment( $comment, __( 'IP address restricted. Completely blocked.', 'honeypot-for-wp-comment' ) );

					return new WP_Error( 'comment_duplicate', $comment_duplicate_message, 409 );
				}

				$this->log_trapped_comment( $comment, sprintf( __( 'IP address restricted. Trapped as %s', 'honeypot-for-wp-comment' ), $trapped_status ) );

				return $trapped_status;
			}
		}

		// Check for the restricted email domains
		// Get restricted email domains
		$restricted_email_domains = $this->get_restricted_email_fields();
		if ( ! empty( $restricted_email_domains ) ) {
			// If email restriction found, throw an error
			if ( in_array( explode( '@', $comment['comment_author_email'] )[1], $restricted_email_domains ) ) {
				if ( $block_completely == 'on' ) {
					$this->log_trapped_comment( $comment, __( 'Email domain restricted. Completely blocked.', 'honeypot-for-wp-comment' ) );

					return new WP_Error( 'comment_duplicate', $comment_duplicate_message, 409 );
				}

				$this->log_trapped_comment( $comment, sprintf( __( 'Email domain restricted. Trapped as %s', 'honeypot-for-wp-comment' ), $trapped_status ) );
				$this->add_ip_to_restriction_list();

				return $trapped_status;
			}
		}

		// Check for link content
		$comment_content_link_restriction = 'off';
		if ( isset( $this->honeypot_settings['advanced']['comment_content_link_restriction'] ) ) {
			$comment_content_link_restriction = $this->honeypot_settings['advanced']['comment_content_link_restriction'];
		}
		if ( $comment_content_link_restriction == 'on' ) {
			preg_match( '/[a-zA-Z]+:\/\/[0-9a-zA-Z;.\/\-?:@=_#&%~,+$]+/', $comment['comment_content'], $matches );
			if ( ! empty( $matches ) ) {
				// If "block completely" enabled, thrown an error
				if ( $block_completely == 'on' ) {
					$this->log_trapped_comment( $comment, sprintf( __( 'URLs detected in comment <strong>%1$s</strong>. Completely blocked.', 'honeypot-for-wp-comment' ), implode( ', ', $matches ) ) );

					return new WP_Error( 'comment_duplicate', $comment_duplicate_message, 409 );
				}

				$this->log_trapped_comment( $comment, sprintf( __( 'URLs detected in comment <strong>%1$s</strong>. Trapped as %2$s', 'honeypot-for-wp-comment' ), implode( ', ', $matches ), $trapped_status ) );

				$trapped = true;
			}
		}

		// check for matched common spam words
		$caught_spam_words = $this->check_spam_words( $comment['comment_content'] );
		if ( ! empty( $caught_spam_words ) ) {
			// If "block completely" enabled, thrown an error
			if ( $block_completely == 'on' ) {
				$this->log_trapped_comment( $comment, sprintf( __( 'Spam words detected <strong>%1$s</strong>. Completely blocked.', 'honeypot-for-wp-comment' ), implode( ', ', $caught_spam_words ) ) );

				return new WP_Error( 'comment_duplicate', $comment_duplicate_message, 409 );
			}

			$this->log_trapped_comment( $comment, sprintf( __( 'Spam words detected <strong>%1$s</strong>. Trapped as %2$s', 'honeypot-for-wp-comment' ), implode( ', ', $caught_spam_words ), $trapped_status ) );
			$this->add_ip_to_restriction_list();

			return $trapped_status;
		}

		// Check for honeypot fields
		// If any trap found, return the set comment status
		foreach ( $this->honeypot_fields as $field_name => $field_placeholder ) {
			if ( ! empty( $_POST[ $field_name ] ) ) {
				// If "block completely" enabled, thrown an error
				if ( $block_completely == 'on' ) {
					$this->log_trapped_comment( $comment, __( 'Completely blocked.', 'honeypot-for-wp-comment' ) );

					return new WP_Error( 'comment_duplicate', $comment_duplicate_message, 409 );
				}

				$this->log_trapped_comment( $comment, sprintf( __( 'Trapped as %s', 'honeypot-for-wp-comment' ), $trapped_status ) );
				$this->add_ip_to_restriction_list();

				return $trapped_status;
			}
		}

		return $approved;
	}

	/**
	 * Return restricted email domains
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function get_restricted_email_fields() {
		if ( $this->honeypot_settings['advanced'] == false ) {
			return false;
		}
		$restricted_email_domains_raw = $this->honeypot_settings['advanced']['restricted_email_domains'];

		if ( $restricted_email_domains_raw == '' ) {
			return false;
		}

		return array_unique(
			array_map( 'trim', explode( PHP_EOL, $restricted_email_domains_raw ) )
		);
	}

	/**
	 * Check if the comment content contains the spam words
	 *
	 * @param $comment_content_raw
	 *
	 * @return   bool|array
	 *
	 * @since    2.1.0
	 * @access   private
	 */
	private function check_spam_words( $comment_content_raw ) {
		$common_spam_words = array();
		// Set the default common spam words only if it is set in setting
		$common_spam_words_restriction = 'off';
		if ( isset( $this->honeypot_settings['advanced']['common_spam_words_restriction'] ) ) {
			$common_spam_words_restriction = $this->honeypot_settings['advanced']['common_spam_words_restriction'];
		}
		if ( $common_spam_words_restriction == 'on' ) {
			$common_spam_words = Honeypot_Wp_Comment_Helper::get_spam_words();
		}
		// Check if there are custom added spam words
		$additional_spam_words_raw = isset( $this->honeypot_settings['advanced']['additional_spam_words'] ) ? $this->honeypot_settings['advanced']['additional_spam_words'] : '';
		if ( $additional_spam_words_raw != '' ) {
			$common_spam_words = array_map(
				function ( $val ) {
					return strtolower( trim( $val ) );
				},
				array_merge(
					$common_spam_words,
					explode( PHP_EOL, $additional_spam_words_raw )
				)
			);
		}

		// If there is no spam words set, return
		if ( empty( $common_spam_words ) ) {
			return false;
		}

		// Make array of comment content for finding match cases
		$comment_content = array_map( function ( $val ) {
			return strtolower( trim( $val ) );
		}, explode( ' ', $comment_content_raw ) );

		return array_values( array_intersect( $comment_content, $common_spam_words ) );
	}

	private function check_ip_restriction() {

		return in_array( Honeypot_Wp_Comment_Helper::get_ip_address(), $this->get_restricted_ips() );
	}

	/**
	 * Log trapped comment
	 *
	 * @param $comment
	 * @param $remarks
	 *
	 * @since    2.1.0
	 * @access   private
	 */
	private function log_trapped_comment( $comment, $remarks ) {
		// Initialize the Log Handler class if log is enabled
		// Log is enabled as default
		$log_handler = null;
		if ( $this->honeypot_settings['basics'] == false || $this->honeypot_settings['basics']['log_enabled'] != 'off' ) {
			$log_handler = new Honeypot_WP_Comment_Logs_Handler();

			$log_data = array(
				'comment_post'         => sprintf( '<a href="%1$s" target="_blank">%2$s</a>', get_permalink( $comment['comment_post_ID'] ), get_the_title( $comment['comment_post_ID'] ) ),
				'comment_author'       => $comment['comment_author'],
				'comment_author_email' => $comment['comment_author_email'],
				'comment_content'      => $comment['comment_content'],
				'comment_author_IP'    => $comment['comment_author_IP'],
				'comment_agent'        => $comment['comment_agent'],
				'comment_date'         => $comment['comment_date'],
			);
		}
		if ( $log_handler != null ) {
			$log_data['remarks'] = $remarks;
			$log_handler->add_log( $log_data );
		}
	}

	/**
	 * Get restricted IPs
	 *
	 * @return array
	 *
	 * @since    2.2.0
	 * @access   public
	 */
	public function get_restricted_ips() {
		return isset( $this->honeypot_settings['advanced']['restricted_ips'] ) ? array_map( 'trim', explode( PHP_EOL, $this->honeypot_settings['advanced']['restricted_ips'] ) ) : array();
	}

	/**
	 * Function to add the commenter's IP to restriction list
	 *
	 * @since    2.2.0
	 * @access   public
	 */
	private function add_ip_to_restriction_list() {
		$ip_restriction_enabled = 'off';
		if ( isset( $this->honeypot_settings['advanced']['ip_restriction_enabled'] ) ) {
			$ip_restriction_enabled = $this->honeypot_settings['advanced']['ip_restriction_enabled'];
		}
		if ( $ip_restriction_enabled == 'on' ) {
			$ip          = Honeypot_Wp_Comment_Helper::get_ip_address();
			$updated_ips = $this->get_restricted_ips();
			if ( ! in_array( $ip, $updated_ips ) ) {
				$updated_ips[] = $ip;
			}

			$updated_advanced_settings                   = $this->honeypot_settings['advanced'];
			$updated_advanced_settings['restricted_ips'] = implode( PHP_EOL, $updated_ips );

			update_option( 'honeypot_advanced', $updated_advanced_settings );
		}
	}

}
