<?php
/**
 * SAAB Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @package Smart Appointment & Booking
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'SAAB' ) ) {

    class SAAB {

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
            add_action( 'setup_theme', array( $this, 'saab_action__setup_theme' ) );
            add_action( 'plugins_loaded', array( $this, 'saab_action__plugins_loaded' ), 1 );
        }
        function saab_action__setup_theme() {

				if ( is_admin() ) {

					SAAB()->admin = new SAAB_Admin;
					SAAB()->admin->action = new SAAB_Admin_Action;
					SAAB()->admin->filter = new SAAB_Admin_Filter;

				} else {

					SAAB()->front = new SAAB_Front;
					SAAB()->front->action = new SAAB_Front_Action;
					SAAB()->front->filter = new SAAB_Front_Filter;
				}
        }

        function saab_action__plugins_loaded() {
			
			global $wp_version;

			# Set filter for plugin's languages directory
			$SAAB_lang_dir = dirname( SAAB_PLUGIN_BASENAME ) . '/languages/';
			$SAAB_lang_dir = apply_filters( 'SAAB_languages_directory', $SAAB_lang_dir );

			# Traditional WordPress plugin locale filter.
			$get_locale = get_locale();

			if ( $wp_version >= 4.7 ) {
				$get_locale = get_user_locale();
			}

			# Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale',  $get_locale, 'wp-smart-appointment-booking' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'wp-smart-appointment-booking', $locale );

			# Setup paths to current locale file
			$mofile_global = WP_LANG_DIR . '/plugins/' . basename( SAAB_DIR ) . '/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				# Look in global /wp-content/languages/plugin-name folder
				load_textdomain( 'smart-appointment-booking', $mofile_global );
			} else {
				# Load the default language files
				load_plugin_textdomain( 'smart-appointment-booking', false, $SAAB_lang_dir );
			}
        }
    }
}
