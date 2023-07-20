<?php
/**
 * WP_SAB Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @package Plugin name
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


if ( !class_exists( 'WP_SAB' ) ) {

	/**
	 * The main WP_SAB class
	 */
	class WP_SAB {

		private static $_instance = null;

		var $admin = null,
		    $front = null,
		    $lib   = null;

		public static function instance() {

			if ( is_null( self::$_instance ) )
				self::$_instance = new self();

			return self::$_instance;
		}

		function __construct() {
			
			add_action( 'plugins_loaded', array( $this, 'action__plugins_loaded' ), 1 );

			# Register plugin activation hook
			register_activation_hook( WP_SAB_FILE, array( $this, 'action__plugin_activation' ) );
			
		}

		/**
		 * Action: plugins_loaded
		 *
		 * -
		 *
		 * @return [type] [description]
		 */
		function action__plugins_loaded() {
			

			# Load Paypal SDK on int action

			# Action to load custom post type
			// add_action( 'init', array( $this, 'action__init' ) );

			global $wp_version;

			# Set filter for plugin's languages directory
			$WP_SAB_lang_dir = dirname( WP_SAB_PLUGIN_BASENAME ) . '/languages/';
			$WP_SAB_lang_dir = apply_filters( 'WP_SAB_languages_directory', $WP_SAB_lang_dir );

			# Traditional WordPress plugin locale filter.
			$get_locale = get_locale();

			if ( $wp_version >= 4.7 ) {
				$get_locale = get_user_locale();
			}

			# Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale',  $get_locale, 'plugin-text-domain' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'plugin-text-domain', $locale );

			# Setup paths to current locale file
			$mofile_global = WP_LANG_DIR . '/plugins/' . basename( WP_SAB_DIR ) . '/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				# Look in global /wp-content/languages/plugin-name folder
				load_textdomain( 'plugin-text-domain', $mofile_global );
			} else {
				# Load the default language files
				load_plugin_textdomain( 'plugin-text-domain', false, $WP_SAB_lang_dir );
			}
		}

		/**
		 * Action: init
		 *
		 * - If license found then action run
		 *
		 */
		function action__init() {

			flush_rewrite_rules();

			# Post Type: Here you add your post type

		}
		function includes() {

			require_once plugin_dir_path(__FILE__) . 'front/class.wp_sab.front.action.php';
			$WP_SAB_Front_Action = new WP_SAB_Front_Action();

			// $WP_SAB_Front_Action  = new WP_SAB_Front_Action();
		}
	
		function register_hooks() {

			// Register activation hook.
			// register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );
			
			// Initialize the front-end class.
				// if( is_admin()) {

			// 	// require_once( WP_SAB_DIR . '/inc/admin/class.' . WP_SAB_PREFIX . '.admin.php' );
			// 	// require_once( WP_SAB_DIR . '/inc/admin/class.' . WP_SAB_PREFIX . '.admin.action.php' );
			// 	// require_once( WP_SAB_DIR . '/inc/admin/class.' . WP_SAB_PREFIX . '.admin.filter.php' );

			// }else{

			// 	// require_once( WP_SAB_DIR . '/inc/front/class.' . WP_SAB_PREFIX . '.front.php' );
			// 	// require_once( WP_SAB_DIR . '/inc/front/class.' . WP_SAB_PREFIX . '.front.action.php' );
			// 	// require_once( WP_SAB_DIR . '/inc/front/class.' . WP_SAB_PREFIX . '.front.filter.php' );

				// $this->front = new WP_SAB_Front_Action();
				$WP_SAB_Front_Action  = new WP_SAB_Front_Action();
			// }

		}

		/**
		 * register_activation_hook
		 *
		 * - When active plugin
		 *
		 */
		function action__plugin_activation() {
		
		}
		function action__setup_theme() {

			if ( is_admin() ) {
				WP_SAB()->admin = new WP_SAB_Admin;
				WP_SAB()->admin->action = new WP_SAB_Admin_Action;
				WP_SAB()->admin->filter = new WP_SAB_Admin_Filter;
			} else {
				WP_SAB()->front = new WP_SAB_Front;
				WP_SAB()->front->action = new WP_SAB_Front_Action;
				WP_SAB()->front->filter = new WP_SAB_Front_Filter;
			}

			// WP_SAB()->front() = new WP_SAB_Front_ActionV;
			

		}

	}
}

function WP_SAB() {
	return WP_SAB::instance();
	// $WP_SAB_Front_Action = new WP_SAB_Front_Action();
}

WP_SAB();
// require_once plugin_dir_path(__FILE__) . 'front/class.WP_SAB.front.action.php';
// $WP_SAB_Front_Action = new WP_SAB_Front_Action();

