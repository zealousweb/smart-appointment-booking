<?php
/**
 * AB_SAB Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @package Smart Appointment & Booking
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AB_SAB' ) ) {

    class AB_SAB {

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
        }
        function action__setup_theme() {

				if ( is_admin() ) {

					AB_SAB()->admin = new SAB_Admin;
					AB_SAB()->admin->action = new SAB_Admin_Action;
					AB_SAB()->admin->filter = new SAB_Admin_Filter;

				} else {

					AB_SAB()->front = new SAB_Front;
					AB_SAB()->front->action = new SAB_Front_Action;
					AB_SAB()->front->filter = new SAB_Front_Filter;
				}
        }

        function action__plugins_loaded() {
			
			global $wp_version;

			# Set filter for plugin's languages directory
			$SAB_lang_dir = dirname( SAB_PLUGIN_BASENAME ) . '/languages/';
			$SAB_lang_dir = apply_filters( 'SAB_languages_directory', $SAB_lang_dir );

			# Traditional WordPress plugin locale filter.
			$get_locale = get_locale();

			if ( $wp_version >= 4.7 ) {
				$get_locale = get_user_locale();
			}

			# Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale',  $get_locale, 'wp-smart-appointment-booking' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'wp-smart-appointment-booking', $locale );

			# Setup paths to current locale file
			$mofile_global = WP_LANG_DIR . '/plugins/' . basename( SAB_DIR ) . '/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				# Look in global /wp-content/languages/plugin-name folder
				load_textdomain( 'smart-appointment-booking', $mofile_global );
			} else {
				# Load the default language files
				load_plugin_textdomain( 'smart-appointment-booking', false, $SAB_lang_dir );
			}
        }
    }
}
