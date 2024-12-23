<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      2.0.0
 *
 * @package    Honeypot_Wp_Comment
 * @subpackage Honeypot_Wp_Comment/admin
 * @author     Prasidhda <pranuk.prwnsi@gmail.com>
 */
class Honeypot_Wp_Comment_Admin {

	private $plugin_name;

	private $version;

	private $honeypot_wp_comment_settings;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->load_dependencies();

	}

	/**
	 * Show the DB update notices
	 *
	 * @since    2.0.0
	 * @access   public
	 */
	public function maybe_db_update() {
		if ( get_option( 'honeypot_wp_comment_db_version', false ) == false ) { ?>
            <div class="error fade">
                <h3><?php _e( 'Honeypot for WP Comment requires database update.', 'honeypot-for-wp-comment' ) ?> </h3>
                <p class="alignleft">
                    <a class="button button-primary"
                       href="<?php echo add_query_arg( '_honeypot_wp_comment', wp_create_nonce( 'db_update' ), admin_url( 'admin.php?page=honeypot-wp-comment-settings' ) ); ?>"
                       target="_self"><?php _e( 'Run the updater', 'honeypot-for-wp-comment' ); ?></a>
                </p>
                <div class="clear"></div>
            </div>
			<?php
		}
	}

	/**
	 * When updating from 1.0.0,
	 * Need to create the new DB table for logs
	 * Need to set the empty plugin settings
	 * @return bool
	 *
	 * @since    2.0.0
	 * @access   public
	 */
	public function handle_db_update() {
		if ( ! isset( $_GET['_honeypot_wp_comment'] ) ) {
			return false;
		}
		if ( ! wp_verify_nonce( $_GET['_honeypot_wp_comment'], 'db_update' ) ) {
			return false;
		}

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
		add_action( 'admin_notices', array( $this, 'db_update_success_message' ), 0 );

	}

	/**
	 * Show the DB update success message
	 *
	 * @since    2.0.0
	 * @access   public
	 */
	public function db_update_success_message() {
		if ( get_option( 'honeypot_wp_comment_db_version', false ) == false ) {
			update_option( 'honeypot_wp_comment_db_version', HONEYPOT_WP_COMMENT_VERSION );
			?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e( '"Honeypot for WP Comment" data update complete. Thank you for updating to the latest version!', 'honeypot-for-wp-comment' ); ?></p>
            </div>
			<?php
		}
	}

	/**
	 * Load dependencies
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( HONEYPOT_WP_COMMENT_FILE ) . 'admin/class-settings-api.php';
		require_once plugin_dir_path( HONEYPOT_WP_COMMENT_FILE ) . 'admin/class-honeypot-wp-comment-settings.php';
		require_once plugin_dir_path( HONEYPOT_WP_COMMENT_FILE ) . 'admin/class-honeypot-wp-comment-logs-table.php';

		$this->honeypot_wp_comment_settings = new Honeypot_Wp_Comment_Settings();
	}

	/**
	 * @param $actions
	 *
	 * @return array
	 *
	 * @since    2.0.0
	 * @access   public
	 */
	public function plugin_action_links( $actions ) {
		$new_actions = array(
			'settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=honeypot-wp-comment-settings' ) ) . '" aria-label="' . esc_attr( __( 'Honeypot for WP comment settngs.', 'honeypot-for-wp-comment' ) ) . '">' . __( 'Settings', 'honeypot-for-wp-comment' ) . '</a>',
		);

		return array_merge( $new_actions, $actions );
	}

	/**
	 * Setting init
	 *
	 * @since    2.0.0
	 * @access   public
	 */
	public function admin_init() {
		$this->honeypot_wp_comment_settings->admin_init();
	}

	/**
	 * Setting menu init
	 *
	 * @since    2.0.0
	 * @access   public
	 */
	public function admin_menu() {
		$this->honeypot_wp_comment_settings->admin_menu();
	}

}
