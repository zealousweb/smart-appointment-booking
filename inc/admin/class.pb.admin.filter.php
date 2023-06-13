<?php
/**
 * PB_Admin_Filter Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Plugin name
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PB_Admin_Filter' ) ) {

	/**
	 *  The PB_Admin_Filter Class
	 */
	class PB_Admin_Filter {

		function __construct() {
			add_filter('manage_bms_forms_posts_columns', array( $this,'add_custom_column'), 10, 2 );		

		}

		/*
		######## #### ##       ######## ######## ########   ######
		##        ##  ##          ##    ##       ##     ## ##    ##
		##        ##  ##          ##    ##       ##     ## ##
		######    ##  ##          ##    ######   ########   ######
		##        ##  ##          ##    ##       ##   ##         ##
		##        ##  ##          ##    ##       ##    ##  ##    ##
		##       #### ########    ##    ######## ##     ##  ######
		*/


		/*
		######## ##     ## ##    ##  ######  ######## ####  #######  ##    ##  ######
		##       ##     ## ###   ## ##    ##    ##     ##  ##     ## ###   ## ##    ##
		##       ##     ## ####  ## ##          ##     ##  ##     ## ####  ## ##
		######   ##     ## ## ## ## ##          ##     ##  ##     ## ## ## ##  ######
		##       ##     ## ##  #### ##          ##     ##  ##     ## ##  ####       ##
		##       ##     ## ##   ### ##    ##    ##     ##  ##     ## ##   ### ##    ##
		##        #######  ##    ##  ######     ##    ####  #######  ##    ##  ######
		*/
		/**
		* Add custom column to the custom post type list
		*/
		function add_custom_column($columns) {

			$new_columns = array();
			$new_columns['cb'] = '';
			$new_columns['title'] = 'Title';
			$new_columns['shortcode'] = 'Shortcode';		
			$new_columns = array_merge($new_columns, $columns);
			return $new_columns;
		}

		/**
		* Plugin setting page URL.
		*/
		function cf7_pdf_plugin_action_links( $links, $file ) {
			
			if ( $file != WP_CF7_PDF_PLUGIN_BASENAME ) {
				return $links;
			}
		
			if ( ! current_user_can( 'wpcf7_read_contact_forms' ) ) {
				return $links;
			}
			
			$settings_link = wpcf7_link(
				menu_page_url( 'wp-cf7-send-pdf', false ),
				esc_html(__( 'Settings', 'Contact-Form-7-PDF-Generation' ))
			);
			array_unshift( $links, $settings_link );

			$documentlink = '<a target="_blank" href="https://www.zealousweb.com/documentation/wordpress-plugins/generate-pdf-using-contact-form-7/"> '. __( 'Document Link', 'generate-pdf-using-contact-form-7') .'</a>';
			array_unshift( $links, $documentlink );
		
			return $links;
		}
		/**
		*
		*/
		function remove_media_upload_fields( $form_fields, $post ) {
		        unset( $form_fields['url'] );
		        unset( $form_fields['align'] );
		    return $form_fields;
		}


	}

	add_action( 'plugins_loaded', function() {
		PB()->admin->filter = new PB_Admin_Filter;
	} );
}
