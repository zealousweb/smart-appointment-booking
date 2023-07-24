<?php
/**
 * WP_SAB_Admin_Filter Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage WP Smart Appointment & Booking
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_SAB_Admin_Filter' ) ) {

	/**
	 *  The WP_SAB_Admin_Filter Class
	 */
	class WP_SAB_Admin_Filter {

		function __construct() {
			add_filter('manage_sab_form_builder_posts_columns', array( $this,'add_custom_column_sab_form_builder'), 10, 2 );		
			add_filter('manage_manage_entries_posts_columns', array( $this,'add_custom_column_manage_entries'), 10, 2 );		
			add_filter('post_row_actions',  array( $this,'add_notification_row_action'), 10, 2 );
		}		
		/**
		 * Add custom action link to row actions
		 * */ 
		function add_notification_row_action($actions, $post) {
			if ($post->post_type === 'sab_form_builder') {
				// Generate the notification URL
				$notification_url = admin_url('admin.php?page=notification-settings&post_type=' . $post->post_type.'&post_id=' . $post->ID);
				
				// Add the "Notification" link to the row actions at the second position
				$actions = array_slice($actions, 0, 1, true) +
					array('notification' => '<a href="' . esc_url($notification_url) . '">Email Notification</a>') +
					array_slice($actions, 1, null, true);
				
				// Remove the "View" link from the row actions
				unset($actions['view']);
			}
		
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
	
		/**
		* Add custom column to the custom post type list
		*/
		function add_custom_column_sab_form_builder($columns) {

			$new_columns = array();
			$new_columns['cb'] = '';
			$new_columns['title'] = 'Title';
			$new_columns['shortcode'] = 'Shortcode';		
			$new_columns = array_merge($new_columns, $columns);
			return $new_columns;
		}
		function add_custom_column_manage_entries($columns) {
			
			$new_columns = array();
			$new_columns['cb'] = '';
			$new_columns['title'] = 'Title';
			$new_columns['form'] = 'Form';
			$new_columns['booking_status'] = 'Status';
			$new_columns['booking_date'] = 'Booked Date';
			$new_columns['timeslot'] = 'Timeslot';		
			$columns['date'] = 'Entry Date';
			$new_columns = array_merge($new_columns, $columns);
			return $new_columns;
		}
	}
	add_action( 'plugins_loaded', function() {
		$WP_SAB_Admin_Filter = new WP_SAB_Admin_Filter();
	} );
	
}
