<?php
/**
 * PB Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @package Plugin name
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


if ( !class_exists( 'PB' ) ) {

	include_once( PB_DIR . '/inc/lib/class.' . PB_PREFIX . '.licence.php' );
	/**
	 * The main PB class
	 */
	class PB {

		private static $_instance = null;
		private static $private_data = null;

		var $admin = null,
		    $front = null,
		    $lib   = null;

		public static function instance() {

			if ( is_null( self::$_instance ) )
				self::$_instance = new self();

			return self::$_instance;
		}

		function __construct() {
			self::$private_data = new PB_Licence();
			add_action( 'plugins_loaded', array( $this, 'action__plugins_loaded' ), 1 );

			# Register plugin activation hook
			register_activation_hook( PB_FILE, array( $this, 'action__plugin_activation' ) );
			add_action( 'setup_theme', array( $this, 'action__setup_theme' ) );
			
			

		}
		// function bms_front_save_post_meta() {
		// 	echo "test";
		// 	error_log('front_save_post_meta function called');
		// 	wp_die();
		// }
		/**
		 * Action: plugins_loaded
		 *
		 * -
		 *
		 * @return [type] [description]
		 */
		function action__plugins_loaded() {

			# Load Paypal SDK on int action

			# Load plugin update file
			require_once ( PB_DIR . '/inc/class.' . PB_PREFIX . '.update.php' );

			$licence_instance = self::$private_data;
			new PB_Update( PB_VERSION, PB_PLUGIN_BASENAME, get_option( $licence_instance::pb_licence_email, '' ), get_option( $licence_instance::pb_licence_key, '' ) );

			if ( !empty( self::$private_data->instance() ) ) {

				# Action to load custom post type
				add_action( 'init', array( $this, 'action__init' ) );

				global $wp_version;

				# Set filter for plugin's languages directory
				$PB_lang_dir = dirname( PB_PLUGIN_BASENAME ) . '/languages/';
				$PB_lang_dir = apply_filters( 'PB_languages_directory', $PB_lang_dir );

				# Traditional WordPress plugin locale filter.
				$get_locale = get_locale();

				if ( $wp_version >= 4.7 ) {
					$get_locale = get_user_locale();
				}

				# Traditional WordPress plugin locale filter
				$locale = apply_filters( 'plugin_locale',  $get_locale, 'plugin-text-domain' );
				$mofile = sprintf( '%1$s-%2$s.mo', 'plugin-text-domain', $locale );

				# Setup paths to current locale file
				$mofile_global = WP_LANG_DIR . '/plugins/' . basename( PB_DIR ) . '/' . $mofile;

				if ( file_exists( $mofile_global ) ) {
					# Look in global /wp-content/languages/plugin-name folder
					load_textdomain( 'plugin-text-domain', $mofile_global );
				} else {
					# Load the default language files
					load_plugin_textdomain( 'plugin-text-domain', false, $PB_lang_dir );
				}
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
				PB()->admin = new PB_Admin;
				PB()->admin->action = new PB_Admin_Action;
				PB()->admin->filter = new PB_Admin_Filter;
			} else {
				PB()->front = new PB_Front;
				PB()->front->action = new PB_Front_Action;
				PB()->front->filter = new PB_Front_Filter;
			}

			// PB()->front() = new PB_Front_ActionV;
			

		}

	}
}

function PB() {
	return PB::instance();
}

PB();
