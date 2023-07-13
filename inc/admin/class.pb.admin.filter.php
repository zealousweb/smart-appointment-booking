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
			add_filter('manage_bms_forms_posts_columns', array( $this,'add_custom_column_bms_forms'), 10, 2 );		
			add_filter('manage_bms_entries_posts_columns', array( $this,'add_custom_column_bms_entries'), 10, 2 );		
			add_filter('post_row_actions',  array( $this,'add_notification_row_action'), 10, 2 );
		
		}
		
		
		
		// Add custom action link to row actions
		function add_notification_row_action($actions, $post) {
			if ($post->post_type === 'bms_forms') {
				// Generate the notification URL
				$notification_url = admin_url('admin.php?page=notification-settings&post_type=' . $post->post_type.'&post_id=' . $post->ID);
				
				// Add the "Notification" link to the row actions at the second position
				$actions = array_slice($actions, 0, 1, true) +
					array('notification' => '<a href="' . esc_url($notification_url) . '">Email Notification</a>') +
					array_slice($actions, 1, null, true);
				
				// Remove the "View" link from the row actions
				unset($actions['view']);
			}
			
			// if ($post->post_type === 'bms_entries') {
			// 	// Generate the notification URL
			// 	// unset($actions['edit']);
			// 	$notification_url = admin_url('admin.php?page=view-booking-entry&post_type=' . $post->post_type.'&post_id=' . $post->ID);
				
			// 	// Add the "View" link to the row actions
			// 	// $actions = array(
			// 	// 	'view' => '<a href="' . esc_url($notification_url) . '">View</a>'
			// 	// ) + $actions;
			// }
			
			return $actions;
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
		
		// function remove_row_actions_for_custom_post_type($actions, $post) {
		// 	if ($post->post_type === 'bms_entries') {
		// 		unset($actions['edit']);
		// 	}
		// 	return $actions;
		// }
		
		/**
		* Add custom column to the custom post type list
		*/
		function add_custom_column_bms_forms($columns) {

			$new_columns = array();
			$new_columns['cb'] = '';
			$new_columns['title'] = 'Title';
			$new_columns['shortcode'] = 'Shortcode';		
			$new_columns = array_merge($new_columns, $columns);
			return $new_columns;
		}
		function add_custom_column_bms_entries($columns) {
			
			$new_columns = array();
			$new_columns['cb'] = '';
			$new_columns['title'] = 'Title';
			$new_columns['form'] = 'Form';
			$new_columns['booking_status'] = 'Booking Status';
			$new_columns['booking_date'] = 'Booked Date';
			$new_columns['timeslot'] = 'Timeslot';		
			$columns['date'] = 'Entry Date';
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

		// function add_custom_post_type_column($columns) {
		// 	$columns['notification'] = 'Notification';
		// 	return $columns;
		// }



	}

	add_action( 'plugins_loaded', function() {
		PB()->admin->filter = new PB_Admin_Filter;
	} );
}
