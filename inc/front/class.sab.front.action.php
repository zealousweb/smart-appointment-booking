<?php
/**
 * SAB_Front_Action Class
 *
 * Handles the Frontend Actions.
 *
 * @package WordPress
 * @subpackage Smart Appointment & Booking
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'SAB_Front_Action' ) ){

	/**
	 *  The SAB_Front_Action Class
	 */
	class SAB_Front_Action {

		function __construct()  {

		
			add_action('wp_ajax_zfb_booking_form_submission', array( $this, 'zfb_booking_form_submission' ) );
			add_action('wp_ajax_nopriv_zfb_booking_form_submission', array( $this, 'zfb_booking_form_submission' ) );

			add_action('wp_ajax_zfb_save_form_submission', array( $this, 'zfb_save_form_submission' ) );
			add_action('wp_ajax_nopriv_zfb_save_form_submission', array( $this, 'zfb_save_form_submission' ) );

			add_action('wp_ajax_action_reload_calender', array( $this, 'action_reload_calender' ) );
			add_action('wp_ajax_nopriv_action_reload_calender', array( $this, 'action_reload_calender' ) );

			add_action('wp_ajax_action_display_available_timeslots', array( $this, 'action_display_available_timeslots' ) );
			add_action('wp_ajax_nopriv_action_display_available_timeslots', array( $this, 'action_display_available_timeslots' ) );

			add_action( 'wp_enqueue_scripts',  array( $this, 'action__enqueue_styles' ));
			add_action( 'wp_enqueue_scripts', array( $this, 'action__wp_enqueue_scripts' ));

			add_shortcode('booking_form',array( $this, 'zealsab_get_booking_form' ));

			add_action('wp_ajax_zfb_cancel_booking', array( $this, 'zfb_cancel_booking' ) );
			add_action('wp_ajax_nopriv_zfb_cancel_booking', array( $this, 'zfb_cancel_booking' ) );

			add_shortcode( 'confirm_booking_cancellation', array( $this, 'confirm_booking_cancellation' ) );
			add_action('wp_ajax_cancel_booking_shortcode', array( $this, 'cancel_booking_shortcode' ) );
			add_action('wp_ajax_nopriv_cancel_booking_shortcode', array( $this, 'cancel_booking_shortcode' ) );

			add_action('notification_send', array( $this, 'zfb_send_notification' ) , 10, 4);
			add_action('get_available_seats_per_timeslot', array( $this, 'get_available_seats_per_timeslot' ) , 10, 4);
			add_action('_key_value_formshortcodes', array( $this, 'create_key_value_formshortcodes' ) , 10, 4);

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
		function action__wp_enqueue_scripts() {
			if(is_admin()){
				wp_enqueue_script( SAB_PREFIX . '_bookingform', SAB_URL . 'assets/js/booking/booking-form.js', array( 'jquery-core' ), SAB_VERSION );
			}
			wp_enqueue_script( SAB_PREFIX . '_front', SAB_URL . 'assets/js/front.js', array( 'jquery-core' ), SAB_VERSION );
			wp_enqueue_script( 'sab_formio_full_min', SAB_URL.'assets/js/formio/formio.full.min.js', array( 'jquery' ), 1.1, false );
			
			wp_localize_script('sab_formio_full_min', 'myAjax', array(
				'ajaxurl' => admin_url('admin-ajax.php')
			));
			wp_enqueue_script( 'sab_boostrap.min', SAB_URL.'assets/js/boostrap/boostrap.min.js', array( 'jquery' ), 1.1, false );
			wp_enqueue_script( 'sab_jquery-3.7.0.slim.min', SAB_URL.'assets/js/boostrap/jquery-3.7.0.slim.min.js', array( 'jquery' ), 1.1, false );
			wp_enqueue_script( 'sab_jquery-3.7.0.min',SAB_URL.'assets/js/boostrap/jquery-3.7.0.min.js', array( 'jquery' ), 1.1, false );

			//cancel booking 

			if (is_front_page()) {

				wp_enqueue_script('cancel-booking', SAB_URL . 'assets/js/booking/cancelbooking.js', array('jquery'), '1.0', true);
				
				wp_localize_script('cancelbooking', 'myAjax', array(
					'ajaxurl' => admin_url('admin-ajax.php'),
				));
			}
		}
		function action__enqueue_styles() {

			wp_enqueue_style( 'sab_front',SAB_URL.'assets/css/front.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'sab_boostrap_min',SAB_URL.'assets/css/boostrap/boostrap.min.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'sab_formio_full_min',SAB_URL.'assets/css/formio/formio.full.min.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'sab_font-awesomev1','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', array(), 1.1, 'all' );
				
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
		function isbooking_open($post_id, $timeslot, $booked_date) {
			$explodedArray = explode("_", $booked_date);
			// Extracting month, day, and year components
			$month = sprintf("%02d", $explodedArray[2]);
			$day = $explodedArray[3];
			$year = $explodedArray[4];
			// Convert the given date to a Unix timestamp
			$givenTimestamp = strtotime("$year-$month-$day");
		
			// Get today's Unix timestamp
			$todayTimestamp = strtotime('today');
		
			$booking_stops_after = get_post_meta($post_id, 'booking_stops_after', true);
			if (!empty($booking_stops_after)) {
				$booking_stops_after_duration_seconds = ($booking_stops_after['hours'] * 3600) + ($booking_stops_after['minutes'] * 60);
			}
		
			$current_time = time();
			$waiting_text = '';
		
			// Explode the time range into start and end times
			$time_parts = explode("-", $timeslot);
			$start_time = $time_parts[0]; // "05:30 PM"
			$end_time = $time_parts[1];   // "08:00 PM"
		
			$get_timezone = get_post_meta($post_id, 'timezone', true);
			date_default_timezone_set($get_timezone);
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
		
		
		function zfb_booking_form_submission() {
			$form_id = $_POST['fid'];
			$error = $mail_message = '';
		
			$booking_date = $_POST['booking_date'];
			$explode_booking_date = explode('-',$booking_date);
			$format_bookingdate = $explode_booking_date[4] . "-" . $explode_booking_date[2] . "-" . $explode_booking_date[3];
			$converted_bookingdate = date('Y-m-d', strtotime($format_bookingdate));
			
			$timeslot = $_POST['timeslot'];
			$slotcapacity = $_POST['slotcapacity'];
			$bookedseats = $_POST['bookedseats'];
			
			$FormTitle = get_the_title($form_id);
			$form_data = $_POST['form_data'];
			$enable_auto_approve = get_post_meta($form_id, 'enable_auto_approve', true);
			$check_waiting = get_post_meta($form_id, 'waiting_list', true);
			$cost = get_post_meta($form_id, 'cost', true);
			$appointment_type = get_post_meta($form_id, 'appointment_type', true);
			$label_symbol = get_post_meta($form_id, 'label_symbol', true);
			$seats_per_timeslot =  get_post_meta($form_id, 'slotcapacity',true);


			$check_isbooking_open = $this->isbooking_open($form_id,$timeslot,$booking_date);
			if($check_isbooking_open === false){
				$error = true;
				wp_send_json_error(array(
					'message' => __('The booking window has closed.','smart-appointment-booking'),
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
					$error = __('No available seats','smart-appointment-booking');
				}
			}else{
				$register_booking = 'true';
			}
		
			if ($register_booking === 'true') {
				
				$pid = get_option('tot_manage_entries');
				if (empty($pid)) {
					$pid = 1;
				} else {
					$pid++;
				}
				$new_post = array(
					'post_title'   => 'entry#' . $pid,
					'post_type'    => 'manage_entries',
					'post_status'  => 'publish'
				);
		
				$created_post_id = wp_insert_post($new_post);
			
				update_option('tot_manage_entries', $pid);
				update_post_meta($created_post_id, 'sab_submission_data', $form_data);
				update_post_meta($created_post_id, 'sab_form_id', $form_id);
		
				update_post_meta($created_post_id, 'timeslot', $timeslot);
				update_post_meta($created_post_id, 'booking_date', $booking_date);
				update_post_meta($created_post_id, 'slotcapacity', $bookedseats);
				update_post_meta($created_post_id, 'cost', $cost);
				update_post_meta($created_post_id, 'label_symbol', $label_symbol);
				update_post_meta($created_post_id, 'appointment_type', $appointment_type);
				$submission_key_val = array();				
				foreach($form_data['data'] as $form_key => $form_value){
                    if($form_key !== 'submit'){
						$submission_key_val[$form_key] = esc_attr($form_value);
                    }
                }
				$explode_timeslot = explode('-',$timeslot);

				$get_user_mapping = get_post_meta($form_id, 'user_mapping', true);
				
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
			
				$encrypted_booking_id = base64_encode($created_post_id);
				$user_mapping = get_post_meta($form_id, 'user_mapping', true);

				if($user_mapping){
					$cancelbooking_pageid = isset($user_mapping['cancel_bookingpage']) ? sanitize_text_field($user_mapping['cancel_bookingpage']) : '';
					$cancelbooking_url = get_permalink($cancelbooking_pageid).'?booking_id=' . $encrypted_booking_id . '&status=cancel';
				}else{
					$encoded_booking_id = urlencode($encrypted_booking_id);
					$cancelbooking_url = home_url('/?booking_id=' . $encoded_booking_id . '&status=cancel');
				}

				$prefixlabel = get_post_meta( $form_id, 'label_symbol', true );
				$cost = get_post_meta( $form_id, 'cost', true );
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
						echo $mail_message = $this->zfb_send_notification( $status, $form_id, $created_post_id, $listform_label_val);
						update_post_meta($created_post_id, 'entry_status', 'waiting');

					} else {
						$mail_message = '';
						$status = 'booked';
						$listform_label_val['Status'] = $status;
						$mail_message = $this->zfb_send_notification( $status, $form_id, $created_post_id, $listform_label_val);
						update_post_meta($created_post_id, 'entry_status', 'booked');
					}
				} else {
					$mail_message = '';
					$status = 'pending';
					$listform_label_val['Status'] = $status;
					$mail_message = $this->zfb_send_notification( $status, $form_id, $created_post_id, $listform_label_val);
					update_post_meta($created_post_id, 'entry_status', 'approval_pending');

				}
				$confirmation = get_post_meta($form_id, 'confirmation', true);
				$success_message = get_post_meta($form_id, 'redirect_text', true);
				$formatted_message = wpautop($success_message);
				$redirect_url = '';
				
				if ($confirmation == 'redirect_text') {
					$wp_editor_value = get_post_meta($form_id, 'redirect_text', true);
				} elseif ($confirmation == 'redirect_page') {
					$redirect_page = get_post_meta($form_id, 'redirect_page', true);
					$redirect_url = get_permalink($redirect_page);
				} elseif ($confirmation == 'redirect_to') {
					$redirect_url = get_post_meta($form_id, 'redirect_to', true);
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
					'message' => __('Something went wrong, Please try again later','smart-appointment-booking'),
					'error' => $error,
				));
			}
			
			wp_die();
		}
		function zfb_save_form_submission() {
			$form_id = $_POST['fid'];
			$form_data = $_POST['form_data'];
		
			$pid = get_option('tot_manage_entries');
			if (empty($pid)) {
				$pid = 1;
			} else {
				$pid++;
			}
			$new_post = array(
				'post_title'   => 'entry_#' . $pid,
				'post_type'    => 'manage_entries',
				'post_status'  => 'publish'
			);
		
			$created_post_id = wp_insert_post($new_post);
			
			update_option('tot_manage_entries', $pid);
			update_post_meta($created_post_id, 'sab_submission_data', $form_data);
			update_post_meta($created_post_id, 'sab_form_id', $form_id);
			
			$prefixlabel = get_post_meta( $form_id, 'label_symbol', true );
			$cost = get_post_meta( $form_id, 'cost', true );

			update_post_meta($created_post_id, 'label_symbol', $prefixlabel);
			update_post_meta($created_post_id, 'cost', $cost);
			
			$submission_key_val = array();				
			foreach($form_data['data'] as $form_key => $form_value){
				if($form_key !== 'submit'){
					$submission_key_val[$form_key] = esc_attr($form_value);
				}
			}
				
			$get_user_mapping = get_post_meta($form_id, 'user_mapping', true);
				
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
				
				
			$encrypted_booking_id = base64_encode($created_post_id);
			$user_mapping = get_post_meta($form_id, 'user_mapping', true);
			if ($user_mapping) {
				$cancelbooking_pageid = isset($user_mapping['cancel_bookingpage']) ? sanitize_text_field($user_mapping['cancel_bookingpage']) : '';
				$cancelbooking_url = get_permalink($cancelbooking_pageid).'?booking_id=' . $encrypted_booking_id . '&status=cancel';
			} else {
				$cancelbooking_url = home_url('/?booking_id=' . $encrypted_booking_id . '&status=cancel');
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
				update_post_meta($created_post_id, 'entry_status', $status);
				$listform_label_val = array_merge($submission_key_val, $other_label_val);
				$mail_response = $this->zfb_send_notification($status,$form_id, $created_post_id, $listform_label_val );
				
				$confirmation = get_post_meta($form_id, 'confirmation', true);
				$redirect_url = '';
				
				if ($confirmation == 'redirect_text') {
					$wp_editor_value = get_post_meta($form_id, 'redirect_text', true);
					
				    $editor_value = wpautop(wp_kses_post($wp_editor_value));
				} elseif ($confirmation == 'redirect_page') {
					$redirect_page = get_post_meta($form_id, 'redirect_page', true);
					$redirect_url = get_permalink($redirect_page);
				} elseif ($confirmation == 'redirect_to') {
					$redirect_url = get_post_meta($form_id, 'redirect_to', true);
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
		function zfb_send_notification($status,$form_id, $post_id, $form_data	) {
			$message = '';
			$get_notification_array = get_post_meta($form_id, 'notification_data', true);	
			
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

					$to = $this->check_shortcode_exist($check_to,$form_id, $form_data,$shortcodesArray );
					$from = $this->check_shortcode_exist($check_from,$form_id, $form_data,$shortcodesArray );
					$replyto = $this->check_shortcode_exist($check_replyto,$form_id, $form_data,$shortcodesArray );
					$bcc = $this->check_shortcode_exist($check_bcc,$form_id, $form_data ,$shortcodesArray );
					$cc = $this->check_shortcode_exist($check_cc,$form_id, $form_data,$shortcodesArray );
					$check_body = $this->check_shortcodes_exist_in_editor($check_body,$form_id, $form_data,$shortcodesArray );
					
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
						$message = __('Email sent successfully','smart-appointment-booking');
					} else {
						$message = __('Failed to send email','smart-appointment-booking');
						error_log('Failed to send email');
					}
				}
			
			}
			if ($notificationFound === false) {
				$message = __('Notification not found for the given status', 'smart-appointment-booking');
				error_log('Notification not found for the given status');
			}
			return $message;
		}
		function check_shortcode_exist($fieldValue, $form_id, $form_data,$dataArray) {
			
			$fieldValue_exploded = explode(',', $fieldValue);
			$processed_fieldValue = [];
		
			foreach ($fieldValue_exploded as $index => $Value_exploded) {
				$Value_exploded = trim($Value_exploded);
				foreach ($dataArray as $shortcode) {
					if (strpos($Value_exploded, $shortcode) !== false) {
						if ($shortcode === '[To]') {
							$get_user_mapping = get_post_meta($form_id, 'user_mapping', true);
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
		function check_shortcodes_exist_in_editor($fieldValue, $form_id, $form_data, $shortcodes) {
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
		
		function front_get_shortcodes($form_id){
			$shortcode_list = array();
			$form_data1 = get_post_meta( $form_id, '_formschema', true ); 
			$form_data1=json_decode($form_data1);
			foreach ($form_data1 as $obj) { 
				$shortcode_list[] = "[".$obj->key."]";
			}
			$tobe_merged = array('[FormId]', '[BookingId]', '[Status]', '[FormTitle]', '[To]', '[FirstName]', '[LastName]', '[Timeslot]', '[BookedSeats]', '[BookingDate]', '[BookedDate]', '[Service]', '[prefixlabel]', '[cost]', '[StartTime]', '[EndTime]', '[CancelBooking]');
			$shortcode_list = array_merge($tobe_merged,$shortcode_list);

			return $shortcode_list;
		}
		function processDate($post_id = null, $date = null) {
			if ($post_id === null) {
				return false;
			} else {
				$check_type = get_post_meta($post_id, 'enable_recurring_apt', true);
				$holiday_dates = get_post_meta($post_id, 'holiday_dates', true);
				if ($holiday_dates && empty($holiday_dates)){
					$holiday_dates = array();
				}
				$arrayofdates = array(); $arrayof_advdates = array();
				$enable_advance_setting = get_post_meta($post_id, 'enable_advance_setting', true);
				$selected_date = get_post_meta($post_id, 'selected_date', true);
				if($enable_advance_setting && $enable_advance_setting){
					$advancedata = get_post_meta($post_id, 'advancedata', true);
					foreach ($advancedata as $index => $data) {
						if ($holiday_dates && is_array($holiday_dates)){
							if (!in_array($data['advance_date'], $holiday_dates)) {
								$arrayofdates[] = $data['advance_date'];
							}
						}
					}
				}
				
				if ($check_type) {
					
					$weekdays_num = array();
					$weekdays = get_post_meta($post_id, 'weekdays', true);					
					$all_days = array("monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday");
					$todays_date = date("Y-m-d");
                  
                    if($date < $selected_date || $date < $todays_date){
                        $arrayofdates = array();
                    }else{

                      	$recurring_type = get_post_meta($post_id, 'recurring_type', true);					
                    
                        $end_repeats_type = get_post_meta($post_id, 'end_repeats', true);
                        if ($end_repeats_type == 'on') {
                            $end_repeats_on = get_post_meta($post_id, 'end_repeats_on',true);
                            $end_repeats_on_date = date("Y-m-d", strtotime($end_repeats_on));
                        }
						
						if ($recurring_type == 'weekend') {

							$remaining_days = array_diff($all_days, $weekdays);
							foreach ($remaining_days as $wdays) {
								$weekdays_num[] = date('N', strtotime($wdays));
							}
							
							$startDate = strtotime($date);
							$endDate = strtotime($end_repeats_on_date);

							while ($startDate <= $endDate) {
								$dayOfWeek = date('N', $startDate); 
								$currentDate = date('Y-m-d', $startDate);

								if (in_array($dayOfWeek, $weekdays_num) && !in_array($currentDate, $holiday_dates)) {
									$arrayofdates[] = $currentDate; 
								}
								$startDate = strtotime('+1 day', $startDate); 
							}

							
						}elseif ($recurring_type == 'weekdays') { 
                            
                            foreach ($weekdays as $wdays) {
                                $weekdays_num[] = date('N', strtotime($wdays));
                            }
							
							$startDate = strtotime($date);
							$endDate = strtotime($end_repeats_on_date);

							while ($startDate <= $endDate) {
								$dayOfWeek = date('N', $startDate); 
								$currentDate = date('Y-m-d', $startDate);

								if (in_array($dayOfWeek, $weekdays_num) && !in_array($currentDate, $holiday_dates)) {
									$arrayofdates[] = $currentDate; 
								}
								$startDate = strtotime('+1 day', $startDate); 
							}
                            
                        }elseif ($recurring_type == 'certain_weekdays') {
							
                            $certain_weekdays_array = get_post_meta($post_id, 'recur_weekdays', true);
							
                            foreach ($certain_weekdays_array as $wdays) {
                                $weekdays_num[] = date('N', strtotime($wdays));
                            }
							
							$startDate = strtotime($date);
							$endDate = strtotime($end_repeats_on_date);

							while ($startDate <= $endDate) {
								$dayOfWeek = date('N', $startDate); 
								$currentDate = date('Y-m-d', $startDate);

								if (in_array($dayOfWeek, $weekdays_num) && !in_array($currentDate, $holiday_dates)) {
									$arrayofdates[] = $currentDate; 
								}

								$startDate = strtotime('+1 day', $startDate); 
							}
                        }elseif ($recurring_type == 'daily') {
						
							$startDate = strtotime($date);
							$endDate = strtotime($end_repeats_on_date);
							$dayOfWeek = date('N', $startDate); 
							$currentDate = date('Y-m-d', $startDate);
						
							if (!in_array($currentDate, $holiday_dates)) {
								$arrayofdates[] = $currentDate; 
							}

						}
						
						
                    }
					return $arrayofdates;
				}else{
					$arrayofdates[] = $selected_date;
					return $arrayofdates;
				}
			}		
			
		}
		function get_advanced_timeslots($post_id,$booking_date,$inputdate){
			
			$no_of_booking = get_post_meta($post_id, 'no_of_booking', true); 
			$output_timeslot = '';
			$check_type = get_post_meta($post_id, 'enable_recurring_apt', true);
			if ($check_type) {
				$recurring_type = get_post_meta($post_id, 'enable_advance_setting', true);				
			}

			if($check_type && $recurring_type == 1){
				$advancedata = get_post_meta($post_id, 'advancedata', true);
				$get_timezone = get_post_meta($post_id,'timezone',true);                
                date_default_timezone_set($get_timezone);
				$current_time = time();
				
				foreach ($advancedata as $item) {
					$advanceDates[] = $item['advance_date'];
				}
				//get booking_stops_after duration
				$timeslot_BookAllow = get_post_meta($post_id, 'timeslot_BookAllow', true);
				$booking_stops_after = get_post_meta( $post_id, 'booking_stops_after', true );
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

							$start_hours = date('h', strtotime($timeslot['start_time']));
							$sampm = date('a', strtotime($start_hours));

							$end_hours = date('h', strtotime($timeslot['end_time']));
							$sampm = date('a', strtotime($end_hours));	

							$start_timeslot = date('h:i A', strtotime($timeslot['start_time']));
							$end_timeslot = date('h:i A',strtotime($timeslot['end_time']));
							
							$checktimeslot = $start_timeslot."-".$end_timeslot;
							
							$checkseats = $this->get_available_seats_per_timeslot($checktimeslot,$booking_date);

							$waiting_text = '';
							$iswaiting_alllowed = get_post_meta( $post_id,'waiting_list', true );
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

							$selected_date = get_post_meta( $post_id,'selected_date', true );
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
										$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
										$waiting_seats =  $timeslot['bookings'];
									} else {
										$output_timeslot .= '<li class="zfb_timeslot" >';
									}
								} else {
									$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
								}
								$available_text = __('Available seats : ','smart-appointment-booking').$available_seats;
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
													$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
													$waiting_seats =  $timeslot['bookings'];
												} else {
													$output_timeslot .= '<li class="zfb_timeslot" >';
												}
											} else {
												$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
											}
											$available_text = __('Available seats : ','smart-appointment-booking').$available_seats;
										}else{
											$output_timeslot .= '<li class="zfb_timeslot" >';
											$available_seats = 0;
											$available_text = __('Timeslot Not available','smart-appointment-booking');
											$waiting_text = '';
										}
									}else{
										$output_timeslot .= '<li class="zfb_timeslot" >';
										$available_seats = 0;
										$available_text = __('Timeslot Not available','smart-appointment-booking');
										$waiting_text = '';
									}
									
								} else {
								
									if ($available_seats <= 0) {
										if ($iswaiting_alllowed == 1) {
											$waiting_text = "Waiting: Allowed";
											$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
											$waiting_seats =  $timeslot['bookings'];
										} else {
											$output_timeslot .= '<li class="zfb_timeslot" >';
										}
									} else {
										$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
									}
									$available_text = __('Available seats : ','smart-appointment-booking').$available_seats;
								}
							}

						
							
							$output_timeslot .= '<span>'.$start_timeslot.' - ' . $end_timeslot.'</span>';
							$output_timeslot .= '<input class="zfb-selected-time" name="booking_slots"  type="hidden" value="'.$start_timeslot."-".$end_timeslot.'">';					
							$output_timeslot .= '<span class="zfb-tooltip-text" data-seats="'.$available_input_seats.'" >'.$available_text.'<br>'.$waiting_text.'</span>';
							$output_timeslot .= '<span class="zfb-waiting" style="display:none;" class="hidden" data-checkdate="'.$check_date.'" data-waiting="'.$iswaiting_alllowed.'" data-seats="'.$waiting_seats.'">'.$iswaiting_alllowed.'</span>';
							$output_timeslot .= '</li>';
						
						}
					}
				}
			}
			return $output_timeslot;
		}
		/**
		 * generate timeslots
		 */
		function front_generate_timeslots($post_id, $todaysDate = null){
			$output_timeslot = '';
			$generatetimeslot = get_post_meta($post_id, 'generatetimeslot', true);	
			$waiting_seats = 0;
			if($generatetimeslot){
				//set timezone
				$get_timezone = get_post_meta($post_id,'timezone',true);
				date_default_timezone_set($get_timezone);

				//get booking_stops_after duration
				$timeslot_BookAllow = get_post_meta($post_id, 'timeslot_BookAllow', true);
				$booking_stops_after = get_post_meta( $post_id, 'booking_stops_after', true );
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
				$no_of_booking = get_post_meta($post_id, 'no_of_booking', true); 				
				foreach ($generatetimeslot as $index => $timeslot) {
					
					$current_time = time();
					$start_time = isset($timeslot['start_time']) ? $timeslot['start_time'] : '';
					$end_time = isset($timeslot['end_time']) ? $timeslot['end_time'] : '';
					$start_timestamp = strtotime($start_time);
					$current_timewe = date('h:i A', $current_time);
					$end_timestamp = strtotime($end_time);
					$start_time_slot = date('h:i A', $start_timestamp);
					$end_time_slot = date('h:i A', $end_timestamp);
					
					// Add the timeslot to the available timeslots array
					$available_timeslots[] = $start_time_slot . ' - ' . $end_time_slot;
					$checktimeslot = $start_time_slot."-".$end_time_slot;
				
					$iswaiting_alllowed = get_post_meta( $post_id,'waiting_list', true );
					if(!$iswaiting_alllowed){
						$iswaiting_alllowed = 0;
						
					}
					
					$checkseats = $this->get_available_seats_per_timeslot($checktimeslot,$todaysDate);
					$selected_date = get_post_meta( $post_id,'selected_date', true );
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
							$output_timeslot .= '<li class="zfb_timeslot"' . (($available_seats > 0 || $iswaiting_alllowed) ? ' onclick="selectTimeslot(this)"' : '') . '>';
						} else {
							$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
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
											$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
											$waiting_seats  = $no_of_booking;
										} else {
											$output_timeslot .= '<li class="zfb_timeslot" >';
										}
									} else {
										$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
									}
									$available_text = 'Available seats : '.$available_seats;
								}else{
									$output_timeslot .= '<li class="zfb_timeslot" >';
									$available_seats = 0;
									$available_text = 'Timeslot Not available';
									$waiting_text = '';
								}
							}else{
								$output_timeslot .= '<li class="zfb_timeslot" >';
								$available_seats = 0;
								$available_text = 'Timeslot Not available';
								$waiting_text = '';
							}
							
						} else {
						
							if ($available_seats <= 0) {
								if ($iswaiting_alllowed == 1) {
									$waiting_text = "Waiting: Allowed";
									$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
									$waiting_seats  = $no_of_booking;
								} else {
									$output_timeslot .= '<li class="zfb_timeslot" >';
								}
							} else {
								$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
							}
							$available_text = 'Available seats : '.$available_seats;
						}
					}
					
					$output_timeslot .= '<span>'.$start_time_slot.' - ' . $end_time_slot.'</span>';
					$output_timeslot .= '<input class="zfb-selected-time" name="booking_slots" data-startime="'.$this_start_time.'"  type="hidden" value="'.$start_time_slot."-".$end_time_slot.'">';					
					$output_timeslot .= '<span class="zfb-tooltip-text" data-seats="'.$available_input_seats.'" > '.$available_text.'<br>'.$waiting_text.'</span>';
					$output_timeslot .= '<span class="zfb-waiting" style="display:none;" class="hidden" data-checkdate="'.$check_date.'" data-waiting="'.$iswaiting_alllowed.'" data-seats="'.$waiting_seats.'" >'.$iswaiting_alllowed.'</span>';
					$output_timeslot .= '</li>';					
				}
			}else{
				$output_timeslot .= '<li class="zfb_timeslot">';
				$output_timeslot .= 'No Timeslot Found';
				$output_timeslot .= '</li>';
			}
			return $output_timeslot;
		}
		/**
		 * 
		 * Get_available_seats_per_timeslot
		 * 
		 */
        function get_available_seats_per_timeslot($timeslot,$booking_date){
        	
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
					$slotcapacity = get_post_meta(get_the_ID(), 'slotcapacity', true);	
					$booking_status = get_post_meta(get_the_ID(), 'entry_status', true);	
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
		 * 
		 * fetch booking form
		 * 
		 */
		function zealsab_get_booking_form($attr) {
			ob_start();	
			
			$post_id = $attr['form_id'];	
			$enable_booking = get_post_meta($post_id, 'enable_booking', true);
			$prefix_label = get_post_meta($post_id, 'label_symbol', true);
			$cost = get_post_meta($post_id, 'cost', true);
			?>
			<!-- Preloader element -->
			<?php
			if(isset($enable_booking) && !empty($enable_booking)){	
				$cal_title = get_post_meta($post_id, 'cal_title', true);
				$cal_description = get_post_meta($post_id, 'cal_description', true);
				$currentMonth = date('m');
				$currentYear = date('Y');

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
				$firstDayOfWeek = date('w', strtotime($currentYear . '-' . $currentMonth . '-01'));
				$firstDayOfWeek += 1;
				
				// Output the calendar
				?>
				<div class='zfb-smart-calender container alignwide' id='calender_reload'>
					<div class="step step1">
						<div class=''>
							<span class='zfb-caltitle'><?php echo $cal_title; ?></span>
							<p class='zfb-cal-desc'><?php echo $cal_description; ?></p>
						</div>
						<div class="month-navigation zfb-cal-container" id="month-navigationid">
							<div class="header-calender">
								<input type="hidden" id="zealform_id" value="<?php echo $post_id; ?>">
								
								<span class="arrow" id="prev-month" onclick="getClicked_prev(this)">&larr;</span>
								<!-- months -->
								<select name='sab_month_n' id='sab_month'>
									<?php
									for ($i = 1; $i <= 12; $i++) {
										echo "<option value='$i'";
										if ($i == $currentMonth) {
											echo " selected";
										}
										echo ">{$monthNames[$i]}</option>";
									}
									?>
								</select>
								<!-- Year -->
								<select name="sab_year_n" id="sab_year">
									<?php
									$startYear = $currentYear + 5;
									$endYear = 2023; //as plugin has been plubished there will be no previous entry
									for ($year = $startYear; $year >= $endYear; $year--) {
										echo "<option value='$year'";
										if ($year == $currentYear) {
											echo " selected";
										}
										echo ">$year</option>";
									}
									?>
								</select>
								<span class="arrow" id="next-month" onclick="getClicked_next(this)">&rarr;</span>
							</div>
							
						
							<table class="zfb-cal-table zfb-cal-table-bordered" id="booking_cal_table">
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
											$isToday = ($date == date('j') && $monthYear == date('n-Y')) ? "calselected_date" : "";
											if ($isToday === "calselected_date") {
												$lastdateid = 'sabid_' . $post_id . '_' . $currentMonth . '_' . $date . '_' . $currentYear;
												$lastday = $date;
												$lastmonth = $currentMonth;
												$lastyear = $currentYear;
											}
											echo "<td id='sabid_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' data_day='sabid_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' class='sab_cal_day $isToday' onclick='getClickedId(this)'>$date</td>";
											$date++;
										} elseif ($dayCounter < $firstDayOfWeek) {
											$prevDate = $daysInPreviousMonth - ($firstDayOfWeek - $dayCounter) + 1;
											echo "<td class='previous-month'>$prevDate</td>";
										} else {
											$nextDate = $dayCounter - ($totalDays + $firstDayOfWeek) + 1;
											echo "<td class='next-month'>$nextDate</td>";
										}

										$dayCounter++;
									}

									echo "</tr>";
								}
								?>
							</table>
					
						</div>
						<!-- // Output the additional div with the provided heading and time slots -->
						<div class='timeslot_result_c' id='zfb-timeslots-table-container' style='display: inline-block; vertical-align: top;'>
							<?php
							$timezone = get_post_meta($post_id,'timezone',true);
							$error = false;
							$TodaysDate = date('F d, Y');	
							$todaysDate = date('Y-m-d');
							echo "<h3 id='head_avail_time'><span class='gfb-timezone'>Timezone: ".$timezone."</span></h3>";
							echo "<h4 id='headtodays_date'>$TodaysDate</h4>";			
							// Get array of available dates 
							$is_available = $this->processDate($post_id,$todaysDate);
						
							?>
							<ul id='zfb-slot-list'>
								<?php
												
								if(isset($is_available) && is_array($is_available) && in_array($todaysDate,$is_available)){
									$check_type = get_post_meta($post_id, 'enable_recurring_apt', true);
									echo $enable_advance_setting = get_post_meta($post_id, 'enable_advance_setting', true);
									
									if($enable_advance_setting && isset($enable_advance_setting)){
										
										echo $this->get_advanced_timeslots($post_id,$lastdateid,$todaysDate);	
									}else{
										echo $this->front_generate_timeslots($post_id,$lastdateid);	
										
									}						
													
								}else{		
									$error = true;
									error_log('Not Available');
									
								}
									
								?>
							</ul>
							<?php 
								if($error === true){
									echo __('No timeslots found for selected date.','smart-appointment-booking');
								}else{
									echo '<input class="zfb-selected-capacity" type="number" name="zfbslotcapacity" placeholder="Enter Slot Capacity" min="1" value="1">';
									echo '<p id="no-timeslots-message" class="h5" style="display: none;">No Timeslots found!</p>';
								}
							?>
						</div>
						<div class="zfb-cost-label">
							<span class="zfb-cost">Cost: <?php echo $prefix_label. ' '. $cost; ?></span>
						</div>
						<input type="hidden" id="booking_date" name="booking_date" value="<?php echo $lastdateid; ?>" name="booking_date" >
					</div>
					<div class="step step2">
					<?php	
					
				}
					if (get_post($post_id)) {
						$post_status = get_post_status($post_id);
						if ($post_status === 'publish') {

								$fields = get_post_meta($post_id, '_formschema', true);
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
												var msgloader = jQuery('#zfbform-message'); 
												jQuery('#backButton').hide();
												if (isSubmitting) {	
													jQuery('#zfbform-message').html("Already Submitted!").fadeIn().delay(1000).fadeOut();																								
													return;
												}												
												isSubmitting = true;
												var formid = <?php echo json_encode($post_id); ?>;
												var booking_date = jQuery('input[name="booking_date"]').val();
												var timeslot = "";
												var slotcapacity = "";

												jQuery('.zfb_timeslot').each(function() {
													if (jQuery(this).hasClass('selected')) {
														timeslot = jQuery(this).find('input[name="booking_slots"]').val();	
														slotcapacity = jQuery(this).find('.zfb-tooltip-text').attr('data-seats');
													} 
												});
												bookedseats = jQuery('input[name="zfbslotcapacity"]').val();
												jQuery.ajax({
													url: '<?php echo admin_url('admin-ajax.php'); ?>',
													type : 'post',
													data: { 
													action: "zfb_booking_form_submission",
													form_data: submission,
													fid:formid,
													timeslot:timeslot,
													booking_date:booking_date,
													bookedseats:bookedseats,
													slotcapacity:slotcapacity,
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
															console.log(response.data.message);
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
												var formid = <?php echo json_encode($post_id); ?>;
												if (isSubmitting) {
													jQuery('#formio_res_msg').html("Already Submitted!").fadeIn().delay(1000).fadeOut();		
													return; 
												}
												isSubmitting = true; 												
												jQuery.ajax({
													url: '<?php echo admin_url('admin-ajax.php'); ?>',
													type : 'post',
													data: { 
													action: "zfb_save_form_submission",
													form_data: submission,
													fid:formid,
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
															$("button[name='data[submit]'] i.fa.fa-refresh.fa-spin.button-icon-right").hide();
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
								echo __('Form data not found.', 'bms');
								}
							
						} else {
							echo __("Post exists but is not published.", 'bms');
						}
					} else {
						echo __("Post does not exist.", 'bms');
					}
			if(isset($enable_booking) && !empty($enable_booking)){
			
			?>
				</div>
			</div>
			<div class="dc_backButton alignwide">
				<button id="backButton">Back</button>
				<button id="nextButton">Next</button>
			</div>

			<div id="zfbform-message"></div>
			<?php
				
			}

			return ob_get_clean();
		  }
		 /**
		 * 
		 * Calender on change of month and year
		 * 
		 */
		  function action_reload_calender(){
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
				$currentMonth = isset($_POST['currentMonth']) ? intval($_POST['currentMonth']) : date('n');
				$currentMonth = max(1, min(12, $currentMonth)); // Ensure currentMonth is between 1 and 12
				$currentYear = isset($_POST['currentYear']) ? intval($_POST['currentYear']) : date('Y');
				$post_id = isset($_POST['form_id']) ? $_POST['form_id'] : '';
				$running_year = date("Y");
				ob_start();
			?>
			<div class="header-calender">
				<input type="hidden" id="zealform_id" value="<?php echo $post_id; ?>" >
                <span class="arrow" id="prev-month" onclick="getClicked_prev(this)">&larr;</span>
                <select name='sab_month_n' id='sab_month'>
                    <?php
                    for ($i = 1; $i <= 12; $i++) {
                        echo "<option value='$i'";
                        if ($i == $currentMonth) {
                            echo " selected";
                        }
                        echo ">{$monthNames[$i]}</option>";
                    }
                    ?>
                </select>
			
				<select name="sab_year_n" id="sab_year">
					<?php
					$futureYear = date("Y", strtotime("+10 years", strtotime("January 1, $currentYear")));
					echo '<optgroup label="Current Year">';
					echo "<option value='$running_year'>$running_year</option>";
					echo '</optgroup>';
					echo '<optgroup label="Years">';
					for ($year = $futureYear; $year >= $currentYear; $year--) {
						echo "<option value='$year'";
						if ($year == $currentYear) {
							echo " selected";
						}
						echo ">$year</option>";
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
                $firstDayOfWeek = date('N', strtotime($currentYear . '-' . $currentMonth . '-01'));
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
                             echo "<td  id='sabid_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' data_day='sabid_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' class='sab_cal_day' onclick='getClickedId(this)'>$date</td>";
                            $date++;
                        }
						elseif ($dayCounter < $firstDayOfWeek) {
                            $prevDate = $daysInPreviousMonth - ($firstDayOfWeek - $dayCounter - 1);
                            echo "<td class='previous-month'>$prevDate</td>";
                        }
						else {
                            $nextDate = $dayCounter - ($totalDays + $firstDayOfWeek) + 1;
                            echo "<td class='next-month'>$nextDate</td>";
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
			echo $output;
			wp_die();
		  }
		/**
		 * 
		 * Display Timeslots
		 * 
		 */
		  function action_display_available_timeslots(){
				$error = false;
				if(isset( $_POST['form_data'])){
					$form_data = $_POST['form_data'];
					$array_data = explode('_',$form_data);
					$post_id = $array_data[1];
					$current_month = $array_data[2];
					$current_day = $array_data[3];
					$current_year = $array_data[4];
				}
				if(isset( $_POST['clickedId'])){
					$clickedId = $_POST['clickedId'];
				}
				$todaysDate = date('Y-m-d', strtotime("$current_year-$current_month-$current_day"));
				$TodaysDate_F = date('F d, Y', strtotime("$current_year-$current_month-$current_day"));
				echo "<h3 id='head_avail_time'>Available Time Slots</h3>";
				echo "<h4 id='headtodays_date'>$TodaysDate_F</h4>";
				echo '<input type="hidden" id="zeallastdate" name="zeallastdate" value="'.$clickedId.'" >';
				echo "<ul id='zfb-slot-list'>";
                    
					$is_available = $this->processDate($post_id,$todaysDate);
                    if (isset($is_available) && is_array($is_available)) {
                        if (in_array($todaysDate, $is_available)) {
                            $check_type = get_post_meta($post_id, 'enable_recurring_apt', true);
                            $enable_advance_setting = get_post_meta($post_id, 'enable_advance_setting', true);

							if($enable_advance_setting && !empty($enable_advance_setting)){
								$advancedata = get_post_meta($post_id, 'advancedata', true);
								foreach ($advancedata as $item) {
									$advanceDates[] = $item['advance_date'];
								}
								if($check_type && !empty($check_type)){
									if($advanceDates && isset($advanceDates) && in_array($todaysDate,$advanceDates)){
										echo $this->get_advanced_timeslots($post_id,$form_data,$todaysDate);
									}else{
										echo $this->front_generate_timeslots($post_id,$form_data);	
									}
								}else{
									echo $this->get_advanced_timeslots($post_id,$form_data,$todaysDate);
								}
							}else{
                                echo $this->front_generate_timeslots($post_id,$form_data);		
                            }
                        } else {
							$error = true;
                            error_log('Check End date! Selected date exceed the selected end date');
                            
                        }
                    } else {
						$error = true;
                        error_log('Array does not exist.');
                    }	
				echo "</ul>";
				if($error === true){
					echo __('No timeslots found for selected date. ','smart-appointment-booking');
				}else{
					echo '<input class="zfb-selected-capacity" type="number" name="zfbslotcapacity" placeholder="Enter Slot Capacity" min="1" value="1">';
					echo '<p id="no-timeslots-message" style="display: none;">No Timeslots found!</p>';
				}
				wp_die();
		  }
		/**
		 * 
		 * Cancel Booking
		 * 
		 */
		  function zfb_cancel_booking() {
			$encrypt_bookingId = $_POST['bookingId'];
			
			$response = array();
		
			if (isset($_POST['bookingId']) && isset($_POST['bookingstatus'])) {
				$booking_id = base64_decode($encrypt_bookingId);
				$bookingstatus = $_POST['bookingstatus'];
		
				if ($bookingstatus === 'cancel') {
					if (isset($_POST['status'])) {
						if ($_POST['status'] === 'check') {
							$get_current_status = get_post_meta($booking_id, 'entry_status', true);
							if ($get_current_status === 'cancelled') {
								$response = array(
									'message' => __('Booking already cancelled', 'smart-appointment-booking'),
									'error' => true,
									'status' => 'check',
								);
							} else {
								$response = array(
									'message' => __('Booking cancellation ready for confirmation', 'smart-appointment-booking'),
									'error' => 'false',
									'status' => 'readytoconfirm',
								);
							}
						}
						if ($_POST['status'] === 'confirm') {
							update_post_meta($booking_id, 'entry_status', 'cancelled');
							$get_current_status = get_post_meta($booking_id, 'entry_status', true);
							$response = array(
								'message' => __('Booking has been cancelled successfully', 'smart-appointment-booking'),
								'error' => 'false',
								'status' => 'updated',
							);

						}
					}
				} else {
					$response = array(
						'message' => __('Something went wrong, please try again later', 'smart-appointment-booking'),
						'error' => true,
						'status' => 'check',
					);
				} 
			} else {
				$response = array(
					'message' => __('Invalid URL.', 'smart-appointment-booking'),
					'error' => true,
				);
			}
			wp_send_json($response);
			wp_die();
		}
		/**
		 * 
		 * confirm_booking_cancellation
		 * 
		 */
		function confirm_booking_cancellation() {
			ob_start();
			echo '<div class="booking-cancellation-card">';
			$encrypt_bookingId = $_REQUEST['booking_id'];	
			if (isset($_REQUEST['booking_id']) && isset($_REQUEST['status'])) {
				$booking_id = base64_decode($encrypt_bookingId);
				$bookingstatus = $_REQUEST['status'];

				if ($bookingstatus === 'cancel' ) {

					$get_current_status = get_post_meta($booking_id,'entry_status',true);
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
						
						<style>
							.booking-cancellation-card {
								background: #ffffff;
								padding: 20px;
								border-radius: 5px;
								box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
							}
		
							.booking-cancellation-card label {
								font-size: 18px;
								margin-bottom: 10px;
							}
							.booking-cancellation-buttons button {
								padding: 8px 12px;
								font-size: 16px;
								border-radius: 4px;
								cursor: pointer;
							}
		
							.booking-cancellation-buttons button.btn-yes {
								background: #4caf50;
								color: #ffffff;
							}
		
							.booking-cancellation-buttons button.btn-no {
								background: #ff0000;
								color: #ffffff;
							}
		
						</style>
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
		function cancel_booking_shortcode() {
			$response = array(					
				'message' => __('','smart-appointment-booking'),
				'mail_message' => '',
				
			);

			if (isset($_POST['bookingId'])) {
				$get_bookingId = sanitize_text_field($_POST['bookingId']);
				$bookingId = base64_decode($get_bookingId);
				$status = 'cancelled';
				$formdata = get_post_meta($bookingId,'sab_submission_data',true);
				$form_id = get_post_meta($bookingId,'sab_form_id',true);
				update_post_meta($bookingId, 'entry_status', $status);
			
				$listform_label_val = $this->create_key_value_formshortcodes($bookingId,$formdata);
				$listform_label_val['Status'] = $status;
				
				$message = $this->zfb_send_notification($status,$form_id, $bookingId, $listform_label_val );
			
				$response = array(					
					'message' => __('Your booking has been cancelled succesfully','smart-appointment-booking'),
					'mail_message' => $message,
					
				);
			}

			wp_send_json($response);
			wp_die();
		}
		/**
		 * function to create shortcode pair for email notification
		 */
		function create_key_value_formshortcodes($bookingId,$form_data){
			
			$form_id = get_post_meta($bookingId,'sab_form_id',true);
			$FormTitle = get_the_title( $form_id );
			
			$get_user_mapping = get_post_meta($form_id, 'user_mapping', true);
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
			$timeslot = get_post_meta($bookingId,'timeslot',true);
			$BookingDate = get_the_date( 'M d,Y', $form_id );
			
			$booking_date = get_post_meta($bookingId,'booking_date',true);
			$no_of_seats = $this->get_available_seats_per_timeslot($timeslot, $booking_date);
			
			$explode_booking_date = explode('_',$booking_date);
			$explode_timeslot = explode('-',$timeslot);

			$format_bookingdate = $explode_booking_date[4] . "-" . $explode_booking_date[2] . "-" . $explode_booking_date[3];
			$converted_bookingdate = date('Y-m-d', strtotime($format_bookingdate));
			
			$encrypted_booking_id = base64_encode($bookingId);
			$user_mapping = get_post_meta($form_id, 'user_mapping', true);
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
			$no_of_booking = get_post_meta($form_id, 'no_of_booking', true);
			
			$checkseats = $this->get_available_seats_per_timeslot($timeslot,$converted_bookingdate);
			if($checkseats >  $no_of_booking ){
				$available_seats = 0;
			}else{
				$available_seats = $no_of_booking - $checkseats;
			}

			$prefixlabel = get_post_meta( $form_id, 'label_symbol', true );
			$cost = get_post_meta( $form_id, 'cost', true );
			

			$bookedseats = get_post_meta($bookingId,'slotcapacity',true);
			
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
		$SAB_Front_Action = new SAB_Front_Action();
	} );
}