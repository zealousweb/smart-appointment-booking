<?php
/**
 * PB_Admin_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @package Plugin name
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PB_Admin_Action' ) ) {

	/**
	 *  The PB_Admin_Action Class
	 */
	class PB_Admin_Action {

		function __construct()  {

			add_action( 'admin_init', array( $this, 'action__admin_init' ) );

			add_action( 'admin_enqueue_scripts',array( $this, 'enqueue_styles' ));
			add_action( 'admin_enqueue_scripts',array( $this, 'enqueue_scripts' ));
			add_action('admin_menu',array( $this, 'add_main_custom_post_type_menu' ));
			
			// add_action( 'add_meta_boxes', array( $this, 'bms_add_meta_box' ) );		
			add_action( 'wp_ajax_bms_save_form_data', array( $this, 'bms_save_form_data' ));
			add_action('manage_bms_forms_posts_custom_column', array( $this, 'populate_custom_column' ), 10, 2);
			add_action('manage_bms_entries_posts_custom_column', array( $this, 'populate_custom_column' ), 10, 2);

			add_action('wp_ajax_zfb_preiveiw_timeslot', array( $this, 'zfb_preiveiw_timeslot' ) );
			add_action('wp_ajax_nopriv_zfb_preiveiw_timeslot', array( $this, 'zfb_preiveiw_timeslot' ) );

			add_action('admin_enqueue_scripts',  array( $this, 'enqueue_admin_scripts' ), 10, 2);
			
		}

		/*
		   ###     ######  ######## ####  #######  ##    ##  ######
		  ## ##   ##    ##    ##     ##  ##     ## ###   ## ##    ##
		 ##   ##  ##          ##     ##  ##     ## ####  ## ##
		##     ## ##          ##     ##  ##     ## ## ## ##  ######
		######### ##          ##     ##  ##     ## ##  ####       ##
		##     ## ##    ##    ##     ##  ##     ## ##   ### ##    ##
		##     ##  ######     ##    ####  #######  ##    ##  ######
		*/

		/**
		 * Action: admin_init
		 *
		 * - Register admin min js and admin min css
		 *
		 */
		function action__admin_init() {

			wp_register_script( PB_PREFIX . '_admin_js', PB_URL . 'assets/js/admin.min.js', array( 'jquery-core' ), PB_VERSION );
			wp_register_style( PB_PREFIX . '_admin_css', PB_URL . 'assets/css/admin.min.css', array(), PB_VERSION );

		}


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
		* WP Enqueue Styles
		*/
		function enqueue_styles() {

			if(isset($_GET['post'])){
				$postid = $_GET['post'];
				$post_type = get_post_type($postid);
			}else{
				$post_type = '';
			}
			
			if (is_singular('bms_forms') || is_singular('zeal_formbuilder') || $post_type == 'bms_forms' || isset($_GET['post_type']) && $_GET['post_type'] === 'bms_forms') {
				
				wp_enqueue_style( 'bms_boostrap_min',PB_URL.'assets/css/bootstrap.min.css', array(), 1.1, 'all' );
				wp_enqueue_style( 'bms_formio_full_min',PB_URL.'assets/css/formio.full.min.css', array(), 1.1, 'all' );
				wp_enqueue_style( 'bms_font-awesomev1','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', array(), 1.1, 'all' );
				
			}
		}

		/**
		* WP Enqueue Scripts
		*/
		function enqueue_scripts() {

			if(isset($_GET['post'])){
				$postid = $_GET['post'];
				$post_type = get_post_type($postid);
			}else{
				$post_type = '';
			}

			if (is_singular('bms_forms') || is_singular('zeal_formbuilder') || $post_type == 'bms_forms' || isset($_GET['post_type']) && $_GET['post_type'] === 'bms_forms') {
				wp_enqueue_script( 'bms_bootstrapbundlemin', PB_URL.'assets/js/bootstrap.bundle.min.js', array( 'jquery' ), 1.1, false );
			 	wp_enqueue_script( 'bms_formio_full_min', PB_URL.'assets/js/formio.full.min.js', array( 'jquery' ), 1.1, false );
				 wp_enqueue_script( 'bms_popper.minjs', PB_URL.'assets/js/popper.min.js', array( 'jquery' ), 1.1, false );
			 	wp_enqueue_script( 'bms_bootstrap.min', PB_URL.'assets/js/bootstrap.min.js', array( 'jquery' ), 1.1, false );
			 	wp_enqueue_script( 'bms_jquery-3.7.0.slim.min', PB_URL.'assets/js/jquery-3.7.0.slim.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'bms_jquery-3.7.0.min',PB_URL.'assets/js/jquery-3.7.0.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'booking-form', PB_URL.'assets/js/booking-form.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'admin', PB_URL.'assets/js/admin.js', array( 'jquery' ), 1.1, false );
			}
		}

		function enqueue_admin_scripts() {
			wp_enqueue_script('jquery-ui-tabs');
		}

		/*
		* Add the main custom post type as the admin menu item
		*/
		function add_main_custom_post_type_menu() {
			
			$labels_form = array(
				'name' => 'Booking Form',
				'singular_name' => 'Booking Form',
			);

			$args_form = array(
				
				'labels' => $labels_form,
				'description' => '',
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'delete_with_user' => false,
				'show_in_rest' => false,
				'rest_base' => '',
				'has_archive' => false,
				// 'show_in_menu' => 'wpcf7',
				'show_in_menu' => 'stripe_dashboard',
				'show_in_nav_menus' => false,
				'exclude_from_search' => true,
				'capability_type' => 'post',
				'capabilities' => array(
					'read' => true,
					'create_posts'  => true,
					'publish_posts' => true,
				),
				'map_meta_cap' => true,
				'hierarchical' => false,
				'rewrite' => true,
				'query_var' => true,
				'supports' => array( 'title' ),
			);
			register_post_type('bms_forms', $args_form);

			$labels = array(
				'name' => 'Booking Entries',
				'singular_name' => 'Booking Entry',
			);

			$args = array(
				'labels' => $labels,
				'description' => '',
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'delete_with_user' => false,
				'show_in_rest' => true,
				'rest_base' => '',
				'has_archive' => false,
				'show_in_nav_menus' => false,
				'exclude_from_search' => true,
				'capability_type' => 'post',
				'capabilities' => array(
					'read' => true,
					'create_posts'  => false,
					'publish_posts' => true,
				),
				'map_meta_cap' => true,
				'hierarchical' => false,
				'rewrite' => true,
				'query_var' => true,
				'supports' => array( 'title' ),
			);

			register_post_type('bms_entries', $args);

			add_menu_page(
				'Booking Forms',
				'Booking Form',
				'manage_options',
				'edit.php?post_type=bms_forms',
				'',
				'',
				20,
			);
			add_submenu_page(
				'edit.php?post_type=bms_forms', 
				'Add New Form', 
				'Add New Form', 
				'manage_options', 
				admin_url('post-new.php?post_type=bms_forms')
			);
			
			add_submenu_page(
				'edit.php?post_type=bms_forms',
				'Booking Entries',
				'Booking Entries',
				'manage_options',
				admin_url('edit.php?post_type=bms_entries')
			);
			add_submenu_page(
				'edit.php?post_type=bms_form',
				'Email Templates',
				'Email Templates',
				'manage_options',
				'bms_email_template',
				'bms_email_template_page_callback'
			);
		
		
		}

		function bms_email_template_page_callback(){
			echo __('Email Template','bms');
		}
				
		function bms_save_form_data() {
			
			// Get the post ID
			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

			// Get the form data
			$form_data = isset( $_POST['form_data'] ) ? $_POST['form_data'] : array();
			// print_r($form_data );
			// Update the post meta with the form data
			update_post_meta( $post_id, '_my_meta_value_key', $form_data );

			wp_send_json_success( 'Form data saved successfully.' );
			exit;

		}

		
		// Populate the custom column with data
		function populate_custom_column($column, $post_id) {
			if ($column === 'shortcode') {				
				echo "[booking_form form_id='".$post_id."']";
			}
			if ($column === 'form') {	
				$form_id = get_post_meta($post_id,'bms_form_id',true);	
				$form_title = get_the_title($form_id);	
				echo __($form_title,'textdomain');
			}
			if ($column === 'booking_status') {		
				$booking_status = get_post_meta($post_id,'entry_status',true);
				echo __($booking_status,'textdomain');
				
			}
			if ($column === 'event_status') {
				$form_id = get_post_meta($post_id,'bms_form_id',true);	
				$form_title = get_the_title($form_id);					
				echo __($form_title,'textdomain');
			}
			

		}
		function zfb_preiveiw_timeslot() {
			$error = 0;
			$output = '';		
			if (isset($_POST['post_id'])) {
				
				$post_id = $_POST['post_id'];
				$start_time = get_post_meta($post_id, 'start_time', true);
				$end_time = get_post_meta($post_id, 'end_time', true);
				$break_times = get_post_meta($post_id, 'breaktimeslots', true);
				$duration_minutes = get_post_meta($post_id, 'timeslot_duration', true);
				$gap_minutes = get_post_meta($post_id, 'steps_duration', true);
		
				$available_timeslots = $this->admin_generate_timeslots($start_time, $end_time, $duration_minutes, $gap_minutes, $break_times, $post_id);
				// Generate the output string
				$output = implode('<br>', $available_timeslots);
				// $output = $available_timeslots;
			} else {
				$error = 1;
				$error_mess = "Something went wrong";
				error_log("post_id not found while preview");
			}
		
			if ($error == 1) {
				// Send error response
				wp_send_json_error(array(
					'error_mess' => $error_mess
				));
			} else {
				// Send success response with timeslots
				wp_send_json_success(array(
					'message' => 'Submitted Successfully',
					'output' => $output
				));
			}
			wp_die();
		}
		function admin_generate_timeslots($start_time, $end_time, $duration_minutes, $gap_minutes, $break_times, $post_id){
			// Convert start time and end time to Unix timestamps
			$start_timestamp = strtotime($start_time);
			$end_timestamp = strtotime($end_time);
	
			// Convert duration and gap to minutes
			$duration = ($duration_minutes['hours'] * 60) + $duration_minutes['minutes'];
			$gap = ($gap_minutes['hours'] * 60) + $gap_minutes['minutes'];
	
			// Convert break times to Unix timestamps
			$break_timestamps = array();
			foreach ($break_times as $break_time) {
				$break_start_timestamp = strtotime($break_time['start_time']);
				$break_end_timestamp = strtotime($break_time['end_time']);
				$break_timestamps[] = array($break_start_timestamp, $break_end_timestamp);
			}
	
			// Initialize the current timestamp with the start timestamp
			$current_timestamp = $start_timestamp;
	
			// Initialize an array to store available timeslots
			$available_timeslots = array();
	
			// Loop through the durations between the start time and end time
			while ($current_timestamp <= $end_timestamp) {
				// Check if the current timestamp falls within any break time
				$within_break = false;
				foreach ($break_timestamps as $break_timestamp) {
					if ($current_timestamp >= $break_timestamp[0] && $current_timestamp < $break_timestamp[1]) {
						$current_timestamp = $break_timestamp[1]; // Move to the end of the break
						$within_break = true;
						break;
					}
				}
	
				if ($within_break) {
					continue;
				}
	
				// Calculate the end timestamp for the current timeslot
				$end_timeslot = $current_timestamp + ($duration * 60);
	
				// Check if the timeslot extends beyond the end time
				if ($end_timeslot > $end_timestamp) {
					break;
				}
	
				// Check if the timeslot extends into any break time
				foreach ($break_timestamps as $break_timestamp) {
					if ($end_timeslot > $break_timestamp[0] && $current_timestamp < $break_timestamp[0]) {
						$end_timeslot = $break_timestamp[0]; // Adjust the end of the timeslot to the start of the break
						break;
					}
				}
	
				// Format the start time and end time of the timeslot
				$start_time_slot = date('h:i A', $current_timestamp);
				$end_time_slot = date('h:i A', $end_timeslot);
	
				// Add the timeslot to the available timeslots array
				$available_timeslots[] = $start_time_slot . ' - ' . $end_time_slot;
	
				// Move to the next available timeslot (including the gap)
				$current_timestamp = $end_timeslot + ($gap * 60);
			}
			return $available_timeslots;
		}
        		
	}

	add_action( 'plugins_loaded', function() {
		PB()->admin->action = new PB_Admin_Action;
	} );
}
