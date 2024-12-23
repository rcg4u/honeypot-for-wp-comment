<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * The core plugin class.
 *
 * @since      2.0.0
 *
 * @package    Honeypot_Wp_Comment
 * @subpackage Honeypot_Wp_Comment/includes
 * @author     Prasidhda <pranuk.prwnsi@gmail.com>
 */
final class Honeypot_Wp_Comment {

	protected $loader;

	protected $plugin_name;

	protected $version;

	public function __construct() {
		if ( defined( 'Honeypot_Wp_Comment_VERSION' ) ) {
			$this->version = Honeypot_Wp_Comment_VERSION;
		} else {
			$this->version = '2.2.3';
		}
		$this->plugin_name = 'honeypot-for-wp-comment';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Include dependent files
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-honeypot-wp-comment-loader.php';

		/**
		 * The class responsible for logs
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-honeypot-wp-comment-logs-handler.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-honeypot-wp-comment-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-honeypot-wp-comment-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing side of the site
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-honeypot-wp-comment-public.php';

		/**
		 * The class responsible for helpers function
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-honeypot-wp-comment-helper.php';

		$this->loader = new Honeypot_Wp_Comment_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Honeypot_Wp_Comment_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Honeypot_Wp_Comment_Admin( $this->get_plugin_name(), $this->get_version() );

		// DB updater admin notices for plugin update
		$this->loader->add_filter( 'admin_notices', $plugin_admin, 'maybe_db_update' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_db_update' );

		$this->loader->add_filter( 'plugin_action_links_' . plugin_basename( HONEYPOT_WP_COMMENT_FILE ), $plugin_admin, 'plugin_action_links', 99, 1 );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Honeypot_Wp_Comment_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'comment_form_before', $plugin_public, 'render_style' );
		$this->loader->add_action( 'comment_form', $plugin_public, 'render_honeypot', 99 );
		$this->loader->add_filter( 'pre_comment_approved', $plugin_public, 'filter_spam', 99, 2 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 *
	 * @return    string    The name of the plugin.
	 * @since     2.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Honeypot_Wp_Comment_Loader    Orchestrates the hooks of the plugin.
	 * @since     2.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     2.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}
