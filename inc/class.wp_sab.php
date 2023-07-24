<?php
/**
 * WP_SAB Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @package WP Smart Appointment & Booking
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_SAB' ) ) {

    class WP_SAB {

        private static $_instance = null;
        var $admin = null,
            $front = null,
            $lib   = null;

        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        private function __construct() {
            add_action( 'setup_theme', array( $this, 'action__setup_theme' ) );
            add_action( 'plugins_loaded', array( $this, 'action__plugins_loaded' ), 1 );
			// register_activation_hook( __FILE__,  array( $this, 'activate_wp_sab' ), 1 );
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
        }

        function action__plugins_loaded() {
			
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
			$locale = apply_filters( 'plugin_locale',  $get_locale, 'wp-smart-appointment-booking' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'wp-smart-appointment-booking', $locale );

			# Setup paths to current locale file
			$mofile_global = WP_LANG_DIR . '/plugins/' . basename( WP_SAB_DIR ) . '/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				# Look in global /wp-content/languages/plugin-name folder
				load_textdomain( 'wp-smart-appointment-booking', $mofile_global );
			} else {
				# Load the default language files
				load_plugin_textdomain( 'wp-smart-appointment-booking', false, $WP_SAB_lang_dir );
			}
        }

		function activate_wp_sab() {

		}
    }
}
