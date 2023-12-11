<?php
/**
 * SAAB_Front_Action Class
 *
 * Handles the Frontend Actions.
 *
 * @package WordPress
 * @subpackage Smart Appointment & Booking
 * @since 1.0
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'SAAB_Front_Action' ) ){

	/**
	 *  The SAAB_Front_Action Class
	 */
	class SAAB_Front_Action {

		function __construct()  {
		
			add_action('wp_ajax_saab_booking_form_submission', array( $this, 'saab_booking_form_submission' ) );
			add_action('wp_ajax_nopriv_saab_booking_form_submission', array( $this, 'saab_booking_form_submission' ) );

			add_action('wp_ajax_saab_save_form_submission', array( $this, 'saab_save_form_submission' ) );
			add_action('wp_ajax_nopriv_saab_save_form_submission', array( $this, 'saab_save_form_submission' ) );

			add_action('wp_ajax_saab_action_reload_calender', array( $this, 'saab_action_reload_calender' ) );
			add_action('wp_ajax_nopriv_saab_action_reload_calender', array( $this, 'saab_action_reload_calender' ) );

			add_action('wp_ajax_saab_action_display_available_timeslots', array( $this, 'saab_action_display_available_timeslots' ) );
			add_action('wp_ajax_nopriv_saab_action_display_available_timeslots', array( $this, 'saab_action_display_available_timeslots' ) );

			add_action( 'wp_enqueue_scripts',  array( $this, 'saab_action__enqueue_styles' ));
			add_action( 'wp_enqueue_scripts', array( $this, 'saab_action__wp_enqueue_scripts' ));

			add_shortcode('saab_booking_form',array( $this, 'saab_get_booking_form' ));
			add_action('wp_ajax_saab_cancel_booking', array( $this, 'saab_cancel_booking' ) );
			add_action('wp_ajax_nopriv_saab_cancel_booking', array( $this, 'saab_cancel_booking' ) );

			add_shortcode( 'saab_confirm_booking_cancellation', array( $this, 'saab_confirm_booking_cancellation' ) );
			add_action('wp_ajax_saab_cancel_booking_shortcode', array( $this, 'saab_cancel_booking_shortcode' ) );
			add_action('wp_ajax_nopriv_saab_cancel_booking_shortcode', array( $this, 'saab_cancel_booking_shortcode' ) );

			add_action('saab_get_available_seats_per_timeslot', array( $this, 'saab_get_available_seats_per_timeslot' ) , 10, 4);
			
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
		 * Action: wp_enqueue_scripts
		 *
		 * - enqueue script in front side
		 *
		 */
		function saab_action__wp_enqueue_scripts() {
			if(is_admin()){
				wp_enqueue_script( SAAB_PREFIX . '_bookingform', SAAB_URL . 'assets/js/booking/booking-form.js', array( 'jquery-core' ), SAAB_VERSION );
			}
			wp_enqueue_script( SAAB_PREFIX . '_front', SAAB_URL . 'assets/js/front.js', array( 'jquery-core' ), SAAB_VERSION );
			wp_enqueue_script( 'saab_formio_full_min', SAAB_URL.'assets/js/formio/formio.full.min.js', array( 'jquery' ), 1.1, false );
			 // Create a nonce for the AJAX request
			$ajax_nonce = wp_create_nonce('my_ajax_nonce');
			wp_localize_script('saab_formio_full_min', 'myAjax', array(
				'ajaxurl' => esc_url(admin_url('admin-ajax.php')),
				'nonce'   => $ajax_nonce,
			));
			wp_enqueue_script( 'saab_boostrap.min', SAAB_URL.'assets/js/boostrap/boostrap.min.js', array( 'jquery' ), 1.1, false );
			
			//cancel booking 

			if (is_front_page()) {

				wp_enqueue_script('cancel-booking', SAAB_URL . 'assets/js/booking/cancelbooking.js', array('jquery'), '1.0', true);
				
				wp_localize_script('cancelbooking', 'myAjax', array(
					'ajaxurl' => esc_url(admin_url('admin-ajax.php')),
				));
			}
		}
		function saab_action__enqueue_styles() {

			wp_enqueue_style( 'saab_front',SAAB_URL.'assets/css/front.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'saab_boostrap_min',SAAB_URL.'assets/css/boostrap/boostrap.min.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'saab_formio_full_min',SAAB_URL.'assets/css/formio/formio.full.min.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'saab_font-awesomev1',SAAB_URL.'assets/css/font-awesome.css', array(), 1.1, 'all' );
				
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
		 * Check if booking is open
		 */
		function saab_isbooking_open($post_id, $timeslot, $booked_date) {
			$explodedArray = explode("_", $booked_date);
			// Extracting month, day, and year components
			$month = sprintf("%02d", $explodedArray[2]);
			$day = $explodedArray[3];
			$year = $explodedArray[4];
			// Convert the given date to a Unix timestamp
			$givenTimestamp = strtotime("$year-$month-$day");
		
			// Get today's Unix timestamp
			$todayTimestamp = strtotime('today');
		
			$booking_stops_after = get_post_meta($post_id, 'saab_booking_stops_after', true);
			if (!empty($booking_stops_after)) {
				$booking_stops_after_duration_seconds = ($booking_stops_after['hours'] * 3600) + ($booking_stops_after['minutes'] * 60);
			}
		
			$current_time = time();
			$waiting_text = '';
		
			// Explode the time range into start and end times
			$time_parts = explode("-", $timeslot);
			$start_time = $time_parts[0]; // "05:30 PM"
			$end_time = $time_parts[1];   // "08:00 PM"
		
			$get_timezone = get_post_meta($post_id, 'saab_timezone', true);
			// date_default_timezone_set($get_timezone);
			if ($get_timezone) {
				wp_timezone_override_offset(false, $get_timezone);
			}
			$start_timestamp = strtotime($start_time);			
			$end_timestamp = strtotime($end_time);
		
			if ($givenTimestamp > $todayTimestamp) {
				// Given date is greater than today's date
				$isbooking_open = true;
			} elseif ($current_time >= $start_timestamp) {
			
				// The current time is greater than or equal to the start time
				// Calculate the end time with booking stops after duration
				$this_start_time = $start_timestamp + $booking_stops_after_duration_seconds;
				if ($current_time < $this_start_time) {
					// The current time is less than the extended booking time
					$isbooking_open = true;
				} else {
					$isbooking_open = false;
				}
			} else {
				
				// None of the above conditions met, booking is closed
				$isbooking_open = true;
			}
		
			return $isbooking_open;
		}
		/**
		 * Saves the results of a booking calendar form submission.
		 *
		 * This function handles the submission of a booking calendar form, processes the data, and stores the results.
		 * It may perform tasks like validating the input, updating the database, and sending notifications.
		 *
		 */	
		function saab_booking_form_submission() {

			$nonce = isset($_POST['nonce']) ? sanitize_key(wp_unslash ($_POST['nonce'])) : '';

			if (empty($nonce) || !check_ajax_referer('booking_form_nonce', 'nonce', false)) {
				wp_send_json_error('Invalid nonce');
			}
			
			$form_id = absint($_POST['fid']);
			$error = $mail_message = '';		

			$booking_date = isset($_POST['booking_date']) ? sanitize_text_field($_POST['booking_date']) : '';
			$explode_booking_date = explode('-',$booking_date);
			$format_bookingdate = $explode_booking_date[4] . "-" . $explode_booking_date[2] . "-" . $explode_booking_date[3];
			$converted_bookingdate = gmdate('Y-m-d', strtotime($format_bookingdate));
			$timeslot = isset($_POST['timeslot']) ? sanitize_text_field($_POST['timeslot']) : '';
			$slotcapacity = isset($_POST['slotcapacity']) ? absint($_POST['slotcapacity']) : 0;			
			$bookedseats = isset($_POST['bookedseats']) ? absint($_POST['bookedseats']) : 0;
			
			$FormTitle = get_the_title($form_id);
			$form_data = isset( $_POST['form_data'] ) ? sanitize_text_field($_POST['form_data']) : array();

			if (is_array($form_data)) {

				foreach ($form_data as $field_name => &$field_value) {
					
					if (is_array($field_value)) {
						foreach ($field_value as &$value) {
							
							if (is_email($value)) {
								$value = sanitize_email($value);
							} elseif (is_numeric($value)) {
								$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
							} elseif (is_string($value)) {
								
								$value = sanitize_textarea_field($value);
							} else {
							
								$value = sanitize_text_field($value);
							}
						}
					} else {
						
						if (is_email($field_value)) {
							$field_value = sanitize_email($field_value);
						} elseif (is_numeric($field_value)) {
							$field_value = filter_var($field_value, FILTER_SANITIZE_NUMBER_INT);
						} elseif (is_string($field_value)) {							
							$field_value = sanitize_textarea_field($field_value);
						} else {							
							$field_value = sanitize_text_field($field_value);
						}
					}
				}
			}

			$enable_auto_approve = get_post_meta($form_id, 'saab_enable_auto_approve', true);
			$check_waiting = get_post_meta($form_id, 'saab_waiting_list', true);
			$cost = get_post_meta($form_id, 'saab_cost', true);
			$appointment_type = get_post_meta($form_id, 'saab_appointment_type', true);
			$label_symbol = get_post_meta($form_id, 'saab_label_symbol', true);
			$seats_per_timeslot =  get_post_meta($form_id, 'saab_slotcapacity',true);

			$bookmap_email = get_post_meta($form_id, 'saab_map_email', true); 
			if (isset($form_data) && !empty($bookmap_email)) {
				if (in_array($bookmap_email, array_keys($form_data['data']))) {
					$user_email = $form_data['data'][$bookmap_email];
			
					// Validate $user_email
					if (empty($user_email)) {
						wp_send_json_error(array(
							'message' => esc_html__('Email field is empty', 'smart-appointment-booking'),
							'error' => $error,
						));
						wp_die();
					} elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
						wp_send_json_error(array(
							'message' => esc_html__('Invalid email address', 'smart-appointment-booking'),
							'error' => $error,
						));
						wp_die();
					}
				}
			} else {
				wp_send_json_error(array(
					'message' => esc_html__('Form Data not found or Error in configuring Email Field', 'smart-appointment-booking'),
					'error' => $error,
				));
				wp_die();
			}
			$check_isbooking_open = $this->saab_isbooking_open($form_id,$timeslot,$booking_date);
			if($check_isbooking_open === false){
				$error = true;
				wp_send_json_error(array(
					'message' => esc_html__('The booking window has closed.','smart-appointment-booking'),
					'error' => $error,
				));
				wp_die();
			}
			$waiting_list = 'false';
			if ($bookedseats > $slotcapacity ) {
				if ($check_waiting) {
					$register_booking = 'true';
					$waiting_list = 'true';
				} else {
					$register_booking = 'false';
					$error = esc_html__('No available seats','smart-appointment-booking');
				}
			}else{
				$register_booking = 'true';
			}
		
			if ($register_booking === 'true') {
				
				
				$new_post = array(
					'post_title'   => 'entry#',
					'post_type'    => 'manage_entries',
					'post_status'  => 'publish'
				);
		
				$created_post_id = wp_insert_post($new_post);			
				
				// Update the post title with the created post ID
				$new_post_title = 'entry_#' . $created_post_id;

				// Update the post
				$update_post_args = array(
					'ID'         => $created_post_id,
					'post_title' => $new_post_title
				);

				wp_update_post($update_post_args);

				// Update the post slug (permalink)
				$slug = sanitize_title($new_post_title);
				wp_update_post(array('ID' => $created_post_id, 'post_name' => $slug));

				// Update the post's permalink (optional)
				$new_post_permalink = get_permalink($created_post_id);
				update_post_meta($created_post_id, '_wp_old_slug', $new_post_permalink);

				update_post_meta($created_post_id, 'saab_submission_data', $form_data);
				update_post_meta($created_post_id, 'saab_form_id', $form_id);		
				update_post_meta($created_post_id, 'saab_timeslot', $timeslot);
				update_post_meta($created_post_id, 'saab_booking_date', $booking_date);
				update_post_meta($created_post_id, 'saab_slotcapacity', $bookedseats);
				update_post_meta($created_post_id, 'saab_cost', $cost);
				update_post_meta($created_post_id, 'saab_label_symbol', $label_symbol);
				update_post_meta($created_post_id, 'saab_appointment_type', $appointment_type);

				$submission_key_val = array();				
				foreach($form_data['data'] as $form_key => $form_value){
                    if($form_key !== 'submit'){
						$submission_key_val[$form_key] = esc_attr($form_value);
                    }
                }
				$explode_timeslot = explode('-',$timeslot);

				$get_user_mapping = get_post_meta($form_id, 'saab_user_mapping', true);
				
				$getfirst_name = isset($get_user_mapping['first_name']) ? sanitize_text_field($get_user_mapping['first_name']) : '';
				if ($getfirst_name) {
					$first_name = $form_data['data'][$getfirst_name];					
				}
				$getlast_name = isset($get_user_mapping['last_name']) ? sanitize_text_field($get_user_mapping['last_name']) : '';
				if ($getlast_name) {
					$last_name =  $form_data['data'][$getlast_name];					
				}
				$getemail = isset($get_user_mapping['email']) ? sanitize_text_field($get_user_mapping['email']) : '';
				if ($getemail) {
					$emailTo =  $form_data['data'][$getemail];					
				}
				$getservice = isset($get_user_mapping['service']) ? sanitize_text_field($get_user_mapping['service']) : '';
				if ($getservice) {
					$service =  ucfirst($form_data['data'][$getservice]);					
				}
			
				// $encrypted_booking_id = wp_base64_encode($created_post_id);
				$encrypted_booking_id = $created_post_id;
				$user_mapping = get_post_meta($form_id, 'saab_user_mapping', true);
				$ajax_nonce = wp_create_nonce('my_ajax_nonce');
				if($user_mapping){

					$cancelbooking_pageid = isset($user_mapping['cancel_bookingpage']) ? sanitize_text_field($user_mapping['cancel_bookingpage']) : '';
					$cancelbooking_url = get_permalink($cancelbooking_pageid).'?booking_id=' . $encrypted_booking_id . '&status=cancel&security='.$ajax_nonce;
				}else{
					$encoded_booking_id = urlencode($encrypted_booking_id);
					$cancelbooking_url = home_url('/?booking_id=' . $encoded_booking_id . '&status=cancel&security='.$ajax_nonce);
				}

				$prefixlabel = get_post_meta( $form_id, 'saab_label_symbol', true );
				$cost = get_post_meta( $form_id, 'saab_cost', true );
				$BookingDate = get_the_date( 'M d,Y', $form_id );

				$other_label_val = array(
					'FormId' => $form_id,
					'BookingId' => $created_post_id,
					'FormTitle' => $FormTitle,
					'To' => $emailTo,
					'FirstName' => $first_name,
					'LastName' => $last_name,
					'Timeslot' => $timeslot,
					'BookingDate' => $BookingDate,
					'BookedSeats' => $bookedseats,
					'BookedDate' =>$converted_bookingdate,					
					'Service' => $service,
					'prefixlabel' => $prefixlabel,
					'cost' => $cost,					
					'slotcapacity' => $slotcapacity,
					'form_data' => $form_data,
					'seats_per_timeslot' => $seats_per_timeslot,
					'StartTime' => $explode_timeslot[0],
					'EndTime' => $explode_timeslot[1],
					'CancelBooking' => $cancelbooking_url,
				);

				$listform_label_val = array_merge($submission_key_val, $other_label_val);
				if ($enable_auto_approve) {
					if ($waiting_list === 'true') {
						$mail_message = '';
						$status = 'waiting';
						$listform_label_val['Status'] = $status;
						$mail_message = $this->saab_send_notification( $status, $form_id, $created_post_id, $listform_label_val);
						update_post_meta($created_post_id, 'saab_entry_status', 'waiting');

					} else {
						$mail_message = '';
						$status = 'booked';
						$listform_label_val['Status'] = $status;
						$mail_message = $this->saab_send_notification( $status, $form_id, $created_post_id, $listform_label_val);
						update_post_meta($created_post_id, 'saab_entry_status', 'booked');
					}
				} else {
					$mail_message = '';
					$status = 'pending';
					$listform_label_val['Status'] = $status;
					$mail_message = $this->saab_send_notification( $status, $form_id, $created_post_id, $listform_label_val);
					update_post_meta($created_post_id, 'saab_entry_status', 'pending');

				}
				$confirmation = get_post_meta($form_id, 'saab_confirmation', true);
				$success_message = get_post_meta($form_id, 'saab_redirect_text', true);
				$formatted_message = wpautop($success_message);
				$redirect_url = '';
				
				if ($confirmation == 'redirect_text') {
					$wp_editor_value = get_post_meta($form_id, 'saab_redirect_text', true);
				} elseif ($confirmation == 'redirect_page') {
					$redirect_page = get_post_meta($form_id, 'saab_redirect_page', true);
					$redirect_url = get_permalink($redirect_page);
				} elseif ($confirmation == 'redirect_to') {
					$redirect_url = get_post_meta($form_id, 'saab_redirect_to', true);
				}
				
				// Send success response
				wp_send_json_success(array(					
					'message' => 'Sucessfully Submitted',
					'redirect_page' => $redirect_url,
					'wp_editor_value' => $formatted_message,
					'redirect_url' => $redirect_url,
					'confirmation' => $confirmation,
					'mail_sent' => $mail_message
				));
			}else{
				$error = true;
				
				wp_send_json_error(array(
					'message' => esc_html__('Something went wrong, Please try again later','smart-appointment-booking'),
					'error' => $error,
				));
			}
			
			wp_die();
		}
		/**
		 * Saves an individual form submission and creates a manageable submission record under the 'manage-entries' post type.
		 *
		 * This function handles the submission of form data and creates a record that can be managed within the 'manage-entries' post type.
		 *
		 */
		function saab_save_form_submission() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ) , 'my_ajax_nonce' ) ){
				wp_send_json_error( 'Invalid nonce' );
			}

			$form_id = isset($_POST['fid']) ? absint($_POST['fid']) : 0;
			$form_data = isset( $_POST['form_data'] ) ? sanitize_text_field($_POST['form_data']) : array();
			if (is_array($form_data)) {
				foreach ($form_data as $field_name => $field_value) {
					// Check if the field value is an array (e.g., for checkboxes or multi-select)
					if (is_array($field_value)) {
						foreach ($field_value as $key => $value) {
							
							$form_data[$field_name][$key] = sanitize_text_field($value);
						}
					} else {
						
						$form_data[$field_name] = sanitize_text_field($field_value);
					}
				}
			}
			
			$new_post = array(
				'post_title'   => 'entry_#',
				'post_type'    => 'manage_entries',
				'post_status'  => 'publish'
			);
		
			$created_post_id = wp_insert_post($new_post);
			
			// Update the post title with the created post ID
			$new_post_title = 'entry_#' . $created_post_id;

			// Update the post
			$update_post_args = array(
				'ID'         => $created_post_id,
				'post_title' => $new_post_title
			);

			wp_update_post($update_post_args);

			// Update the post slug (permalink)
			$slug = sanitize_title($new_post_title);
			wp_update_post(array('ID' => $created_post_id, 'post_name' => $slug));

			// Update the post's permalink (optional)
			$new_post_permalink = get_permalink($created_post_id);
			update_post_meta($created_post_id, '_wp_old_slug', $new_post_permalink);
			update_post_meta($created_post_id, 'saab_submission_data', $form_data);
			update_post_meta($created_post_id, 'saab_form_id', $form_id);
			
			$prefixlabel = get_post_meta( $form_id, 'saab_label_symbol', true );
			$cost = get_post_meta( $form_id, 'saab_cost', true );

			update_post_meta($created_post_id, 'saab_label_symbol', $prefixlabel);
			update_post_meta($created_post_id, 'saab_cost', $cost);
			
			$submission_key_val = array();				
			foreach($form_data['data'] as $form_key => $form_value){
				if($form_key !== 'submit'){
					$submission_key_val[$form_key] = esc_attr($form_value);
				}
			}
				
			$get_user_mapping = get_post_meta($form_id, 'saab_user_mapping', true);
				
			$getfirst_name = isset($get_user_mapping['first_name']) ? sanitize_text_field($get_user_mapping['first_name']) : '';
			if ($getfirst_name) {
				$first_name = $form_data['data'][$getfirst_name];					
			}
			$getlast_name = isset($get_user_mapping['last_name']) ? sanitize_text_field($get_user_mapping['last_name']) : '';
			if ($getlast_name) {
				$last_name =  $form_data['data'][$getlast_name];					
			}
			$getemail = isset($get_user_mapping['email']) ? sanitize_text_field($get_user_mapping['email']) : '';
			if ($getemail) {
				$emailTo =  $form_data['data'][$getemail];					
			}
			$getservice = isset($get_user_mapping['service']) ? sanitize_text_field($get_user_mapping['service']) : '';
			if ($getservice) {
				$service =  ucfirst($form_data['data'][$getservice]);					
			}			
				
			$encrypted_booking_id = $created_post_id;
			$user_mapping = get_post_meta($form_id, 'saab_user_mapping', true);
			$ajax_nonce = wp_create_nonce('my_ajax_nonce');
			if ($user_mapping) {
				$cancelbooking_pageid = isset($user_mapping['cancel_bookingpage']) ? sanitize_text_field($user_mapping['cancel_bookingpage']) : '';
				$cancelbooking_url = get_permalink($cancelbooking_pageid).'?booking_id=' . $encrypted_booking_id . '&status=cancel&security='.$ajax_nonce;
			} else {
				$cancelbooking_url = home_url('/?booking_id=' . $encrypted_booking_id . '&status=cancel&security='.$ajax_nonce);
			}				
			$other_label_val = array();
			$publishedDate = get_the_date( 'M d,Y', $form_id );
			$FormTitle = get_the_title( $form_id );
			$other_label_val = array(
				'FormId' => $form_id,
				'BookingId' => $created_post_id,
				'FormTitle' => $FormTitle,
				'To' => $emailTo,
				'FirstName' => $first_name,
				'LastName' => $last_name,
				'Timeslot' => '',
				'BookingDate' => $publishedDate,
				'BookingSeats' => '',
				'BookedDate' =>'',					
				'Service' => $service,
				'prefixlabel' => $prefixlabel,
				'cost' => $cost,					
				'slotcapacity' => '',
				'bookedseats' => '',	
				'form_data' => $form_data,
				'no_of_seats' => '',
				'tot_no_of_seats' => '',
				'StartTime' => '',
				'EndTime' => '',
				'CancelBooking' => '',
			);
				$status = 'submitted';
				update_post_meta($created_post_id, 'saab_entry_status', $status);
				$listform_label_val = array_merge($submission_key_val, $other_label_val);
				$mail_response = $this->saab_send_notification($status,$form_id, $created_post_id, $listform_label_val );
				
				$confirmation = get_post_meta($form_id, 'saab_confirmation', true);
				$redirect_url = '';
				
				if ($confirmation == 'redirect_text') {
					$wp_editor_value = get_post_meta($form_id, 'saab_redirect_text', true);					
				    $editor_value = wpautop(wp_kses_post($wp_editor_value));
				} elseif ($confirmation == 'redirect_page') {
					$redirect_page = get_post_meta($form_id, 'saab_redirect_page', true);
					$redirect_url = get_permalink($redirect_page);
				} elseif ($confirmation == 'redirect_to') {
					$redirect_url = get_post_meta($form_id, 'saab_redirect_to', true);
				}
				
				wp_send_json_success(array(					
					'message' => 'Sucessfully Submitted',
					'redirect_page' => $redirect_url,
					'wp_editor_value' => $editor_value,
					'redirect_url' => $redirect_url,
					'mail_response' =>$mail_response,
					'confirmation' => $confirmation
				));

			wp_die();
		}
		/**
		 * Sends notification based on the provided status for a Smart Appointment & Booking form.
		 *
		 * @param string $status      The status for which the notification is triggered.
		 * @param int    $form_id     The ID of the Smart Appointment & Booking form.
		 * @param int    $post_id     The ID of the post associated with the form.
		 * @param array  $form_data   The form data to be used in the notification.
		 *
		 * @return string             A message indicating the result of the email sending process.
		 */
		function saab_send_notification($status,$form_id, $post_id, $form_data	) {
			$message = '';
			$get_notification_array = get_post_meta($form_id, 'saab_notification_data', true);	
			
			$notificationFound = false;
			foreach ($get_notification_array as $notification) {
				
				if ($notification['state'] === 'enabled' && $notification['type'] === $status) {
					$notificationFound = true;
					$check_to = $notification['to'];
					$check_replyto = $notification['replyto'];
					$check_bcc = $notification['bcc'];
					$check_cc = $notification['cc'];
					$check_from = $notification['from'];
					$subject = $notification['subject'];
					$check_body = $notification['mail_body'];
					
					$shortcodesArray = $this->front_get_shortcodes($form_id);

					$to = $this->saab_check_shortcode_exist($check_to,$form_id, $form_data,$shortcodesArray );
					$from = $this->saab_check_shortcode_exist($check_from,$form_id, $form_data,$shortcodesArray );
					$replyto = $this->saab_check_shortcode_exist($check_replyto,$form_id, $form_data,$shortcodesArray );
					$bcc = $this->saab_check_shortcode_exist($check_bcc,$form_id, $form_data ,$shortcodesArray );
					$cc = $this->saab_check_shortcode_exist($check_cc,$form_id, $form_data,$shortcodesArray );
					$check_body = $this->saab_check_shortcodes_exist_in_editor($check_body,$form_id, $form_data,$shortcodesArray );
					
					$notification['use_html'];
					$headers = array(						
						'From: ' . $from,
						'Reply-To: ' . $replyto,
						'Bcc: ' . $bcc,
						'Cc: ' . $cc
					);

					if($notification['use_html'] == 1){
						$headers[] = 'Content-Type: text/html; charset=UTF-8';
					}else{
						$headers[] = 'Content-Type: text/plain; charset=UTF-8';
					}
					$loop = 1;
					$result = wp_mail($to, $subject, $check_body, $headers);		
					if ($result === true) {
						$message = esc_html__('Email sent successfully','smart-appointment-booking');
					} else {
						$message = esc_html__('Failed to send email','smart-appointment-booking');
						error_log('Failed to send email');
					}
				}
			
			}
			if ($notificationFound === false) {
				$message = esc_html__('Notification not found for the given status', 'smart-appointment-booking');
				error_log('Notification not found for the given status');
			}
			return $message;
		}
		/**
		 * Process the given field value containing shortcodes and replace them with actual values.
		 *
		 * @param string $fieldValue  The field value containing shortcodes.
		 * @param int    $form_id     The ID of the Smart Appointment & Booking form.
		 * @param array  $form_data   The form data to be used for shortcode replacement.
		 * @param array  $dataArray   An array of available shortcodes.
		 *
		 * @return string|null        The processed field value with replaced shortcodes or null if any replacement failed.
		 */
		function saab_check_shortcode_exist($fieldValue, $form_id, $form_data,$dataArray) {
			
			$fieldValue_exploded = explode(',', $fieldValue);
			$processed_fieldValue = [];
		
			foreach ($fieldValue_exploded as $index => $Value_exploded) {
				$Value_exploded = trim($Value_exploded);
				foreach ($dataArray as $shortcode) {
					if (strpos($Value_exploded, $shortcode) !== false) {
						if ($shortcode === '[To]') {
							$get_user_mapping = get_post_meta($form_id, 'saab_user_mapping', true);
							$email = isset($get_user_mapping['email']) ? sanitize_text_field($get_user_mapping['email']) : '';
							if ($email) {
								$to_email = $form_data[$email];
								if (is_email($to_email)) {
									$Value_exploded = str_replace('[To]', $to_email, $Value_exploded);
								} else {
									$Value_exploded = null; 
									break;
								}
							} else {
								$Value_exploded = null;
								break;
							}
						} else {
							$shcodeWithoutBrackets = str_replace(['[', ']'], '', $shortcode);
							$othershval = $form_data[$shcodeWithoutBrackets];
							if ($othershval && is_email($othershval)) {
								$Value_exploded = str_replace($shortcode, $othershval, $Value_exploded);
							} else {
								$Value_exploded = null;
								break;
							}
						}
					}
				}
				if ($Value_exploded !== null) {
					$processed_fieldValue[] = $Value_exploded;
				}
			}
		
			$to = implode(',', $processed_fieldValue);
			return $to; 
		}
		/**
		 * Process the given field value containing shortcodes within an editor and replace them with actual values.
		 *
		 * @param string $fieldValue  The field value containing shortcodes.
		 * @param int    $form_id     The ID of the Smart Appointment & Booking form.
		 * @param array  $form_data   The form data to be used for shortcode replacement.
		 * @param array  $shortcodes  An array of available shortcodes.
		 *
		 * @return string             The processed field value with replaced shortcodes.
		 */
		function saab_check_shortcodes_exist_in_editor($fieldValue, $form_id, $form_data, $shortcodes) {
			foreach ($shortcodes as $shortcode) {
				$shcodeWithoutBrackets = str_replace(['[', ']'], '', $shortcode);
				$shortcodePattern = '/\[' . preg_quote($shcodeWithoutBrackets, '/') . '\]/';
		
				if (preg_match($shortcodePattern, $fieldValue)) {

					$keyExists = isset($form_data[$shcodeWithoutBrackets]);
					if ($keyExists) {
						$fieldValue = str_replace('[' . $shcodeWithoutBrackets . ']', $form_data[$shcodeWithoutBrackets], $fieldValue);
					} else {
						$fieldValue = str_replace('[' . $shcodeWithoutBrackets . ']', '', $fieldValue);
					}
				}
			}
			return $fieldValue;
		}
		/**
		 * Retrieve a list of shortcodes based on the form schema and additional predefined shortcodes.
		 *
		 * @param int $form_id  The ID of the Smart Appointment & Booking form.
		 *
		 * @return array An array containing the list of shortcodes.
		 */
		function front_get_shortcodes($form_id){
			$shortcode_list = array();
			$form_data1 = get_post_meta( $form_id, 'saab_formschema', true ); 
			$form_data1=json_decode($form_data1);
			foreach ($form_data1 as $obj) { 
				$shortcode_list[] = "[".$obj->key."]";
			}
			$tobe_merged = array('[FormId]', '[BookingId]', '[Status]', '[FormTitle]', '[To]', '[FirstName]', '[LastName]', '[Timeslot]', '[BookedSeats]', '[BookingDate]', '[BookedDate]', '[Service]', '[prefixlabel]', '[cost]', '[StartTime]', '[EndTime]', '[CancelBooking]');
			$shortcode_list = array_merge($tobe_merged,$shortcode_list);

			return $shortcode_list;
		}
		/**
		 * Process recurring dates based on the provided parameters.
		 *
		 * @param int    $post_id  The ID of the Smart Appointment & Booking form.
		 * @param string $date     The date to start processing recurring dates.
		 *
		 * @return array|false     An array of processed recurring dates or false if post_id is null.
		 */
		function saab_processDate($post_id = null, $date = null) {
			if ($post_id === null) {
				return false;
			} else {
				$check_type = get_post_meta($post_id, 'saab_enable_recurring_apt', true);
				
				$holiday_dates = get_post_meta($post_id, 'saab_holiday_dates', true);
				if (isset($holiday_dates) && empty($holiday_dates)) {
					$holiday_dates = array();
				}
				$arrayofdates = array(); 
				$arrayof_advdates = array();
				$enable_advance_setting = get_post_meta($post_id, 'saab_enable_advance_setting', true);
				$selected_date = get_post_meta($post_id, 'saab_selected_date', true);
				if($enable_advance_setting && $enable_advance_setting){
					$advancedata = get_post_meta($post_id, 'saab_advancedata', true);
					foreach ($advancedata as $index => $data) {
						
						if (!in_array($data['advance_date'], $holiday_dates)) {
							$arrayof_advdates[] = $data['advance_date'];
						}
						
					}
				}
				if ($check_type) {
					
					$weekdays_num = array();
					$weekdays = get_post_meta($post_id, 'saab_weekdays', true);					
					$all_days = array("monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday");
					$todays_date = gmdate("Y-m-d");
					
                    if($date < $selected_date || $date < $todays_date){
                        $arrayofdates = array();
                    }else{
						
                      	$recurring_type = get_post_meta($post_id, 'saab_recurring_type', true);	
                        $end_repeats_type = get_post_meta($post_id, 'saab_end_repeats', true);
                        if ($end_repeats_type == 'on') {
                            $end_repeats_on = get_post_meta($post_id, 'saab_end_repeats_on',true);
                            $end_repeats_on_date = gmdate("Y-m-d", strtotime($end_repeats_on));
                        }else{
							if(!empty($date)){
								$end_repeats_on_date = gmdate("Y-m-d", strtotime($date));
							}else{
								$end_repeats_on_date = gmdate("Y-m-d", strtotime($selected_date));
							}
							
						}
						
						if ($recurring_type == 'weekend') {

							$remaining_days = array_diff($all_days, $weekdays);
							foreach ($remaining_days as $wdays) {
								$weekdays_num[] = gmdate('N', strtotime($wdays));
							}
							
							$startDate = strtotime($date);
							$endDate = strtotime($end_repeats_on_date);
							// return $selected_date = gmdate('Y-m-d', $selected_date);
							while ($startDate <= $endDate) {
								
								$dayOfWeek = gmdate('N', $startDate); 
								$currentDate = gmdate('Y-m-d', $startDate);
							
								if (in_array($dayOfWeek, $weekdays_num)) {
									if(isset($holiday_dates) && !empty($holiday_dates) ) {
										if(!in_array($currentDate, $holiday_dates)){
											$arrayofdates[] = $currentDate; 
										}
									}else{
										$arrayofdates[] = $currentDate; 
									}
									
								}
								$startDate = strtotime('+1 day', $startDate); 
							}
							$arrayofdates[] = $selected_date; 
							
						}elseif ($recurring_type == 'weekdays') { 
                            
                            foreach ($weekdays as $wdays) {
                                $weekdays_num[] = gmdate('N', strtotime($wdays));
                            }
							
							$startDate = strtotime($date);
							$endDate = strtotime($end_repeats_on_date);

							while ($startDate <= $endDate) {
								$dayOfWeek = gmdate('N', $startDate); 
								$currentDate = gmdate('Y-m-d', $startDate);

								if (in_array($dayOfWeek, $weekdays_num)) {
									if(isset($holiday_dates) && !empty($holiday_dates) ) {
										if(!in_array($currentDate, $holiday_dates)){
											$arrayofdates[] = $currentDate; 
										}
									}else{
										$arrayofdates[] = $currentDate; 
									}
									
								}
								$startDate = strtotime('+1 day', $startDate); 
							}
                            
                        }elseif ($recurring_type == 'certain_weekdays') {
							
                            $certain_weekdays_array = get_post_meta($post_id, 'saab_recur_weekdays', true);
							
                            foreach ($certain_weekdays_array as $wdays) {
                                $weekdays_num[] = gmdate('N', strtotime($wdays));
                            }
							
							$startDate = strtotime($date);
							$endDate = strtotime($end_repeats_on_date);

							while ($startDate <= $endDate) {
								$dayOfWeek = gmdate('N', $startDate); 
								$currentDate = gmdate('Y-m-d', $startDate);

								if (in_array($dayOfWeek, $weekdays_num)) {
									if(isset($holiday_dates) && !empty($holiday_dates) ) {
										if(!in_array($currentDate, $holiday_dates)){
											$arrayofdates[] = $currentDate; 
										}
									}else{
										$arrayofdates[] = $currentDate; 
									}
									
								}
								$startDate = strtotime('+1 day', $startDate); 
							}
                        }elseif ($recurring_type == 'daily') {
						
							$startDate = strtotime($date);
							$endDate = strtotime($end_repeats_on_date);
							$dayOfWeek = gmdate('N', $startDate); 
							$currentDate = gmdate('Y-m-d', $startDate);
							$str_currentDate = strtotime($currentDate);
						
							if ( $str_currentDate <= $endDate) {
								if (!in_array($currentDate, $holiday_dates)) {
									$arrayofdates[] = $currentDate; 
								}
							}
						}
                    }
					$new_array = array_merge($arrayof_advdates,$arrayofdates);
					return $new_array;

				}else{
					$arrayofdates[] = $selected_date;
					return $arrayofdates;
				}
			}	
		}
		/**
		 * Generate Timeslots if advanced timeslots is enabled
		 */
		function saab_get_advanced_timeslots($post_id,$booking_date,$inputdate){
			
			$no_of_booking = get_post_meta($post_id, 'saab_no_of_booking', true); 
			$output_timeslot = '';
			$check_type = get_post_meta($post_id, 'saab_enable_recurring_apt', true);
			if ($check_type) {
				$recurring_type = get_post_meta($post_id, 'saab_enable_advance_setting', true);				
			}

			if($check_type && $recurring_type == 1){
				$advancedata = get_post_meta($post_id, 'saab_advancedata', true);
				$get_timezone = get_post_meta($post_id,'saab_timezone',true);                
                // date_default_timezone_set($get_timezone);
				if ($get_timezone) {
					wp_timezone_override_offset(false, $get_timezone);
				}
				$current_time = time();
				
				foreach ($advancedata as $item) {
					$advanceDates[] = $item['advance_date'];
				}
				//get booking_stops_after duration
				$timeslot_BookAllow = get_post_meta($post_id, 'saab_timeslot_BookAllow', true);
				$booking_stops_after = get_post_meta( $post_id, 'saab_booking_stops_after', true );
				if (!empty($booking_stops_after)) {
					
					$booking_stops_after_array = array(
						'hours' => sanitize_text_field($booking_stops_after['hours']),
						'minutes' => sanitize_text_field($booking_stops_after['minutes'])
					);
					$booking_stops_after_hours = $booking_stops_after_array['hours'];
					$booking_stops_after_minutes = $booking_stops_after_array['minutes'];
				
					$booking_stops_after_sec = $booking_stops_after_hours . ':' . $booking_stops_after_minutes . ':00';
					if (!empty($booking_stops_after_sec)) {
						$booking_stops_after_duration_seconds = ($booking_stops_after_array['hours'] * 3600) + ($booking_stops_after_array['minutes'] * 60);
					}
				}
				foreach ($advancedata as $index => $data) {
					if($data['advance_date'] == $inputdate){
						foreach ($data['advance_timeslot'] as $slot_index => $timeslot) {
							// Format the start time and end time of the timeslot
							$start_time = isset($timeslot['start_time']) ? $timeslot['start_time'] : '';
							$end_time = isset($timeslot['end_time']) ? $timeslot['end_time'] : '';

							$start_timestamp = strtotime($timeslot['start_time']);
							$end_timestamp = strtotime($timeslot['end_time']);

							$start_hours = gmdate('h', strtotime($timeslot['start_time']));
							$sampm = gmdate('a', strtotime($start_hours));

							$end_hours = gmdate('h', strtotime($timeslot['end_time']));
							$sampm = gmdate('a', strtotime($end_hours));	

							$start_timeslot = gmdate('h:i A', strtotime($timeslot['start_time']));
							$end_timeslot = gmdate('h:i A',strtotime($timeslot['end_time']));
							
							$checktimeslot = $start_timeslot."-".$end_timeslot;
							
							$checkseats = $this->saab_get_available_seats_per_timeslot($checktimeslot,$booking_date);

							$waiting_text = '';
							$iswaiting_alllowed = get_post_meta( $post_id,'saab_waiting_list', true );
							if(!$iswaiting_alllowed){
								$iswaiting_alllowed = 0;
							}
							
							if($checkseats >  $timeslot['bookings'] ){
								$available_seats = 0;
								$available_input_seats = 0;
								if(($available_input_seats == 0) && ($iswaiting_alllowed == 1)){
									$available_input_seats = $no_of_booking;
		
								}
							}else{
								
								$available_seats = $timeslot['bookings'] - $checkseats;
								$available_input_seats = $timeslot['bookings'] - $checkseats;
							}

							$selected_date = get_post_meta( $post_id,'saab_selected_date', true );
							$check_date = 0;
							if(!in_array($inputdate,$advanceDates)){
								$check_date = 1;
							}

							
							$explodedArray = explode("_", $booking_date);
							
							// Extracting month, day, and year components
							$month = str_pad($explodedArray[2], 2, "0", STR_PAD_LEFT);
							$day = $explodedArray[3];
							$year = $explodedArray[4];
							$givenTimestamp = strtotime("$year-$month-$day");
							$todayTimestamp = strtotime('today');
							$waiting_seats = 0;
							$waiting_text= '';
							if($givenTimestamp > $todayTimestamp){
								if ($available_seats <= 0) {
									if ($iswaiting_alllowed == 1) {
										$waiting_text = "Waiting: Allowed";
										$output_timeslot .= '<li class="saab_timeslot" onclick="selectTimeslot(this)" >';
										$waiting_seats =  $timeslot['bookings'];
									} else {
										$output_timeslot .= '<li class="saab_timeslot" >';
									}
								} else {
									$output_timeslot .= '<li class="saab_timeslot" onclick="selectTimeslot(this)" >';
								}
								$available_text = esc_html(__('Available seats: ', 'smart-appointment-booking')) . esc_html($available_seats);

							}else{
								if ($current_time >= $start_timestamp) {
								
									$start_datetime = new DateTime($start_time);
									$end_datetime = new DateTime($end_time);
									$time_difference = $start_datetime->diff($end_datetime);	
								
									$time_diff_total_seconds = ($time_difference->h * 3600) + ($time_difference->i * 60) + $time_difference->s;
									if ($time_diff_total_seconds >= $booking_stops_after_duration_seconds) {
									
										$this_start_time = $start_timestamp + $booking_stops_after_duration_seconds;
										if ($current_time < $this_start_time) {
											
											if ($available_seats <= 0) {
												if ($iswaiting_alllowed == 1) {
													$waiting_text = "Waiting: Allowed";
													$output_timeslot .= '<li class="saab_timeslot" onclick="selectTimeslot(this)" >';
													$waiting_seats =  $timeslot['bookings'];
												} else {
													$output_timeslot .= '<li class="saab_timeslot" >';
												}
											} else {
												$output_timeslot .= '<li class="saab_timeslot" onclick="selectTimeslot(this)" >';
											}
											$available_text = esc_html__('Available seats : ','smart-appointment-booking').$available_seats;
										}else{
											$output_timeslot .= '<li class="saab_timeslot" >';
											$available_seats = 0;
											$available_text = esc_html__('Timeslot Not available','smart-appointment-booking');
											$waiting_text = '';
										}
									}else{
										$output_timeslot .= '<li class="saab_timeslot" >';
										$available_seats = 0;
										$available_text = esc_html__('Timeslot Not available','smart-appointment-booking');
										$waiting_text = '';
									}
									
								} else {
								
									if ($available_seats <= 0) {
										if ($iswaiting_alllowed == 1) {
											$waiting_text = "Waiting: Allowed";
											$output_timeslot .= '<li class="saab_timeslot" onclick="selectTimeslot(this)" >';
											$waiting_seats =  $timeslot['bookings'];
										} else {
											$output_timeslot .= '<li class="saab_timeslot" >';
										}
									} else {
										$output_timeslot .= '<li class="saab_timeslot" onclick="selectTimeslot(this)" >';
									}
									$available_text = esc_html(__('Available seats: ', 'smart-appointment-booking')) . esc_html($available_seats);
								}
							}

							$output_timeslot .= '<span>' . htmlspecialchars($start_timeslot, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($end_timeslot, ENT_QUOTES, 'UTF-8') . '</span>';
							$output_timeslot .= '<input class="saab-selected-time" name="booking_slots" type="hidden" value="' . htmlspecialchars($start_timeslot . '-' . $end_timeslot, ENT_QUOTES, 'UTF-8') . '">';
							$output_timeslot .= '<span class="saab-tooltip-text" data-seats="' . htmlspecialchars($available_input_seats, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($available_text, ENT_QUOTES, 'UTF-8') . '<br>' . htmlspecialchars($waiting_text, ENT_QUOTES, 'UTF-8') . '</span>';
							$output_timeslot .= '<span class="saab-waiting" style="display:none;" class="hidden" data-checkdate="' . htmlspecialchars($check_date, ENT_QUOTES, 'UTF-8') . '" data-waiting="' . htmlspecialchars($iswaiting_alllowed, ENT_QUOTES, 'UTF-8') . '" data-seats="' . htmlspecialchars($waiting_seats, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($iswaiting_alllowed, ENT_QUOTES, 'UTF-8') . '</span>';
							$output_timeslot .= '</li>';
							
						
						}
					}
				}
			}
			return $output_timeslot;
		}
		/**
		 * generate timeslots for Regular setting
		 */
		function saab_front_generate_timeslots($post_id, $todaysDate = null){
			$output_timeslot = '';
			$generatetimeslot = get_post_meta($post_id, 'saab_generatetimeslot', true);	
			$waiting_seats = 0;
			if($generatetimeslot){
				//set timezone
				$get_timezone = get_post_meta($post_id,'saab_timezone',true);
				// date_default_timezone_set($get_timezone);
				if ($get_timezone) {
					wp_timezone_override_offset(false, $get_timezone);
				}
				//get booking_stops_after duration
				$timeslot_BookAllow = get_post_meta($post_id, 'saab_timeslot_BookAllow', true);
				$booking_stops_after = get_post_meta( $post_id, 'saab_booking_stops_after', true );
				if (!empty($booking_stops_after)) {					
					$booking_stops_after_array = array(
						'hours' => sanitize_text_field($booking_stops_after['hours']),
						'minutes' => sanitize_text_field($booking_stops_after['minutes'])
					);
					$booking_stops_after_hours = $booking_stops_after_array['hours'];
					$booking_stops_after_minutes = $booking_stops_after_array['minutes'];
				
					$booking_stops_after_sec = $booking_stops_after_hours . ':' . $booking_stops_after_minutes . ':00';
					if (!empty($booking_stops_after_sec)) {
						$booking_stops_after_duration_seconds = ($booking_stops_after_array['hours'] * 3600) + ($booking_stops_after_array['minutes'] * 60);
					}
				}
				$no_of_booking = get_post_meta($post_id, 'saab_no_of_booking', true); 				
				foreach ($generatetimeslot as $index => $timeslot) {
					
					$current_time = time();
					$start_time = isset($timeslot['start_time']) ? $timeslot['start_time'] : '';
					$end_time = isset($timeslot['end_time']) ? $timeslot['end_time'] : '';
					$start_timestamp = strtotime($start_time);
					$current_timewe = gmdate('h:i A', $current_time);
					$end_timestamp = strtotime($end_time);
					$start_time_slot = gmdate('h:i A', $start_timestamp);
					$end_time_slot = gmdate('h:i A', $end_timestamp);
					
					// Add the timeslot to the available timeslots array
					$available_timeslots[] = $start_time_slot . ' - ' . $end_time_slot;
					$checktimeslot = $start_time_slot."-".$end_time_slot;
				
					$iswaiting_alllowed = get_post_meta( $post_id,'saab_waiting_list', true );
					if(!$iswaiting_alllowed){
						$iswaiting_alllowed = 0;
						
					}					
					$checkseats = $this->saab_get_available_seats_per_timeslot($checktimeslot,$todaysDate);
					$selected_date = get_post_meta( $post_id,'saab_selected_date', true );
					$check_date = 0;					
					
					$explodedArray = explode("_", $todaysDate);
					// Extracting month, day, and year components
					$month = str_pad($explodedArray[2], 2, "0", STR_PAD_LEFT);
					$day = $explodedArray[3];
					$year = $explodedArray[4];
					$givenTimestamp = strtotime("$year-$month-$day");
					$todayTimestamp = strtotime('today');
					if($todaysDate < $selected_date){
						$check_date = 1;
					}
					
					if($checkseats >  $no_of_booking ){
						$available_seats = 0;
						$available_input_seats = 0;
						if(($available_input_seats == 0) && ($iswaiting_alllowed == 1)){
							$available_input_seats = $no_of_booking;
						}
					}else{
						$available_seats = $no_of_booking - $checkseats;
						$available_input_seats = $no_of_booking - $checkseats;
					}
					$waiting_text= '';
					if($givenTimestamp > $todayTimestamp){
						if ($available_seats <= 0) {
							if ($iswaiting_alllowed == 1) {
								$waiting_text = "Waiting: Allowed";
								$waiting_seats = $no_of_booking;
							}
							$output_timeslot .= '<li class="saab_timeslot"' . (($available_seats > 0 || $iswaiting_alllowed) ? ' onclick="selectTimeslot(this)"' : '') . '>';
						} else {
							$output_timeslot .= '<li class="saab_timeslot" onclick="selectTimeslot(this)" >';
						}
						
						$available_text = 'Available seats : ' . $available_seats;
						
					}else{
						
						if ($current_time >= $start_timestamp) {
							$start_datetime = new DateTime($start_time);
							$end_datetime = new DateTime($end_time);
							$time_difference = $start_datetime->diff($end_datetime);	
						
							$time_diff_total_seconds = ($time_difference->h * 3600) + ($time_difference->i * 60) + $time_difference->s;
							if ($time_diff_total_seconds >= $booking_stops_after_duration_seconds) {
								$this_start_time = $start_timestamp + $booking_stops_after_duration_seconds;
								if ($current_time < $this_start_time) {
									if ($available_seats <= 0) {
										if ($iswaiting_alllowed == 1) {
											$waiting_text = "Waiting: Allowed";
											$output_timeslot .= '<li class="saab_timeslot" onclick="selectTimeslot(this)" >';
											$waiting_seats  = $no_of_booking;
										} else {
											$output_timeslot .= '<li class="saab_timeslot" >';
										}
									} else {
										$output_timeslot .= '<li class="saab_timeslot" onclick="selectTimeslot(this)" >';
									}
									$available_text = 'Available seats: ' . esc_attr($available_seats);
								}else{
									$output_timeslot .= '<li class="saab_timeslot" >';
									$available_seats = 0;
									$available_text = 'Timeslot Not available';
									$waiting_text = '';
								}
							}else{
								$output_timeslot .= '<li class="saab_timeslot" >';
								$available_seats = 0;
								$available_text = 'Timeslot Not available';
								$waiting_text = '';
							}
							
						} else {
						
							if ($available_seats <= 0) {
								if ($iswaiting_alllowed == 1) {
									$waiting_text = "Waiting: Allowed";
									$output_timeslot .= '<li class="saab_timeslot" onclick="selectTimeslot(this)" >';
									$waiting_seats  = $no_of_booking;
								} else {
									$output_timeslot .= '<li class="saab_timeslot" >';
								}
							} else {
								$output_timeslot .= '<li class="saab_timeslot" onclick="selectTimeslot(this)" >';
							}
							$available_text = 'Available seats: ' . esc_attr($available_seats);

						}
					}
					
					$output_timeslot .= '<span>' . htmlspecialchars($start_time_slot, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($end_time_slot, ENT_QUOTES, 'UTF-8') . '</span>';
					$output_timeslot .= '<input class="saab-selected-time" name="booking_slots" data-startime="' . htmlspecialchars($this_start_time, ENT_QUOTES, 'UTF-8') . '" type="hidden" value="' . htmlspecialchars($start_time_slot . '-' . $end_time_slot, ENT_QUOTES, 'UTF-8') . '">';
					$output_timeslot .= '<span class="saab-tooltip-text" data-seats="' . htmlspecialchars($available_input_seats, ENT_QUOTES, 'UTF-8') . '"> ' . htmlspecialchars($available_text, ENT_QUOTES, 'UTF-8') . '<br>' . htmlspecialchars($waiting_text, ENT_QUOTES, 'UTF-8') . '</span>';
					$output_timeslot .= '<span class="saab-waiting" style="display:none;" class="hidden" data-checkdate="' . htmlspecialchars($check_date, ENT_QUOTES, 'UTF-8') . '" data-waiting="' . htmlspecialchars($iswaiting_alllowed, ENT_QUOTES, 'UTF-8') . '" data-seats="' . htmlspecialchars($waiting_seats, ENT_QUOTES, 'UTF-8') . '" >' . htmlspecialchars($iswaiting_alllowed, ENT_QUOTES, 'UTF-8') . '</span>';
					$output_timeslot .= '</li>';
									
				}
			}else{
				$output_timeslot .= '<li class="saab_timeslot">';
				$output_timeslot .= 'No Timeslot Found';
				$output_timeslot .= '</li>';
			}
			return $output_timeslot;
		}
		/**
		 * Retrieves the number of available seats for a specific timeslot on a given booking date.
		 *
		 * This function queries the database or performs calculations to determine the available seats for the provided timeslot and booking date.
		 *
		 * @param string $timeslot The timeslot to check for available seats.
		 * @param string $booking_date The date of the booking.
		 *
		 */
        function saab_get_available_seats_per_timeslot($timeslot,$booking_date){
        	
            $args = array(
                'post_type' => 'manage_entries',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'timeslot',
                        'value' => $timeslot,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'booking_date',
                        'value' => $booking_date,
                        'compare' => '='
                    )
                )
            );
            
            $query = new WP_Query($args);
			
			if ($query->have_posts()) {
				$post_count = $query->found_posts;
				$no_of_booking = 0; 
				$arr = array();
				while ($query->have_posts()) {
					$query->the_post();
					$slotcapacity = get_post_meta(get_the_ID(), 'saab_slotcapacity', true);	
					$booking_status = get_post_meta(get_the_ID(), 'saab_entry_status', true);	
					if($booking_status === 'booked' || $booking_status === 'approved'){
						$no_of_booking += $slotcapacity;
					}					
				}
				wp_reset_postdata();				
			
			} else {
				$no_of_booking = 0;
			}
            return $no_of_booking;
        }
		/**
		 * Display a booking form in the frontend that has been created using the form builder.
		 *
		 * This function generates and displays a booking form on the frontend based on the form created using the form builder.
		 *
		 * @param array $attr An array of attributes, typically containing 'form_id' to specify the form to display.
		 *
		 * @return void Outputs the generated booking form to the frontend.
		 */
		function saab_get_booking_form($attr) {
			ob_start();				
			$post_id = isset($attr['form_id']) ? absint($attr['form_id']) : '';			
			if (empty($post_id)) {
				return ob_get_clean();
			}
			$enable_booking = get_post_meta($post_id, 'saab_enable_booking', true);
			$prefix_label = get_post_meta($post_id, 'saab_label_symbol', true);
			$cost = get_post_meta($post_id, 'saab_cost', true);
			
			if(isset($enable_booking) && !empty($enable_booking)){	
				$cal_title = get_post_meta($post_id, 'saab_cal_title', true);
				$cal_description = get_post_meta($post_id, 'saab_cal_description', true);
				$currentMonth = gmdate('m');
				$currentYear = gmdate('Y');

				// Remove leading zeros from the current month
				$currentMonth = ltrim($currentMonth, '0');

				// Create an array of month names
				$monthNames = array(
					1 => 'January',
					2 => 'February',
					3 => 'March',
					4 => 'April',
					5 => 'May',
					6 => 'June',
					7 => 'July',
					8 => 'August',
					9 => 'September',
					10 => 'October',
					11 => 'November',
					12 => 'December'
				);

				// Get the first day of the current month
				$firstDayOfWeek = gmdate('w', strtotime($currentYear . '-' . $currentMonth . '-01'));
				$firstDayOfWeek += 1;
				
				// Output the calendar
				?>
				<div class='saab-smart-calender container alignwide' id='calender_reload'>
					<div class="step step1">
						<div class=''>
							<span class='saab-caltitle'><?php echo esc_html($cal_title); ?></span>
							<p class='saab-cal-desc'><?php echo esc_html($cal_description); ?></p>
						</div>
						<div class="month-navigation saab-cal-container" id="month-navigationid">
							<div class="header-calender">
								<input type="hidden" id="zealform_id" value="<?php echo esc_attr($post_id); ?>">
								
								<span class="arrow" id="prev-month" onclick="getClicked_prev(this)">&larr;</span>
								<!-- months -->
								<select name='saab_month_n' id='saab_month'>
									<?php
									for ($i = 1; $i <= 12; $i++) {
										echo "<option value='" . esc_attr($i) . "'";
										if ($i == $currentMonth) {
											echo " selected";
										}
										echo ">{$monthNames[$i]}</option>";
									}
									?>
								</select>
								<!-- Year -->
								<select name="saab_year_n" id="saab_year">
									<?php
									$startYear = $currentYear + 5;
									$endYear = 2023; //as plugin has been plubished there will be no previous entry
									for ($year = $startYear; $year >= $endYear; $year--) {
										echo "<option value='" . esc_attr($year) . "'";
										if ($year == $currentYear) {
											echo " selected";
										}
										echo ">" . esc_html($year) . "</option>";										
									}
									?>
								</select>
								<span class="arrow" id="next-month" onclick="getClicked_next(this)">&rarr;</span>
							</div>
							
						
							<table class="saab-cal-table saab-cal-table-bordered" id="booking_cal_table">
								<tr>
									<th>Sun</th>
									<th>Mon</th>
									<th>Tue</th>
									<th>Wed</th>
									<th>Thu</th>
									<th>Fri</th>
									<th>Sat</th>
								</tr>

								<?php
								$totalDays = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
								$daysInPreviousMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth - 1, $currentYear);

								// Calculate the number of cells needed
								$totalCells = ceil(($totalDays + $firstDayOfWeek) / 7) * 7;

								$dayCounter = 1;
								$date = 1;
								$monthYear = $currentMonth . '-' . $currentYear;

								while ($dayCounter <= $totalCells) {
									echo "<tr>";
									for ($i = 0; $i < 7; $i++) {
										if ($dayCounter >= $firstDayOfWeek && $date <= $totalDays) {
											$isToday = ($date == gmdate('j') && $monthYear == gmdate('n-Y')) ? "calselected_date" : "";
											if ($isToday === "calselected_date") {
												$lastdateid = 'saabid_' . absint($post_id) . '_' . absint($currentMonth) . '_' . sanitize_text_field($date) . '_' . absint($currentYear);
												$lastday = $date;
												$lastmonth = $currentMonth;
												$lastyear = $currentYear;
											}
											echo '<td id="saabid_' . esc_attr($post_id) . '_' . esc_attr($currentMonth) . '_' . esc_attr($date) . '_' . esc_attr($currentYear) . '" data_day="saabid_' . esc_attr($post_id) . '_' . esc_attr($currentMonth) . '_' . esc_attr($date) . '_' . esc_attr($currentYear) . '" class="saab_cal_day ' . esc_attr($isToday) . '" onclick="getClickedId(this)">' . esc_html($date) . '</td>';
											$date++;
										} elseif ($dayCounter < $firstDayOfWeek) {
											$prevDate = $daysInPreviousMonth - ($firstDayOfWeek - $dayCounter) + 1;
											echo '<td class="previous-month">' . esc_html($prevDate) . '</td>';

										} else {
											$nextDate = $dayCounter - ($totalDays + $firstDayOfWeek) + 1;
											echo '<td class="next-month">' . esc_html($nextDate) . '</td>';
										}
										$dayCounter++;
									}

									echo "</tr>";
								}
								?>
							</table>
					
						</div>
						<!-- // Output the additional div with the provided heading and time slots -->
						<div class='timeslot_result_c' id='saab-timeslots-table-container' style='display: inline-block; vertical-align: top;'>
							<?php
							$timezone = get_post_meta($post_id,'saab_timezone',true);
							$error = false;
							$TodaysDate = gmdate('F d, Y');	
							$todaysDate = gmdate('Y-m-d');
							echo "<h3 id='head_avail_time'><span class='gfb-timezone'>Timezone: " . esc_html($timezone) . "</span></h3>";
							echo "<h4 id='headtodays_date'>" . esc_html($TodaysDate) . "</h4>";								
							// Get array of available dates 
							$is_available = $this->saab_processDate($post_id,$todaysDate);							
							?>
							<ul id='saab-slot-list'>
								<?php		
								if(isset($is_available) && is_array($is_available) && in_array($todaysDate,$is_available)){
									$check_type = get_post_meta($post_id, 'saab_enable_recurring_apt', true);
									$enable_advance_setting = get_post_meta($post_id, 'saab_enable_advance_setting', true);
									 
									$advancedata = get_post_meta($post_id, 'saab_advancedata', true);
									foreach ($advancedata as $item) {
										$advanceDates[] = $item['advance_date'];
									}
									if(in_array($todaysDate,$advanceDates)){
									  echo esc_html($this->saab_get_advanced_timeslots($post_id,$lastdateid,$todaysDate));   
									}else{
										echo esc_html($this->saab_front_generate_timeslots($post_id,$lastdateid));                                
									}        
								}else {
									$error = true;
									error_log('Check End date! Selected date exceed the selected end date');
								}
									
								?>
							</ul>
							<?php 
								if($error === true){
									echo esc_html__('No timeslots found for selected date.','smart-appointment-booking');
								}else{
									echo '<input class="saab-selected-capacity" type="number" name="saabslotcapacity" pattern="^[1-9]\d*$" placeholder="Enter Slot Capacity" min="1" value="1">';
									echo '<p id="no-timeslots-message" class="h5" style="display: none;">No Timeslots found!</p>';
								}
							?>
						</div>
						<div class="saab-cost-label">
							<span class="saab-cost">Cost: <?php echo esc_html($prefix_label) . ' ' . esc_html($cost); ?></span>
						</div>
						<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('booking_form_nonce'); ?>">
						<input type="hidden" id="booking_date" name="booking_date" value="<?php echo $lastdateid; ?>" name="booking_date" >
					</div>
					<div class="step step2">
					<?php	
					
				}
					if (get_post($post_id)) {
						$post_status = get_post_status($post_id);
						if ($post_status === 'publish') {

								$fields = get_post_meta($post_id, 'saab_formschema', true);
								if ($fields) {
								?>
								<div id="formio"></div>
								<div id="formio_res_msg" style="display:none"></div>
								<?php
									if(isset($enable_booking) && !empty($enable_booking)){
								?>
									<script type='text/javascript'>								
										var myScriptData = <?php echo $fields; ?>;															
										var value = myScriptData;
										Formio.createForm(document.getElementById('formio'), {
										components: value
										}).then(function(form) {
											var isSubmitting = false; // Flag variable to track form submission
											form.on('submit', function(submission) {
												event.preventDefault();
												var msgloader = jQuery('#saabform-message'); 
												jQuery('#backButton').hide();
												if (isSubmitting) {	
													jQuery('#saabform-message').html("Already Submitted!").fadeIn().delay(1000).fadeOut();																								
													return;
												}												
												isSubmitting = true;
												var formid = <?php echo wp_json_encode($post_id); ?>;
												var booking_date = jQuery('input[name="booking_date"]').val();
												var nonce = jQuery('input[name="nonce"]').val();
												var timeslot = "";
												var slotcapacity = "";

												jQuery('.saab_timeslot').each(function() {
													if (jQuery(this).hasClass('selected')) {
														timeslot = jQuery(this).find('input[name="booking_slots"]').val();	
														slotcapacity = jQuery(this).find('.saab-tooltip-text').attr('data-seats');
													} 
												});
												bookedseats = jQuery('input[name="saabslotcapacity"]').val();
												jQuery.ajax({
													url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
													type : 'post',
													data: { 
													action: "saab_booking_form_submission",
													form_data: submission,
													fid:formid,
													timeslot:timeslot,
													booking_date:booking_date,
													bookedseats:bookedseats,
													slotcapacity:slotcapacity,
													nonce:nonce,
													},
													success: function (response) {
														if (response.success) {															
          													msgloader.hide();
															var confirmationType = response.data.confirmation;
															var message = response.data.message;
															var redirectPage = response.data.redirect_page;
															var wpEditorValue = response.data.wp_editor_value;
															var redirectUrl = response.data.redirect_url;
															// Check the confirmation type
															if (confirmationType === 'redirect_text') {
																// Replace div content with wpEditorValue or message
																jQuery('#calender_reload').html(wpEditorValue).fadeIn().delay(3000);
															} else if (confirmationType === 'redirect_to') {
																jQuery('#calender_reload').html('<p>' + message + '</p>');
																setTimeout(function() {
																	window.location.href = redirectUrl;
																}, 3000); 
															} else if (confirmationType === 'redirect_page') {
																jQuery('#calender_reload').html('<p>' + message + '</p>');
																setTimeout(function() {
																	window.location.href = redirectUrl;
																}, 3000);
															} else if(confirmationType === ''){

																jQuery('#formio_res_msg').html(response.data.message).fadeIn().delay(3000).fadeOut();
															}
															else {
																jQuery('#formio_res_msg').html(response.data.message).fadeIn().delay(3000).fadeOut();
															}
															jQuery('#nextButton').css('display', 'none');
															jQuery('#backButton').css('display', 'none');
															$("button[name='data[submit]'] i.fa.fa-refresh.fa-spin.button-icon-right").hide();
														} else {
															// console.log(response.data.message);
															var errorMessage = response.data.error;
               												isSubmitting = false; 
															jQuery('#formio_res_msg').html(response.data.message).fadeIn().delay(3000).fadeOut();
														}
													}
													
												});
												return false;
											});
												
										});
										
									</script>

								<?php
									}else{
										?>
										<script type='text/javascript'>								
										var myScriptData = <?php echo $fields; ?>;															
										var value = myScriptData;
										
										Formio.createForm(document.getElementById('formio'), {
										components: value
										}).then(function(form) {
											var isSubmitting = false; // Flag variable to track form submission
											form.on('submit', function(submission) {
												event.preventDefault();
												var formid = <?php echo wp_json_encode($post_id); ?>;
												if (isSubmitting) {
													jQuery('#formio_res_msg').html("Already Submitted!").fadeIn().delay(1000).fadeOut();		
													return; 
												}
												isSubmitting = true; 		
												var nonce = myAjax.nonce;								
												jQuery.ajax({
													url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
													type : 'post',
													data: { 
													action: "saab_save_form_submission",
													form_data: submission,
													fid:formid,
													nonce:nonce,
													},
													success: function (response) {
														if (response.success) {
															jQuery("i.fa.fa-refresh.fa-spin.button-icon-right").hide();
															var confirmationType = response.data.confirmation;
															var message = response.data.message;
															var redirectPage = response.data.redirect_page;
															var wpEditorValue = response.data.wp_editor_value;
															var redirectUrl = response.data.redirect_url;

															// Check the confirmation type
															if (confirmationType === 'redirect_text') {
																jQuery('#formio').hide();
																jQuery('#formio_res_msg').html(wpEditorValue);
															} else if (confirmationType === 'redirect_to' || confirmationType === 'redirect_page') {
																jQuery('#formio_res_msg').html('<p>' + message + '</p>');
																setTimeout(function() {
																	window.location.href = redirectUrl;
																}, 3000);
															} else if (confirmationType === '') {
																console.log(message);
																jQuery('#formio_res_msg').html(message).fadeIn().delay(3000).fadeOut();
															} else {
																jQuery('#formio_res_msg').html(message).fadeIn().delay(3000).fadeOut();
															}
															jQuery("button[name='data[submit]'] i.fa.fa-refresh.fa-spin.button-icon-right").hide();
														} else {
															var errorMessage = response.data.error;
															submitButton.disabled = false;
															isSubmitting = false; 
															jQuery('#formio_res_msg').html(response.data.message).fadeIn().delay(3000).fadeOut();
														}
													},

												});
												return false;
											});
												
										});
										
									</script>

										<?php
									}
								} else {
								echo esc_html__('Form data not found.', 'smart-appointment-booking');
								}
							
						} else {
							echo esc_html__("Post exists but is not published.", 'smart-appointment-booking');
						}
					} else {
						echo esc_html__("Post does not exist.", 'smart-appointment-booking');
					}
			if(isset($enable_booking) && !empty($enable_booking)){
			
			?>
				</div>
			</div>
			<div class="dc_backButton alignwide">
				<button id="backButton">Back</button>
				<button id="nextButton">Next</button>
			</div>

			<div id="saabform-message"></div>
			<?php
				
			}
			return ob_get_clean();
		  }
		 /**
		 * 
		 * Calender on change of month and year
		 * 
		 */
		  function saab_action_reload_calender(){
			if ( isset( $_POST['security'] ) ||  wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['security'] ) ) , 'my_ajax_nonce' ) ){
				$monthNames = array(
					1 => 'January',
					2 => 'February',
					3 => 'March',
					4 => 'April',
					5 => 'May',
					6 => 'June',
					7 => 'July',
					8 => 'August',
					9 => 'September',
					10 => 'October',
					11 => 'November',
					12 => 'December'
					);
					$currentMonth = isset($_POST['currentMonth']) ? intval($_POST['currentMonth']) : gmdate('n');
					$currentMonth = max(1, min(12, $currentMonth)); // Ensure currentMonth is between 1 and 12
					$currentYear = isset($_POST['currentYear']) ? intval($_POST['currentYear']) : gmdate('Y');
					$post_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
					
					$running_year = gmdate("Y");
					ob_start();
				?>
				<div class="header-calender">
					<input type="hidden" id="zealform_id" value="<?php echo esc_attr($post_id); ?>">
					<span class="arrow" id="prev-month" onclick="getClicked_prev(this)">&larr;</span>
					<select name='saab_month_n' id='saab_month'>
						<?php
						for ($i = 1; $i <= 12; $i++) {
							echo "<option value='" . esc_attr($i) . "'";
							if ($i == $currentMonth) {
								echo " selected";
							}
							echo ">" . esc_html($monthNames[$i]) . "</option>";
						}
						?>
					</select>
				
					<select name="saab_year_n" id="saab_year">
						<?php
						$futureYear = gmdate("Y", strtotime("+10 years", strtotime("January 1, $currentYear")));
						echo '<optgroup label="Current Year">';
						echo "<option value='" . esc_html($running_year) . "'>" . esc_html($running_year). "</option>";
						echo '</optgroup>';
						echo '<optgroup label="Years">';
						for ($year = $futureYear; $year >= $currentYear; $year--) {
							echo "<option value='" . esc_attr($year) . "'";
							if ($year == $currentYear) {
								echo " selected";
							}
							echo ">" . esc_attr($year) . "</option>";

						}
						echo '</optgroup>';
						?>
					</select>

					<span class="arrow" id="next-month" onclick="getClicked_next(this)">&rarr;</span>
				</div>
				<table>
					<tr>
						<th>Sun</th>
						<th>Mon</th>
						<th>Tue</th>
						<th>Wed</th>
						<th>Thu</th>
						<th>Fri</th>
						<th>Sat</th>
					</tr>
					<?php
					$firstDayOfWeek = gmdate('N', strtotime($currentYear . '-' . $currentMonth . '-01'));
					$firstDayOfWeek += 1;
					$totalDays = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
					$daysInPreviousMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

					// Calculate the number of cells needed
					$totalCells = ($totalDays + $firstDayOfWeek - 1) % 7 === 0 ? $totalDays + $firstDayOfWeek - 1 : ceil(($totalDays + $firstDayOfWeek - 1) / 7) * 7;

					$dayCounter = 1;
					$date = 1;
					$monthYear = $currentMonth . '-' . $currentYear;

					while ($dayCounter <= $totalCells) {
						echo "<tr>";
						for ($i = 1; $i <= 7; $i++) {
							
							if ($dayCounter >= $firstDayOfWeek && $date <= $totalDays) {
								echo "<td id='saabid_" . esc_attr( $post_id) . '_' . esc_attr($currentMonth) . "_" . esc_attr($date) . "_" . esc_attr($currentYear) . "' data_day='saabid_" . esc_attr($post_id) . '_' . esc_attr($currentMonth) . "_" . esc_attr($date) . "_" . esc_attr($currentYear) . "' class='saab_cal_day' onclick='getClickedId(this)'>" . esc_attr($date) . "</td>";
								$date++;
							}
							elseif ($dayCounter < $firstDayOfWeek) {
								$prevDate = $daysInPreviousMonth - ($firstDayOfWeek - $dayCounter - 1);
								echo "<td class='previous-month'>" . esc_attr($prevDate) . "</td>";
							}
							else {
								$nextDate = $dayCounter - ($totalDays + $firstDayOfWeek) + 1;
								echo "<td class='next-month'>" . esc_attr($nextDate) . "</td>";
							}

							$dayCounter++;
						}

						echo "</tr>";
					}
					?>
				</table>
				<style>
					
				</style>
				<?php
				$output = ob_get_clean();
				echo esc_attr($output);
				wp_die();
			} else {
				wp_die();
			}
			
		  }
		/*** 
		 * Display Timeslots on booking calendar
		 */
		  function saab_action_display_available_timeslots(){
			
			if ( isset( $_POST['security'] ) &&  wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['security'] ) ) , 'my_ajax_nonce' ) ) {   
				$error = false;
				if(isset( $_POST['form_data'])){
					$form_data = sanitize_text_field($_POST['form_data']);
					$array_data = explode('_',$form_data);
					$post_id = $array_data[1];
					$current_month = $array_data[2];
					$current_day = $array_data[3];
					$current_year = $array_data[4];
				}
				if(isset( $_POST['clickedId'])){
					$clickedId = absint($_POST['clickedId']);
				}
				$todaysDate = gmdate('Y-m-d', strtotime("$current_year-$current_month-$current_day"));
				$TodaysDate_F = gmdate('F d, Y', strtotime("$current_year-$current_month-$current_day"));
				echo "<h3 id='head_avail_time'>Available Time Slots</h3>";
				echo "<h4 id='headtodays_date'>" . esc_html($TodaysDate_F) . "</h4>";
				echo '<input type="hidden" id="zeallastdate" name="zeallastdate" value="' . htmlspecialchars($clickedId, ENT_QUOTES, 'UTF-8') . '" >';
				echo "<ul id='saab-slot-list'>";
                    
					$is_available = $this->saab_processDate($post_id,$todaysDate);				
					
                    if (isset($is_available) && is_array($is_available)) {

                        if(isset($is_available) && is_array($is_available) && in_array($todaysDate,$is_available)){
							$check_type = get_post_meta($post_id, 'saab_enable_recurring_apt', true);
							$enable_advance_setting = get_post_meta($post_id, 'saab_enable_advance_setting', true);
							 
							// if($enable_advance_setting && !empty($enable_advance_setting)){
								$advancedata = get_post_meta($post_id, 'saab_advancedata', true);
								foreach ($advancedata as $item) {
									$advanceDates[] = $item['advance_date'];
								}
								if(in_array($todaysDate,$advanceDates)){
								   echo $this->saab_get_advanced_timeslots($post_id,$form_data,$todaysDate);
								}else{
								   echo $this->saab_front_generate_timeslots($post_id,$form_data);                                      
								}
							// }         
						}else {
							$error = true;
							error_log('Check End date! Selected date exceed the selected end date');
						}
                    } else {
						$error = true;
                        error_log('Array does not exist.');
                    }	
				echo "</ul>";
				if($error === true){
					echo esc_html__('No timeslots found for selected date. ','smart-appointment-booking');
				}else{
					echo '<input class="saab-selected-capacity" type="number" name="saabslotcapacity" pattern="^[1-9]\d*$" placeholder="Enter Slot Capacity" min="1" value="1">';
					echo '<p id="no-timeslots-message" style="display: none;">No Timeslots found!</p>';
				}
				wp_die();
			}else{
				$error = true;
                error_log('Noonce is not set.');
				wp_die();
			}
				
		  }
		/**	
		 * Cancel Booking via linked clicked by user send in mail by using shortcode placed on cancel booking page
		 */
		  function saab_cancel_booking() {
			if ( !isset( $_POST['security'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['security'] ) ) , 'my_ajax_nonce' ) ) {  
				$response = array(
					'message' => esc_html__('Something went wrong, please try again later', 'smart-appointment-booking'),
					'error' => true,
					'status' => 'check',
				);
				wp_die();

			} 
			$encrypt_bookingId = isset($_POST['bookingId']) ? absint($_POST['bookingId']) : 0;
			
			$response = array();
		
			if (isset($_POST['bookingId']) && isset($_POST['bookingstatus'])) {
				// $booking_id = wp_base64_decode($encrypt_bookingId);
				$booking_id = $encrypt_bookingId;
				$bookingstatus = isset($_POST['bookingstatus']) ? sanitize_text_field($_POST['bookingstatus']) : '';
		
				if ($bookingstatus === 'cancel') {
					if (isset($_POST['status'])) {
						$status = sanitize_text_field($_POST['status']);
						if ($status === 'check') {
							$get_current_status = get_post_meta($booking_id, 'saab_entry_status', true);
							if ($get_current_status === 'cancelled') {
								$response = array(
									'message' => esc_html__('Booking already cancelled', 'smart-appointment-booking'),
									'error' => true,
									'status' => 'check',
								);
							} else {
								$response = array(
									'message' => esc_html__('Booking cancellation ready for confirmation', 'smart-appointment-booking'),
									'error' => 'false',
									'status' => 'readytoconfirm',
								);
							}
						}
						if ($status === 'confirm') {
							update_post_meta($booking_id, 'saab_entry_status', 'cancelled');
							$get_current_status = get_post_meta($booking_id, 'saab_entry_status', true);
							$response = array(
								'message' => esc_html__('Booking has been cancelled successfully', 'smart-appointment-booking'),
								'error' => 'false',
								'status' => 'updated',
							);

						}
					}
				} else {
					$response = array(
						'message' => esc_html__('Something went wrong, please try again later', 'smart-appointment-booking'),
						'error' => true,
						'status' => 'check',
					);
				} 
			} else {
				$response = array(
					'message' => esc_html__('Invalid URL.', 'smart-appointment-booking'),
					'error' => true,
				);
			}
			wp_send_json($response);
			wp_die();
		}
		/**	
		 * Confirm booking cancellation
		 */
		function saab_confirm_booking_cancellation() {
			ob_start();
			$nonce = isset($_GET['security']) ? sanitize_text_field( wp_unslash ($_GET['security'])) : '';
			if (empty($nonce) && !wp_verify_nonce($nonce, 'my_ajax_nonce')) {
				?>
				<div class="booking-cancellation-card"><p class="h6" id="msg_booking_cancel"> Something went wrong </p></div> 
				<?php			
				return ob_get_clean();
			}
			echo '<div class="booking-cancellation-card">';
			$encrypt_bookingId = isset($_REQUEST['booking_id']) ? sanitize_text_field($_REQUEST['booking_id']) : '';

			if (isset($_REQUEST['booking_id']) && isset($_REQUEST['status'])) {
				// $booking_id = wp_base64_decode($encrypt_bookingId);
				$booking_id = $encrypt_bookingId;
				$bookingstatus = sanitize_text_field( $_REQUEST['status'] );

				if ($bookingstatus === 'cancel' ) {

					$get_current_status = get_post_meta($booking_id,'saab_entry_status',true);
					if($get_current_status === 'cancelled'){
						?> <p class="h6" id="msg_booking_cancel"> You have already cancelled. </p> <?php
					}else{
						?>
						
							<label>Are you sure you want to cancel the booking?</label>
							<div class="booking-cancellation-buttons">
								<button class="btn-yes">Yes, Confirmed</button>
								<!-- <button class="btn-no">No</button> -->
							</div>
							<p class="h6" id="msg_booking_cancel"></p>
						
						<?php						
					}
				
				}
			}else{
				?> <p class="h6" id="msg_booking_cancel"> Something went wrong </p> <?php
			}
			echo '</div>';
			
			return ob_get_clean();
		}
		/**
		 * shortcode to add in page
		 */
		function saab_cancel_booking_shortcode() {
			$response = array(					
				'message' => esc_html__('','smart-appointment-booking'),
				'mail_message' => '',
				
			);
			if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['security'] ) ) , 'my_ajax_nonce' ) ){
				$response = array(					
					'message' => esc_html__('An error occurred','smart-appointment-booking'),
					'mail_message' => 'none',
					
				); 
				$error_message = 'An error occurred';
				wp_send_json_error($response);
				wp_die();
			}

			if (isset($_POST['bookingId'])) {
				$get_bookingId = absint($_POST['bookingId']);
				$bookingId = $get_bookingId;
				$status = 'cancelled';
				$formdata = get_post_meta($bookingId,'saab_submission_data',true);
				$form_id = get_post_meta($bookingId,'saab_form_id',true);
				update_post_meta($bookingId, 'saab_entry_status', $status);
			
				$listform_label_val = $this->saab_create_key_value_formshortcodes($bookingId,$formdata);
				$listform_label_val['Status'] = $status;
				
				$message = $this->saab_send_notification($status,$form_id, $bookingId, $listform_label_val );			
				$response = array(					
					'message' => esc_html__('Your booking has been cancelled succesfully','smart-appointment-booking'),
					'mail_message' => $message,
					
				);
			}

			wp_send_json($response);
			wp_die();
		}
		/**
		 * function to create shortcode pair for email notification
		 */
		function saab_create_key_value_formshortcodes($bookingId,$form_data){
			
			$form_id = get_post_meta($bookingId,'saab_form_id',true);
			$FormTitle = get_the_title( $form_id );
			
			$get_user_mapping = get_post_meta($form_id, 'saab_user_mapping', true);
			$getemail = isset($get_user_mapping['email']) && isset($form_data['data'][$get_user_mapping['email']]) ? sanitize_text_field($get_user_mapping['email']) : '';
   			if ($getemail) {
				$emailTo =  $form_data['data'][$getemail];					
			}
			$getfirst_name = isset($get_user_mapping['first_name']) && isset($form_data['data'][$get_user_mapping['first_name']])  ? sanitize_text_field($get_user_mapping['first_name']) : '';
			if ($getfirst_name) {
				$first_name = $form_data['data'][$getfirst_name];					
			}
			$getlast_name = isset($get_user_mapping['last_name']) && isset($form_data['data'][$get_user_mapping['last_name']]) ? sanitize_text_field($get_user_mapping['last_name']) : '';
			if ($getlast_name) {
				$last_name =  $form_data['data'][$getlast_name];					
			}
			$getservice = isset($get_user_mapping['service']) && isset($form_data['data'][$get_user_mapping['service']]) ? sanitize_text_field($get_user_mapping['service']) : '';
			
			if ($getservice) {
				$service =  ucfirst($form_data['data'][$getservice]);					
			}
			$timeslot = get_post_meta($bookingId,'saab_timeslot',true);
			$BookingDate = get_the_date( 'M d,Y', $form_id );
			
			$booking_date = get_post_meta($bookingId,'saab_booking_date',true);
			$no_of_seats = $this->saab_get_available_seats_per_timeslot($timeslot, $booking_date);
			
			$explode_booking_date = explode('_',$booking_date);
			$explode_timeslot = explode('-',$timeslot);

			$format_bookingdate = $explode_booking_date[4] . "-" . $explode_booking_date[2] . "-" . $explode_booking_date[3];
			$converted_bookingdate = gmdate('Y-m-d', strtotime($format_bookingdate));
			
			// $encrypted_booking_id = wp_base64_encode($bookingId);
			$encrypted_booking_id = $bookingId;
			$user_mapping = get_post_meta($form_id, 'saab_user_mapping', true);
			if ($user_mapping) {
				$cancelbooking_pageid = isset($user_mapping['cancel_bookingpage']) ? sanitize_text_field($user_mapping['cancel_bookingpage']) : '';
				$cancelbooking_url = get_permalink($cancelbooking_pageid).'?booking_id=' . $encrypted_booking_id . '&status=cancel';
			} else {
				$cancelbooking_url = add_query_arg(
					array(
						'booking_id' => $encrypted_booking_id,
						'status' => 'cancel',
					),
					site_url()
				);		
				$cancelbooking_url = esc_url($cancelbooking_url);
			}
			$no_of_booking = get_post_meta($form_id, 'saab_no_of_booking', true);
			
			$checkseats = $this->saab_get_available_seats_per_timeslot($timeslot,$converted_bookingdate);
			if($checkseats >  $no_of_booking ){
				$available_seats = 0;
			}else{
				$available_seats = $no_of_booking - $checkseats;
			}

			$prefixlabel = get_post_meta( $form_id, 'saab_label_symbol', true );
			$cost = get_post_meta( $form_id, 'saab_cost', true );
			

			$bookedseats = get_post_meta($bookingId,'saab_slotcapacity',true);
			
			$other_label_val = array(
				'FormId' => $form_id,
				'BookingId' => $bookingId,
				'FormTitle' => $FormTitle,
				'To' => $emailTo,
				'FirstName' => $first_name,
				'LastName' => $last_name,
				'Service' => $service,
				'Timeslot' => $timeslot,
				'BookingDate' => $BookingDate,
				'BookingSeats' => $no_of_seats,
				'BookedDate' =>$converted_bookingdate,	
				'prefixlabel' => $prefixlabel,
				'cost' => $cost,					
				'slotcapacity' => $available_seats,
				'bookedseats' => $bookedseats,	
				'form_data' => $form_data,
				'no_of_seats' => $no_of_seats,
				'tot_no_of_seats' => $available_seats,
				'StartTime' => $explode_timeslot[0],
				'EndTime' => $explode_timeslot[1],
				'CancelBooking' => $cancelbooking_url,
			);
			return $other_label_val;

		}
	}
	add_action( 'plugins_loaded', function() {
		$SAAB_Front_Action = new SAAB_Front_Action();
	} );
}