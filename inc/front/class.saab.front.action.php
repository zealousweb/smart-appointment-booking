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
			add_shortcode('saab_summary',array( $this, 'saab_summary' ));
			add_shortcode('saab_add_to_cal', array( $this, 'saab_add_event_to_calender' ) ); 
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
		function saab_summary() {
			ob_start();
			$user_id = get_current_user_id();
			$post_ids = array(); 
			$args = array(
				'post_type' => 'manage_entries',
				'meta_key' => 'user_mapped',
				'meta_value' => $user_id,
				'fields' => 'ids',
				'posts_per_page' => 55,
			);
		
			$query = new WP_Query($args);
		
			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$post_ids[] = get_the_ID(); 
					$post_id = get_the_ID(); 
					$form_id = get_post_meta( $post_id, 'saab_form_id', true ); 
					$timeslot = get_post_meta( $post_id, 'saab_timeslot', true );
					$cost = get_post_meta($post_id, 'saab_cost', true);
					if(isset($timeslot) && !empty($timeslot)){
						$times = explode("-", $timeslot);
						// echo $times[0];
						$time_obj_0 = DateTime::createFromFormat('h:i A', $times[0]);
						$start_time = $time_obj_0->format('H:i A');
						$time_obj_1 = DateTime::createFromFormat('h:i A', $times[1]);
						$end_time = $time_obj_1->format('H:i A');				
					}
					$booking_date = get_post_meta( $post_id, 'saab_booking_date', true );
					if($booking_date && !empty($booking_date)){
						$array_of_date = explode('_', $booking_date);
					}
					if(isset($array_of_date) && !empty($array_of_date[2]) && !empty($array_of_date[3]) && !empty($array_of_date[4])){
						$bookedmonth = $array_of_date[2];
						$bookedday = $array_of_date[3];
						$bookedyear = $array_of_date[4];
						$booked_date = $bookedday . "-" . $bookedmonth . "-" . $bookedyear;
						$booked_date = date('d F Y', strtotime($booked_date)); //phpcs:ignore
						$slotcapacity = get_post_meta( $post_id, 'saab_slotcapacity', true );   
					}
					$payment_mode = get_post_meta($post_id, 'saab_payment_mode',true);
					$payment_status =  get_post_meta($post_id, 'saab_payment_status',true); 	
					$appointment_type = get_post_meta($post_id, 'saab_appointment_type', true); 
	
					?>
					<div class="booking-details">
						<div class="booking-detail">
							<p class="h6">Booked Timeslot: <?php echo isset($start_time) ? esc_attr($start_time) : ''; ?> to <?php echo isset($end_time) ? esc_attr($end_time) : ''; ?></p>
						 </div>
						<div class="booking-detail">
							<p class="h6">Booking Date: <?php echo esc_html( $booked_date ); ?></p>						
						</div>
						<div class="booking-detail">
							<p class="h6">Cost: <?php echo esc_html($cost); ?></p>						
						</div>
						
						<?php if ($appointment_type) { ?>
							<div class="booking-detail">
								<p class="h6">Appointment Type: <?php echo esc_html( $appointment_type ); ?></p>
							</div>
						<?php } ?>
	
						<div class="booking-detail">
							<?php 
							$entry_status = get_post_meta($post_id, 'saab_entry_status', true);
							?>
							<p class="h6">Booking Status: <?php echo esc_html( ucfirst($entry_status) ); ?></p>
							
						</div>
	
						<div class="booking-detail">
							<?php
							if ($entry_status === 'waiting') {
								$slotcapacity;
							} else {
								$slotcapacity;
							}
							?>
							<p class="h6">No of Bookings: <?php echo wp_kses_post( $slotcapacity ); ?></p>
						
						</div>
	
						<?php if ($payment_mode) { ?>
							<div class="booking-detail">
								<p class="h6">Payment Mode: <?php echo wp_kses_post( ucfirst($payment_mode) ); ?></p>
							</div>
						<?php } ?>
	
						<?php if ($payment_status) { ?>
							<div class="booking-detail">
								<p class="h6">Payment Status: <?php echo wp_kses_post( $payment_status ); ?></p>
							</div>
	
							<div class="booking-detail">
								<p class="h6">Payment Status: <?php echo wp_kses_post( $payment_status ); ?></p>
							</div>
						<?php } ?>
					</div>
					<?php
				}				
				wp_reset_postdata();
			} else {
				echo 'No posts found matching user ID: ' . esc_html( $user_id );
			}
			return ob_get_clean();
		}
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
			date_default_timezone_set($get_timezone); //phpcs:ignore
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

		function saab_save_form_submission() {	
			// ini_set('display_startup_errors', 1);
			// ini_set('display_errors', 1);
			// error_reporting(-1);
		//if( ! wp_verify_nonce( 'saab_front_nonce' ) ){} // ignoring nonce validation error in the front form		
		$form_id = ( isset( $_POST['fid'] ) ) ? $_POST['fid'] : '';
		$form_data = ( isset( $_POST['form_data'] ) ) ? $_POST['form_data'] : '';
		// User 
		$is_user_logged_in = is_user_logged_in();
		$userLoginRequired = get_post_meta($form_id, 'saab_userLoginRequired', true);
		$enableCreateUser = get_post_meta($form_id, 'saab_enableCreateUser', true);
		if ($enableCreateUser && !$is_user_logged_in) {

			$map_first_name = get_post_meta($form_id, 'saab_map_first_name', true);  
			$map_username = get_post_meta($form_id, 'saab_map_username', true);  
			$map_password = get_post_meta($form_id, 'saab_map_password', true); 
			
			$map_last_name = get_post_meta($form_id, 'saab_map_last_name', true); 
			$map_email = get_post_meta($form_id, 'saab_map_email', true); 
			$map_role = get_post_meta($form_id, 'saab_map_role', true); 

			if (isset($form_data['data'])) {
				if (in_array($map_first_name, array_keys($form_data['data']))) {
					$user_first_name = $form_data['data'][$map_first_name];
				}

				if (in_array($map_username, array_keys($form_data['data']))) {
					$user_username = $form_data['data'][$map_username];
				}
				if ($map_password || empty($map_password) || $map_password === 'auto') {
					$user_password = wp_generate_password();
				}else{
					if (in_array($map_password, array_keys($form_data['data']))) {
						$user_password = $form_data['data'][$map_password];
					}else{
						$user_password = wp_generate_password();
					}
				}

				if (in_array($map_last_name, array_keys($form_data['data']))) {
					$user_last_name = $form_data['data'][$map_last_name];
				}

				if (in_array($map_email, array_keys($form_data['data']))) {
					$user_email = $form_data['data'][$map_email];
				}
			}
			
			if(filter_var($user_email, FILTER_VALIDATE_EMAIL)){
				
				if( !empty( trim( $user_email ) ) ){
				
					if(false == email_exists( $user_email ) &&	false == username_exists($user_username)){
						
						if( !$user_password && empty( $user_password ) ) {
							$user_password = wp_generate_password( $length = 12, $include_standard_special_chars = true );
						}
						if(empty($map_role)){
							$map_role = 'subscriber';
						}
						if(empty($user_username)){
							$user_username = $user_email;
						}
						if( !empty( $user_password ) ) {
							//insert the user if email is not exists
							$userdata = array(
								'user_login'	=>  wp_slash($user_username),
								'user_pass'		=>  $user_password,
								'user_email'	=>  wp_slash($user_email),
								'role'			=>	$map_role,
							);
							$user_id = wp_insert_user( $userdata );
							if ( is_wp_error( $user_id  ) ) {
							
								$usererror = true;
								wp_send_json_error(array(
									'message' => __('Error creating user '. $user_id->get_error_message(),'smart-appointment-booking'),
									'error' => $usererror,
								));

							} else {
								update_user_meta($user_id,'saab_user','saab_user');
								update_user_meta($user_id,'temp_storage',$user_password);
								$usererror = false;										
							}
						
						}
					}else{
						if( defined('WP_DEBUG') && true === WP_DEBUG ){ 
							error_log('Email Already exist'); //phpcs:ignore
						}
						$error = true;
						wp_send_json_error(array(
							'message' => __('Email or Username Already exist','smart-appointment-booking'),
							'error' => $error,
						));
					}

				}else{
					if( defined('WP_DEBUG') && true === WP_DEBUG ){ 
						error_log('Email is empty'); //phpcs:ignore
					}
					$error = true;
					wp_send_json_error(array(
						'message' => __('Email is empty','smart-appointment-booking'),
						'error' => $error,
					));

				}

			}else{
				if( defined('WP_DEBUG') && true === WP_DEBUG ){ 
					error_log('Email Validation Error'); //phpcs:ignore
				}
				$error = true;
				wp_send_json_error(array(
					'message' => __('Email Validation Error','smart-appointment-booking'),
					'error' => $error,
				));

			}
		}
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

		$new_post_title = 'entry_#' . $created_post_id;
		$new_post_slug = 'saab_entry_' . $created_post_id;

		$updated_post_data = array(
			'ID' => $created_post_id,    
			'post_title' => $new_post_title,
			'post_name' => $new_post_slug, 
		);

		wp_update_post($updated_post_data);

		if ($userLoginRequired && $is_user_logged_in) {
			$user_id = get_current_user_id();
			update_post_meta($created_post_id, 'saab_user_mapped', $user_id);
		}else{
			if(isset($user_id) && !empty($user_id)){
				update_post_meta($created_post_id, 'saab_user_mapped', $user_id);
			}

		}
		if(!empty($user_id)){
			if(empty($user_id)){
				$user_id = '';
			}
			if(empty($user_username)){
				$user_username = '';
			}
			if(empty($user_password)){
				$user_password = '';
			}
			if(empty($user_email)){
				$user_email = '';
			}
			// $temp_user_data = array(
			// 	'userid' => $user_id,
			// 	'usename' => $user_username,
			// 	'userpass' => $user_password,
			// 	'useremail' => $user_email,
			// );
			// update_user_meta($user_id,'temp_userdata',$temp_user_data);
		}
		update_option('tot_manage_entries', $pid);
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
			
		//check payments
		$saab_enableStripePayment = get_post_meta($form_id, 'saab_enableStripePayment', true);

		$payment_status='deactivated';
		update_post_meta($created_post_id, 'saab_payment_mode','none');
		$payment_response = '';
		// if ($saab_enableStripePayment && $error !== true) {
		if ($saab_enableStripePayment) {
			
			$saab_testPublishableKey = get_post_meta($form_id, 'saab_testPublishableKey', true);  
			$saab_testSecretKey = get_post_meta($form_id, 'saab_testSecretKey', true);  
			$saab_customerEmail = get_post_meta($form_id, 'saab_customerEmail', true); 
			$saab_quantity = get_post_meta($form_id, 'saab_quantity', true); 
			$saab_currency = get_post_meta($form_id, 'saab_currency', true); 
			$saab_lastName = get_post_meta($form_id, 'saab_lastName', true); 
			$saab_address = get_post_meta($form_id, 'saab_address', true);  
			$saab_state = get_post_meta($form_id, 'saab_state', true);  
			$saab_country = get_post_meta($form_id, 'saab_country', true); 
			$saab_enableTestMode = get_post_meta($form_id, 'saab_enableTestMode', true); 
			$saab_livePublishableKey = get_post_meta($form_id, 'saab_livePublishableKey', true); 
			$saab_liveSecretKey = get_post_meta($form_id, 'saab_liveSecretKey', true); 
			$saab_amount = get_post_meta($form_id, 'saab_amount', true);  
			$saab_description = get_post_meta($form_id, 'saab_description', true);  
			$saab_firstName = get_post_meta($form_id, 'saab_firstName', true); 
			$saab_companyName = get_post_meta($form_id, 'saab_companyName', true); 
			$saab_city = get_post_meta($form_id, 'saab_city', true); 
			$saab_zipCode = get_post_meta($form_id, 'saab_zipCode', true); 
			$metadata = array();
			if (isset($form_data['data'])) {
				if (in_array($saab_testPublishableKey, array_keys($form_data['data']))) {
					$saab_testPublishableKey = $form_data['data'][$saab_testPublishableKey];
				}
				if (in_array($saab_testSecretKey, array_keys($form_data['data']))) {
					$saab_testSecretKey = $form_data['data'][$saab_testSecretKey];
				}
				if (in_array($saab_enableTestMode, array_keys($form_data['data']))) {
					$saab_enableTestMode = $form_data['data'][$saab_enableTestMode];
				}
				if (in_array($saab_livePublishableKey, array_keys($form_data['data']))) {
					$saab_livePublishableKey = $form_data['data'][$saab_livePublishableKey];
				}
				if (in_array($saab_liveSecretKey, array_keys($form_data['data']))) {
					$saab_liveSecretKey = $form_data['data'][$saab_liveSecretKey];
				}
				if (in_array($saab_firstName, array_keys($form_data['data']))) {
					$saab_firstName = $form_data['data'][$saab_firstName];
					$metadata['first_name'] = $saab_firstName;
				}
				if (in_array($saab_lastName, array_keys($form_data['data']))) {
					$saab_lastName = $form_data['data'][$saab_lastName];
					$metadata['last_name'] = $saab_lastName;
				}
				if (in_array($saab_companyName, array_keys($form_data['data']))) {
					$saab_companyName = $form_data['data'][$saab_companyName];
					$metadata['company_name'] = $saab_companyName;
				}
				if (in_array($saab_address, array_keys($form_data['data']))) {
					$saab_address = $form_data['data'][$saab_address];
					$metadata['address'] = $saab_address;
				}
				if (in_array($saab_city, array_keys($form_data['data']))) {
					$saab_city = $form_data['data'][$saab_city];
					$metadata['city'] = $saab_city;
				}
				if (in_array($saab_state, array_keys($form_data['data']))) {
					$saab_state = $form_data['data'][$saab_state];
					$metadata['state'] = $saab_state;
				}
				if (in_array($saab_zipCode, array_keys($form_data['data']))) {
					$saab_zipCode = $form_data['data'][$saab_zipCode];
					$metadata['zip_code'] = $saab_zipCode;
				}		
				if (in_array($saab_country, array_keys($form_data['data']))) {
					$saab_country = $form_data['data'][$saab_country];
					$metadata['country'] = $saab_firstName;
				}				
				if (in_array($saab_description, array_keys($form_data['data']))) {
					$saab_description = $form_data['data'][$saab_description];
				}
				if (in_array($saab_amount, array_keys($form_data['data']))) {
					$saab_amount = $form_data['data'][$saab_amount];
				}
				if (in_array($saab_quantity, array_keys($form_data['data']))) {
					$saab_quantity = $form_data['data'][$saab_quantity];
				}
				if (in_array($saab_currency, array_keys($form_data['data']))) {
					$saab_currency = $form_data['data'][$saab_currency];
				}
				if (in_array($saab_customerEmail, array_keys($form_data['data']))) {
					$saab_customerEmail = $form_data['data'][$saab_customerEmail];
				}
			}
								
			// if ($saab_enableStripePayment) { // Check if payment is enabled
			// 	if ($saab_enableTestMode && !empty($saab_testPublishableKey) && !empty($saab_testSecretKey)) {
			// 		// Use test mode and test keys							
			// 		$publishableKey = $saab_testPublishableKey;
			// 		$secretKey = $saab_testSecretKey;
			// 	} elseif (!$saab_enableTestMode && !empty($saab_livePublishableKey) && !empty($saab_liveSecretKey)) {							
			// 		$publishableKey = $saab_livePublishableKey;
			// 		$secretKey = $saab_liveSecretKey;
			// 	}
			// }
			if(empty($saab_amount)){
				$error_message = "Amount configuration Error";
				wp_delete_post($created_post_id, true); 
				wp_send_json_error(array(
					'message' => __($error_message, 'smart-appointment-booking'),
					'error' => true,
				));
			}
			$stripetoken = ( isset( $_POST['token'] ) ) ? $_POST['token'] : '';
			// Set your Stripe Publishable key
			SabStripe::setApiKey($secretKey); // Replace with your Stripe API key

			try {
				// Check if the customer already exists based on email
				$existing_customer = SabCustomer::all(['email' => $saab_customerEmail, 'limit' => 10000]);
				
				if (count($existing_customer->data) === 0) {
					// Create a new customer if they don't exist
					$customer = SabCustomer::create([
						'name' => $saab_firstName,
						'email' => $saab_customerEmail,
						'source' => $stripetoken, 
						'address' => [
							'city' => $saab_city,
							'country' => $saab_country,
							'line1' => $saab_address,
							'postal_code' => $saab_zipCode,
							'state' => $saab_state,
						],
					]);
					$customer_id = $customer->id;
				} else {
					$customer_id = $existing_customer->data[0]->id;
				}
				$totalAmount = (float) ( empty( $saab_quantity ) ? $saab_amount : ( $saab_quantity * $saab_amount ) );
				$totalAmount = sprintf('%0.2f', $totalAmount) * 100;

				// $paymentIntent = SabPaymentIntent::create([
				// 	'payment_method_types' => ['card'],
				// 	'description'=> $saab_description,
				// 	'amount' => $totalAmount, 
				// 	'currency' => $saab_currency,
				// 	'customer' => $customer_id,
				// 	'metadata' => $metadata,
					
				// ]);
				
				// update_post_meta($created_post_id, 'saab_payment_mode','stripe');
				// update_post_meta($created_post_id, 'saab_payment_response',$paymentIntent);
				// $payment_response = ( is_array( $paymentIntent ) || is_object( $paymentIntent ) ) ?  print_r( $paymentIntent, true ) : $paymentIntent;

			}catch ( Exception $e ) {
				$error_message = $e->getMessage();
				wp_delete_post($created_post_id, true);
				wp_send_json_error(array(
					'message' => __($error_message, 'smart-appointment-booking'),
					'error' => true,
				));
			}
		}
		$encrypted_booking_id = base64_encode($created_post_id);
		$user_mapping = get_post_meta($form_id, 'saab_user_mapping', true);
		if ($user_mapping) {
			$cancelbooking_pageid = isset($user_mapping['cancel_bookingpage']) ? sanitize_text_field($user_mapping['cancel_bookingpage']) : '';
			$cancelbooking_url = get_permalink($cancelbooking_pageid).'?booking_id=' . $encrypted_booking_id . '&status=cancel';
		} else {
			$cancelbooking_url = home_url('/?booking_id=' . $encrypted_booking_id . '&status=cancel');
		}				
		$other_label_val = array();
		$publishedDate = get_the_date( 'M d,Y', $form_id );
		$FormTitle = get_the_title( $form_id );
		if(empty($user_password)){
			$user_password = '';
		}
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
			//'Payment_intent_status' => $paymentIntent->status,
			'UserPassword' => $user_password,
			
		);
			$status = 'submitted';
			update_post_meta($created_post_id, 'saab_entry_status', $status);				
			$listform_label_val = array_merge($submission_key_val, $other_label_val);
			update_post_meta($created_post_id, 'saab_listform_label_val', $listform_label_val);
			$mail_response = array(
				'status'=>$status,
			);
			update_post_meta($created_post_id, 'saab_mail_response', $mail_response);
			$confirmation = get_post_meta($form_id, 'saab_confirmation', true);
			$redirect_url = '';
			
			if ($confirmation === 'redirect_text') {
				$wp_editor_value = get_post_meta($form_id, 'saab_redirect_text', true);
				$editor_value = wpautop(wp_kses_post($wp_editor_value));
				$shortcodesArray = $this->front_get_shortcodes($form_id); //replace shortcodes with
				$editor_check_shortcodes = $this->saab_check_shortcodes_exist_in_editor($wp_editor_value, $form_id, $listform_label_val, $shortcodesArray );
				
			} elseif ($confirmation === 'redirect_page') {
				$redirect_page = get_post_meta($form_id, 'saab_redirect_page', true);
				$redirect_url = get_permalink($redirect_page);
			} elseif ($confirmation === 'redirect_to') {
				$redirect_url = get_post_meta($form_id, 'saab_redirect_url', true);
				$shortcode_patterns = array(
					'/\[postid\]/',
					'/\[formid\]/',
				);
				$replacement_values = array(
					'post_id=' . $created_post_id,
					'form_id=' . $form_id,
				);
				$redirect_url = preg_replace($shortcode_patterns, $replacement_values, $redirect_url);
				$test = "test";
			}
			
			wp_send_json_success(array(					
				'message' => 'Sucessfully Submitted',
				'redirect_page' => $redirect_url,
				'wp_editor_value' => $editor_check_shortcodes,
				'redirect_url' => $redirect_url,
				'mail_response' =>$mail_response,
				'confirmation' => $confirmation,
				'payment'=>$paymentIntent->client_secret,
				'payment_response'=>$payment_response,
				'client_secret'=>$paymentIntent->client_secret,
				'payment_enabled'=>$saab_enableStripePayment,
				'fpid'=>$form_id.'T'.$created_post_id,
				'saab_amount'=>$saab_amount,
				
			));
		wp_die();
	}
		/**
		 * Saves the results of a booking calendar form submission.
		 *
		 * This function handles the submission of a booking calendar form, processes the data, and stores the results.
		 * It may perform tasks like validating the input, updating the database, and sending notifications.
		 *
		 */	
			
		function saab_booking_form_submission() {
// 			ini_set('display_startup_errors', 1);
// ini_set('display_errors', 1);
// error_reporting(-1);
            $error ='';	
           // if( ! wp_verify_nonce( 'saab_front_nonce' ) ){}
			$booking_date = ( isset( $_POST['booking_date'] ) ) ? $_POST['booking_date'] : '';
			$explode_booking_date = explode('_',$booking_date);
			$form_id = $explode_booking_date[1];
			$format_bookingdate = $explode_booking_date[4] . "-" . $explode_booking_date[2] . "-" . $explode_booking_date[3];
			$converted_bookingdate = date('Y-m-d', strtotime($format_bookingdate)); 
			$timeslot = ( isset( $_POST['timeslot'] ) ) ? $_POST['timeslot'] : '';
			//total availableseats
			$slotcapacity = ( isset( $_POST['slotcapacity'] ) ) ? $_POST['slotcapacity'] : '';
			//quantity
			$bookedseats = ( isset( $_POST['bookedseats'] ) )? $_POST['bookedseats'] : '' ;
			$form_id = isset($_POST['fid']) ? absint($_POST['fid']) : 0;
			$form_data = isset( $_POST['form_data'] ) ? $_POST['form_data']:'';
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
			$pid =  get_option('tot_manage_entries');
				update_option('tot_manage_entries', $pid);
			$slug = sanitize_title($new_post_title);
			wp_update_post(array('ID' => $created_post_id, 'post_name' => $slug));

			// Update the post's permalink (optional)
			$appointment_type = get_post_meta($form_id, 'saab_appointment_type', true);
			$check_waiting = get_post_meta($form_id, 'saab_waiting_list', true);
			$prefixlabel = get_post_meta( $form_id, 'saab_label_symbol', true );
			$cost = get_post_meta( $form_id, 'saab_cost', true );
			$enable_auto_approve = get_post_meta($form_id, 'saab_enable_auto_approve', true);
			$new_post_permalink = get_permalink($created_post_id);
			$seats_per_timeslot =  get_post_meta($form_id, 'saab_slotcapacity',true);
			update_post_meta($created_post_id, 'saab_timeslot', $timeslot);
			update_post_meta($created_post_id, 'saab_booking_date', $booking_date);
			update_post_meta($created_post_id, '_wp_old_slug', $new_post_permalink);
			update_post_meta($created_post_id, 'saab_submission_data', $form_data);
			update_post_meta($created_post_id, 'saab_slotcapacity', $bookedseats);
			update_post_meta($created_post_id, 'saab_cost', $cost);
			update_post_meta($created_post_id, 'saab_form_id', $form_id);
			update_post_meta($created_post_id, 'saab_label_symbol', $prefixlabel);
			update_post_meta($created_post_id, 'saab_appointment_type', $appointment_type);
			
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
			$submission_key_val = array();
			if (isset($form_data['data']) && is_array($form_data['data'])) {
				foreach ($form_data['data'] as $form_key => $form_value) {
					if ($form_key !== 'submit') {
						$submission_key_val[$form_key] = esc_attr($form_value);
					}
				}
			
             }

			 $explode_timeslot = explode('-',$timeslot);
			 $get_user_mapping = get_post_meta($form_id, 'saab_user_mapping', true);

			 $first_name = $last_name = $emailTo = $service = $timeslot = '';
			 
			 // Ensure that $form_data['data'] is an array
			 if (isset($form_data['data']) && is_array($form_data['data'])) {
				 
				 $getfirst_name = isset($get_user_mapping['first_name']) ? sanitize_text_field($get_user_mapping['first_name']) : '';
				 if ($getfirst_name && isset($form_data['data'][$getfirst_name])) {
					 $first_name = sanitize_text_field($form_data['data'][$getfirst_name]);
				 }
			 
				 $getlast_name = isset($get_user_mapping['last_name']) ? sanitize_text_field($get_user_mapping['last_name']) : '';
				 if ($getlast_name && isset($form_data['data'][$getlast_name])) {
					 $last_name = sanitize_text_field($form_data['data'][$getlast_name]);
				 }
			 
				 $getemail = isset($get_user_mapping['email']) ? sanitize_text_field($get_user_mapping['email']) : '';
				 if ($getemail && isset($form_data['data'][$getemail])) {
					 $emailTo = sanitize_email($form_data['data'][$getemail]);
				 }
			 
				 $getservice = isset($get_user_mapping['service']) ? sanitize_text_field($get_user_mapping['service']) : '';
				 if ($getservice && isset($form_data['data'][$getservice])) {
					 $service = ucfirst(sanitize_text_field($form_data['data'][$getservice]));
				 }

				 $gettimeslot = isset($get_user_mapping['timeslot']) ? sanitize_text_field($get_user_mapping['timeslot']) : '';
				 if ($gettimeslot && isset($form_data['data'][$gettimeslot])) {
					 $timeslot = sanitize_text_field($form_data['data'][$gettimeslot]);
				 }
			 
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
			$bookingdate = get_the_date( 'M d,Y', $form_id );
			$FormTitle = get_the_title( $form_id );
			$other_label_val = array(
				'FormId' => $form_id,
				'BookingId' => $created_post_id,
				'FormTitle' => $FormTitle,
				'To' => $emailTo,
				'FirstName' => $first_name,
				'LastName' => $last_name,
				'Timeslot' => $timeslot,
				'BookingDate' => $bookingdate,
				//'BookedDate' =>$converted_bookingdate,
				'slotcapacity' => $slotcapacity,
				//'StartTime' => $explode_timeslot[0],
				//'EndTime' => $explode_timeslot[1],
				'BookingSeats' => '',
				'BookedDate' =>'',					
				'Service' => $service,
				'prefixlabel' => $prefixlabel,
				'cost' => $cost,					
				//'slotcapacity' => '',
				'bookedseats' => '',	
				'form_data' => $form_data,
				'no_of_seats' => '',
				'tot_no_of_seats' => '',
				'StartTime' => '',
				'EndTime' => '',
				'CancelBooking' => '',
			);
			//$status = $enable_auto_approve === 'on' ? 'approved' : 'pending';

				$status = 'submitted';
				update_post_meta($created_post_id, 'saab_entry_status', $status);
				$listform_label_val = array_merge($submission_key_val, $other_label_val);
				update_post_meta($created_post_id, 'saab_listform_label_val', $listform_label_val);
				$mail_response = array(
					'status'=>$status,
				);
				update_post_meta($created_post_id, 'saab_mail_response', $mail_response);
				$confirmation = get_post_meta($form_id, 'saab_confirmation', true);
				$redirect_url = '';
				
				if ($confirmation == 'redirect_text') {
					$wp_editor_value = get_post_meta($form_id, 'saab_redirect_text', true);					
				    //$editor_value = wpautop(wp_kses_post($wp_editor_value));
					$shortcodesArray = $this->front_get_shortcodes($form_id);
					$editor_check_shortcodes = $this->saab_check_shortcodes_exist_in_editor($wp_editor_value, $form_id, $listform_label_val, $shortcodesArray);
				} elseif ($confirmation == 'redirect_page') {
					$redirect_page = get_post_meta($form_id, 'saab_redirect_page', true);
					$redirect_url = get_permalink($redirect_page);
				} elseif ($confirmation == 'redirect_to') {
					$redirect_url = get_post_meta($form_id, 'saab_redirect_url', true);
				}
				
				wp_send_json_success(array(					
					'message' => 'Sucessfully Submitted',
					'redirect_page' => $redirect_url,
					'wp_editor_value' => $editor_check_shortcodes,
					'redirect_url' => $redirect_url,
					'mail_response' =>$mail_response,
					'confirmation' => $confirmation,
					'status' => $status,
					'fpid'=>$form_id.'T'.$created_post_id,
			
				));

			wp_die();
		}
		function saab_add_event_to_calender(){

			ob_start();

			if(isset($_GET['code'])){ 

				require_once SAAB_DIR . '/inc/lib/google-library/vendor/autoload.php';				
				$stateParameter = ( isset( $_GET['state'] ) ) ? $_GET['state'] : '';
				$mystate = explode('T', $stateParameter);
				$form_id = $mystate[0];
				$post_id = $mystate[1];
				$timezone = get_post_meta($form_id,'saab_timezone',true);

				$timeslot = get_post_meta($post_id,'saab_timeslot',true);
				$explode_timeslot = explode('-',$timeslot);
				$startTime = date("H:i:s", strtotime(str_replace(' ', '', $explode_timeslot[0]))); //phpcs:ignore
				$s_hour = date("H", strtotime($startTime)); //phpcs:ignore
				$s_minute = date("i", strtotime($startTime)); //phpcs:ignore
				$s_second = date("s", strtotime($startTime)); //phpcs:ignore

				$endTime = date("H:i:s", strtotime(str_replace(' ', '', $explode_timeslot[1]))); //phpcs:ignore
				$e_hour = date("H", strtotime($endTime)); //phpcs:ignore
				$e_minute = date("i", strtotime($endTime)); //phpcs:ignore
				$e_second = date("s", strtotime($endTime));	 //phpcs:ignore

				$booking_date = get_post_meta($post_id,'saab_booking_date',true);
				$parts = explode("_", $booking_date);
				$postId = $parts[1];
				$day = $parts[3];
				$month = $parts[2];
				$year = $parts[4];
				
				$dateTime_stevent_n = sprintf("%04d-%02d-%02dT%02d:%02d:%02d", $year, $month, $day, $s_hour, $s_minute, $s_second);
				$dateTime_stevent = str_replace(' ', '', $dateTime_stevent_n);
				$dateTime_etevent_n = sprintf("%04d-%02d-%02dT%02d:%02d:%02d", $year, $month, $day, $e_hour, $e_minute, $e_second);
				
				$client_new = new Google_Client();
				$client_new->setApplicationName('My Calendar Event');
				$client_new->setScopes([
					Google_Service_Calendar::CALENDAR,
					Google_Service_Calendar::CALENDAR_EVENTS,
				]);
				$get_client_id = get_option('saab_client_id', true);
				$get_secret_id = get_option('saab_secret_id', true);
				$get_redirect_uri = get_option('saab_redirect_uri', true);
		
				$client_new->setClientId($get_client_id);
				$client_new->setClientSecret($get_secret_id);
				$client_new->setRedirectUri($get_redirect_uri);	

				$client_new->setAccessType('offline');

				if (isset($_GET['code'])) {

					$token = $client_new->fetchAccessTokenWithAuthCode($_GET['code']);   
					$client_new->setAccessToken($token);    
					$service = new Google_Service_Calendar($client_new);
					
					$calendarId = 'primary'; 
					$calendarList = $service->calendarList->listCalendarList();
					
					if (!empty($calendarList->getItems())) {
						$userTimezone = $calendarList->getItems()[0]->getTimeZone();
						
						$eventStart = new DateTime($dateTime_stevent_n, new DateTimeZone($timezone));
						$eventEnd = new DateTime($dateTime_etevent_n, new DateTimeZone($timezone));
						
						$eventStart->setTimezone(new DateTimeZone($userTimezone));
						$eventEnd->setTimezone(new DateTimeZone($userTimezone));
						$title = get_post_meta($form_id, 'saab_cal_title', true);
						$description = get_post_meta($form_id, 'saab_cal_description', true);
						$description .= $startTime.'-'.$endTime;
						$event = new Google_Service_Calendar_Event(array(
							'summary' => $title,
    						'description' => $description,
							'start' => array(
								'dateTime' => $eventStart->format('Y-m-d\TH:i:s'),
								'timeZone' => $userTimezone,
							),
							'end' => array(
								'dateTime' => $eventEnd->format('Y-m-d\TH:i:s'),
								'timeZone' => $userTimezone,
							),
						));
						
						$calendarId = 'primary'; 
						$event = $service->events->insert($calendarId, $event);
						$message = 'Event has been added to the calendar!';
							echo '<div id="calendar-insertmsg">' . esc_html( $message ) . '</div>';
							echo '<script>
								jQuery(document).ready(function() {
									// Show the message
									jQuery("#calendar-insertmsg").fadeIn();
									
									// After 3 seconds, fade it out
									setTimeout(function() {
										jQuery("#calendar-insertmsg").fadeOut();
									}, 3000); // 3000 milliseconds = 3 seconds
								});
							</script>';
		
					} else {
						$error = 'true';
						$err_message = "Unable to access user's calendar";
						if( defined('WP_DEBUG') && true === WP_DEBUG ){
							error_log("Unable to access user's calendar"); //phpcs:ignore
						}
					}					
				}else{
					$error = 'true';
					$err_message = __("Not Able To Authorize Google Calender","smart-appointment-booking");
					if( defined('WP_DEBUG') && true === WP_DEBUG ){
						error_log("Error In accessing code value, check you client id and secret Id"); //phpcs:ignore
					}
				}
			}
			return ob_get_clean();
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
		function saab_send_notification($status, $form_id, $post_id, $form_data) {
			// Sanitize the status value from $_POST, if applicable
			$status = (isset($_POST['status']) && !empty($_POST['status'])) ? sanitize_text_field($_POST['status']) : $status;
			
			// Log status to ensure it's being received correctly
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log('Status received: ' . $status);
			}
		
			$message = '';
			$notificationFound = false;
		
			// Get notification data
			$get_notification_array = get_post_meta($form_id, 'saab_notification_data', true);
			
			// Log the retrieved notification data for debugging
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log('Notification array: ' . print_r($get_notification_array, true));
			}
		
			// Check if the notification data exists and is an array
			if ($get_notification_array && is_array($get_notification_array)) {
				foreach ($get_notification_array as $notification) {
					// Ensure notification is enabled and matches the status
					if ($notification['state'] === 'enabled' && $notification['type'] === $status) {
						$notificationFound = true; // Mark notification as found
		
						// Log notification for debugging
						if (defined('WP_DEBUG') && WP_DEBUG) {
							error_log('Notification found: ' . print_r($notification, true));
						}
						
						$check_to = $notification['to'];
						$check_replyto = $notification['replyto'];
						$check_bcc = $notification['bcc'];
						$check_cc = $notification['cc'];
						$check_from = $notification['from'];
						$subject = $notification['subject'];
						$check_body = $notification['mail_body'];
		
						// Retrieve shortcodes for replacements
						$shortcodesArray = $this->front_get_shortcodes($form_id);
		
						// Replace shortcodes with actual data
						$to = $this->saab_check_shortcode_exist($check_to, $form_id, $form_data, $shortcodesArray);
						$from = $this->saab_check_shortcode_exist($check_from, $form_id, $form_data, $shortcodesArray);
						$replyto = $this->saab_check_shortcode_exist($check_replyto, $form_id, $form_data, $shortcodesArray);
						$bcc = $this->saab_check_shortcode_exist($check_bcc, $form_id, $form_data, $shortcodesArray);
						$cc = $this->saab_check_shortcode_exist($check_cc, $form_id, $form_data, $shortcodesArray);
						$check_body = $this->saab_check_shortcodes_exist_in_editor($check_body, $form_id, $form_data, $shortcodesArray);
		
						// Log email details for debugging
						if (defined('WP_DEBUG') && WP_DEBUG) {
							error_log('Email details: to: ' . $to . ', from: ' . $from . ', subject: ' . $subject . ', body: ' . $check_body);
						}
		
						// Set email headers
						$headers = array(
							'From: ' . sanitize_email($from),
							'Reply-To: ' . sanitize_email($replyto),
							'Bcc: ' . sanitize_email($bcc),
							'Cc: ' . sanitize_email($cc)
						);
		
						// Add HTML or plain text content type based on settings
						if (isset($notification['use_html']) && $notification['use_html'] == 1) {
							$headers[] = 'Content-Type: text/html; charset=UTF-8';
						} else {
							$headers[] = 'Content-Type: text/plain; charset=UTF-8';
						}
		
						// Attempt to send the email
						$result = wp_mail($to, sanitize_text_field($subject), $check_body, $headers);
		
						if ($result) {
							$message = esc_html__('Email sent successfully', 'smart-appointment-booking');
						} else {
							$message = esc_html__('Failed to send email', 'smart-appointment-booking');
							if (defined('WP_DEBUG') && WP_DEBUG) {
								error_log('Failed to send email to: ' . $to); // Debug logging
							}
						}
					}
				}
			} else {
				// Log an error if no notification data was found for the form
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log('No notification data found for form ID: ' . $form_id);
				}
			}
		
			// If no notification was found, log an error
			if ($notificationFound === false) {
				$message = __('Notification not found for the given status: ' . $status, 'smart-appointment-booking');
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log('Notification not found for the given status: ' . $status); // Debug logging
				}
				wp_send_json_error(array('message' => $message));
				wp_die();
			}
		
			// Send success response if email was sent
			wp_send_json_success(array('message' => $message));
			wp_die();
		}
		
		
		
		function saab_send_post_update_notification($status, $form_id, $post_id, $form_data) {
			// Sanitize status and other input data
			$status = (isset($_POST['status']) && !empty($_POST['status'])) ? sanitize_text_field($_POST['status']) : sanitize_text_field($status);
			$message = '';
		
			// Get notification data from post meta
			$get_notification_array = get_post_meta($form_id, 'saab_notification_data', true);
		
			$notificationFound = false;
		
			// Check if notification array is not empty
			if (!empty($get_notification_array) && is_array($get_notification_array)) {
				foreach ($get_notification_array as $notification) {
		
					// Check if notification is enabled and matches the given status
					if (isset($notification['state'], $notification['type']) && $notification['state'] === 'enabled' && $notification['type'] === $status) {
						$notificationFound = true;
		
						// Assign variables from the notification
						$check_to = $notification['to'];
						$check_replyto = $notification['replyto'];
						$check_bcc = $notification['bcc'];
						$check_cc = $notification['cc'];
						$check_from = $notification['from'];
						$subject = $notification['subject'];
						$check_body = $notification['mail_body'];
		
						// Get shortcodes for replacements
						$shortcodesArray = $this->front_get_shortcodes($form_id);
		
						// Replace shortcodes with actual form data
						$to = $this->saab_check_shortcode_exist($check_to, $form_id, $form_data, $shortcodesArray);
						$from = $this->saab_check_shortcode_exist($check_from, $form_id, $form_data, $shortcodesArray);
						$replyto = $this->saab_check_shortcode_exist($check_replyto, $form_id, $form_data, $shortcodesArray);
						$bcc = $this->saab_check_shortcode_exist($check_bcc, $form_id, $form_data, $shortcodesArray);
						$cc = $this->saab_check_shortcode_exist($check_cc, $form_id, $form_data, $shortcodesArray);
						$check_body = $this->saab_check_shortcodes_exist_in_editor($check_body, $form_id, $form_data, $shortcodesArray);
		
						// Build email headers
						$headers = array(
							'From: ' . sanitize_email($from),
							'Reply-To: ' . sanitize_email($replyto),
							'Bcc: ' . sanitize_email($bcc),
							'Cc: ' . sanitize_email($cc)
						);
		
						// Check if the notification uses HTML email format
						if (isset($notification['use_html']) && $notification['use_html'] == 1) {
							$headers[] = 'Content-Type: text/html; charset=UTF-8';
						} else {
							$headers[] = 'Content-Type: text/plain; charset=UTF-8';
						}
		
						// Send the email
						$result = wp_mail($to, sanitize_text_field($subject), $check_body, $headers);
		
						if ($result) {
							$message = __('Email sent successfully', 'smart-appointment-booking');
						} else {
							// Log details if email sending fails
							$message = __('Failed to send email. Details: to-' . $to . ', from-' . $from . ', Bcc-' . $bcc . ', Cc-' . $cc . ', subject-' . $subject . ', body-' . $check_body . ', headers-' . json_encode($headers), 'smart-appointment-booking');
							if (defined('WP_DEBUG') && WP_DEBUG) {
								error_log('Failed to send email. Details: to-' . $to . ', from-' . $from . ', Bcc-' . $bcc . ', Cc-' . $cc . ', subject-' . $subject . ', body-' . $check_body . ', headers-' . json_encode($headers));
							}
						}
					}
				}
			}
		
			// Handle case where no matching notification is found
			if ($notificationFound === false) {
				$message = __('Notification not found for the given status: ' . $status, 'smart-appointment-booking');
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log('Notification not found for the given status: ' . $status);
				}
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
				if($advancedata){
					foreach ($advancedata as $index => $data) {
						
						if (!in_array($data['advance_date'], $holiday_dates)) {
							$arrayof_advdates[] = $data['advance_date'];
						}
						
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
							if($certain_weekdays_array){
								foreach ($certain_weekdays_array as $wdays) {
									$weekdays_num[] = gmdate('N', strtotime($wdays));
								}
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
				if($advancedata){
					foreach ($advancedata as $item) {
						$advanceDates[] = $item['advance_date'];
					}
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
								$available_text = esc_html(__('Available seats: ', 'smart-appointment-booking')) . esc_attr($available_seats);

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

							$output_timeslot .= '<span>'.$start_timeslot.' - ' . $end_timeslot.'</span>';
							$output_timeslot .= '<input class="saab-selected-time" name="booking_slots"  type="hidden" value="'.$start_timeslot."-".$end_timeslot.'">';					
							$output_timeslot .= '<span class="saab-tooltip-text" data-seats="'.$available_input_seats.'" >'.$available_text.'<br>'.$waiting_text.'</span>';
							$output_timeslot .= '<span class="saab-waiting" style="display:none;" class="hidden" data-checkdate="'.$check_date.'" data-waiting="'.$iswaiting_alllowed.'" data-seats="'.$waiting_seats.'">'.$iswaiting_alllowed.'</span>';
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
// 			ini_set('display_startup_errors', 1);
// ini_set('display_errors', 1);
// error_reporting(-1);	
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
				$this_start_time = '';
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
					
					$output_timeslot .= '<span>'.$start_time_slot.' - ' . $end_time_slot.'</span>';
					$output_timeslot .= '<input class="saab-selected-time" name="booking_slots" data-startime="'.$this_start_time.'"  type="hidden" value="'.$start_time_slot."-".$end_time_slot.'">';					
					$output_timeslot .= '<span class="saab-tooltip-text" data-seats="'.$available_input_seats.'" > '.$available_text.'<br>'.$waiting_text.'</span>';
					$output_timeslot .= '<span class="saab-waiting" style="display:none;" class="hidden" data-checkdate="'.$check_date.'" data-waiting="'.$iswaiting_alllowed.'" data-seats="'.$waiting_seats.'" >'.$iswaiting_alllowed.'</span>';
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
// 			ini_set('display_startup_errors', 1);
// ini_set('display_errors', 1);
// error_reporting(-1);
			ob_start();				
			$post_id = $attr['form_id'];	
			$enable_booking = get_post_meta($post_id, 'saab_enable_booking', true);
			$prefix_label = get_post_meta($post_id, 'saab_label_symbol', true);
			$cost = get_post_meta($post_id, 'saab_cost', true);
			
			if(isset($enable_booking) && !empty($enable_booking)){	
				$cal_title = get_post_meta($post_id, 'saab_cal_title', true);
				$cal_description = get_post_meta($post_id, 'saab_cal_description', true);
				$currentMonth = gmdate('m');
				$currentYear = gmdate('Y');
				$cal_location = get_post_meta($post_id, 'saab_cal_location', true);
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
										echo ">" . esc_html($monthNames[$i]) . "</option>";
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
											$isToday = ($date == date('j') && $monthYear == date('n-Y')) ? "calselected_date" : ""; //phpcs:ignore
											if ($isToday === "calselected_date") {
												$lastdateid = 'saabid_' . $post_id . '_' . $currentMonth . '_' . $date . '_' . $currentYear;
												$lastday = $date;
												$lastmonth = $currentMonth;
												$lastyear = $currentYear;
											}
											echo "<td id='saabid_" . esc_attr( $post_id ) . '_' . esc_attr( $currentMonth ) . "_" . esc_attr( $date ) . "_" . esc_attr( $currentYear ) . "' data_day='saabid_" . esc_attr( $post_id ) . '_' . esc_attr( $currentMonth ) . "_" . esc_attr( $date ) . "_" . esc_attr( $currentYear ) . "' class='saab_cal_day " . esc_attr( $isToday ) . "' onclick='getClickedId(this)'>" . esc_html( $date ) ."</td>";
											$date++;
										} elseif ($dayCounter < $firstDayOfWeek) {
											$prevDate = $daysInPreviousMonth - ($firstDayOfWeek - $dayCounter) + 1;
											echo "<td class='previous-month'>" . esc_html( $prevDate ) . '</td>';
										} else {
											$nextDate = $dayCounter - ($totalDays + $firstDayOfWeek) + 1;
											echo "<td class='next-month'>" . esc_html( $nextDate ) . '</td>';
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
							$TodaysDate = date('F d, Y');	
							$todaysDate = date('Y-m-d');
							echo "<h3 id='head_avail_time'><span class='gfb-timezone'>Timezone: " . esc_attr($timezone) . "</span></h3>";
							echo "<h4 id='headtodays_date'>" . esc_html($TodaysDate) . "</h4>";								
							// Get array of available dates 
							$is_available = $this->saab_processDate($post_id,$todaysDate);							
							?>
							<ul id='saab-slot-list'>
								<?php		
								if(isset($is_available) && is_array($is_available) && in_array($todaysDate,$is_available)){
									$check_type = get_post_meta($post_id, 'saab_enable_recurring_apt', true);
									
									$enable_advance_setting = get_post_meta($post_id, 'saab_enable_advance_setting', true);
									if($enable_advance_setting && !empty($enable_advance_setting)){
										
										$advancedata = get_post_meta($post_id, 'saab_advancedata', true);
										$advanceDates = array();
										if( $advancedata ){
											foreach ($advancedata as $item) {
												$advanceDates[] = $item['advance_date'];
											}
										}

										if(in_array($todaysDate,$advanceDates)){
											echo $this->saab_get_advanced_timeslots($post_id,$lastdateid,$todaysDate); //phpcs:ignore
										}else{
											echo $this->saab_front_generate_timeslots($post_id,$lastdateid); //phpcs:ignore               
										}     
									}else{
										echo $this->saab_front_generate_timeslots($post_id,$lastdateid); //phpcs:ignore
									}   
								} else {
									
									$error = true;
									if( defined('WP_DEBUG') && true === WP_DEBUG ){ 
										error_log('Check End date! Selected date exceed the selected end date'); //phpcs:ignore
									}
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
							<span class="saab-cost">Cost: <?php echo esc_attr($prefix_label) . ' ' . esc_attr($cost); ?></span>
						</div>
						<!-- <input type="hidden" name="nonce" value="<//?php //echo esc_attr(wp_create_nonce('booking_form_nonce')); ?>"> -->
						<input type="hidden" id="booking_date" name="booking_date" value="<?php echo esc_attr($lastdateid); ?>" name="booking_date" >
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
							<div id="formio_res_msg"></div>
							<?php
							// Checking if booking is enabled
							if (isset($enable_booking) && !empty($enable_booking)) {
								?>
								<script type='text/javascript'>
									var myScriptData = <?php echo wp_kses_post($fields); ?>;
									var value = myScriptData;
									Formio.createForm(document.getElementById('formio'), {
										components: value
									}).then(function (form) {
										var isSubmitting = false; // Flag variable to track form submission
										form.on('submit', function (submission) {
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
				
											jQuery('.saab_timeslot').each(function () {
												if (jQuery(this).hasClass('selected')) {
													timeslot = jQuery(this).find('input[name="booking_slots"]').val();
													slotcapacity = jQuery(this).find('.saab-tooltip-text').attr('data-seats');
												}
											});
											bookedseats = jQuery('input[name="saabslotcapacity"]').val();
											jQuery.ajax({
												url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
												type: 'post',
												data: {
													action: "saab_booking_form_submission",
													form_data: submission,
													fid: formid,
													timeslot: timeslot,
													booking_date: booking_date,
													bookedseats: bookedseats,
													slotcapacity: slotcapacity,
													nonce: nonce,
												},
												success: function (response) {
													handleFormResponse(response);
												}
											});
											return false;
										});
									});
								</script>
								<?php
							} else {
								// When booking is disabled
								?>
								<script type='text/javascript'>
									var myScriptData = <?php echo wp_kses_post($fields); ?>;
									var value = myScriptData;
				
									Formio.createForm(document.getElementById('formio'), {
										components: value
									}).then(function (form) {
										var isSubmitting = false;
										form.on('submit', function (submission) {
											event.preventDefault();
											if (isSubmitting) {
												jQuery('#formio_res_msg').html("Already Submitted!").fadeIn().delay(1000).fadeOut();
												return;
											}
											isSubmitting = true;
											var formid = <?php echo wp_json_encode($post_id); ?>;
											var nonce = myAjax.nonce;
				
											jQuery.ajax({
												url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
												type: 'post',
												data: {
													action: "saab_save_form_submission",
													form_data: submission,
													fid: formid,
													nonce: nonce,
												},
												success: function (response) {
													handleFormResponse(response);
												}
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
				
				// JavaScript function to handle the response for both scenarios
			
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
			//if( ! wp_verify_nonce( 'saab_front_nonce' ) ){} // ignoring nonce validation error in the front form
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
				$currentMonth = isset($_POST['currentMonth']) ? intval($_POST['currentMonth']) : date('n'); //phpcs:ignore
				$currentMonth = max(1, min(12, $currentMonth)); // Ensure currentMonth is between 1 and 12
				$currentYear = isset($_POST['currentYear']) ? intval($_POST['currentYear']) : date('Y'); //phpcs:ignore
				$post_id = isset($_POST['form_id']) ? $_POST['form_id'] : '';
				$running_year = date("Y"); //phpcs:ignore
				ob_start();
			?>
			<div class="header-calender">
				<input type="hidden" id="zealform_id" value="<?php echo esc_attr( $post_id ); ?>" >
                <span class="arrow" id="prev-month" onclick="getClicked_prev(this)">&larr;</span>
                <select name='saab_month_n' id='saab_month'>
                    <?php
                    for ($i = 1; $i <= 12; $i++) {
                        echo "<option value='" . esc_attr( $i ) . "'";
                        if ($i == $currentMonth) {
                            echo " selected";
                        }
                        echo ">" . esc_html( $monthNames[$i] ) . "</option>";
                    }
                    ?>
                </select>
			
				<select name="saab_year_n" id="saab_year">
					<?php
					$futureYear = date("Y", strtotime("+10 years", strtotime("January 1, $currentYear"))); //phpcs:ignore
					echo '<optgroup label="Current Year">';
					echo "<option value='" . esc_attr( $running_year ) . "'>" . esc_html( $running_year ) . "</option>";
					echo '</optgroup>';
					echo '<optgroup label="Years">';
					for ($year = $futureYear; $year >= $currentYear; $year--) {
						echo "<option value='" . esc_attr( $year ) . "'";
						if ($year == $currentYear) {
							echo " selected";
						}
						echo '>' . esc_html( $year ) . '</option>';
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
                $firstDayOfWeek = date('N', strtotime($currentYear . '-' . $currentMonth . '-01')); //phpcs:ignore
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
                             echo "<td  id='saabid_" . esc_attr( $post_id ) . '_' . esc_attr( $currentMonth ) . "_" . esc_attr( $date ) . "_" . esc_attr( $currentYear ) . "' data_day='saabid_" . esc_attr( $post_id ) . '_' . esc_attr( $currentMonth ) . "_" . esc_attr( $date ) . "_" .  esc_attr( $currentYear ) . "' class='saab_cal_day' onclick='getClickedId(this)'>" . esc_html( $date ) . "</td>";
                            $date++;
                        }
						elseif ($dayCounter < $firstDayOfWeek) {
                            $prevDate = $daysInPreviousMonth - ($firstDayOfWeek - $dayCounter - 1);
                            echo "<td class='previous-month'>" . esc_html( $prevDate ) . "</td>";
                        }
						else {
                            $nextDate = $dayCounter - ($totalDays + $firstDayOfWeek) + 1;
                            echo "<td class='next-month'>" . esc_html( $nextDate ) . "</td>";
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
			echo $output; //phpcs:ignore
			wp_die();
		  }
		/*** 
		 * Display Timeslots on booking calendar
		 */
		  function saab_action_display_available_timeslots(){
			ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);		
				//if( ! wp_verify_nonce( 'saab_front_nonce' ) ){}
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
				$todaysDate = date('Y-m-d', strtotime("$current_year-$current_month-$current_day")); //phpcs:ignore
				$TodaysDate_F = date('F d, Y', strtotime("$current_year-$current_month-$current_day")); //phpcs:ignore
				$currDate = date('Y-m-d'); //phpcs:ignore
				$timezone = get_post_meta($post_id,'saab_timezone',true);
				echo "<h3 id='head_avail_time'><span class='gfb-timezone'>Timezone: " . esc_html( $timezone ) . "</span></h3>";
				echo "<h4 id='headtodays_date'>" . esc_html( $TodaysDate_F ) . "</h4>";
				echo '<input type="hidden" id="zeallastdate" name="zeallastdate" value="' . esc_attr( $clickedId ) . '" >';
				if($todaysDate < $currDate ){
					// echo $todaysDate ."--" .$currDate; 
					$error = true;

				}else{
						echo "<ul id='saab-slot-list'>";
					
							$is_available = $this->saab_processDate($post_id,$todaysDate);
							// print_r($is_available);
							// echo $todaysDate;
							if (isset($is_available) && is_array($is_available)) {

								if(isset($is_available) && is_array($is_available) && in_array($todaysDate,$is_available)){
									$check_type = get_post_meta($post_id, 'saab_enable_recurring_apt', true);
									$enable_advance_setting = get_post_meta($post_id, 'saab_enable_advance_setting', true);
									
									// if($enable_advance_setting && !empty($enable_advance_setting)){
										$advancedata = get_post_meta($post_id, 'saab_advancedata', true);
										$advanceDates = array();
										if ( ! empty( $advancedata ) ) {
											foreach ($advancedata as $item) {
												$advanceDates[] = $item['advance_date'];
											}
										}
										if(in_array($todaysDate,$advanceDates)){
											echo $this->saab_get_advanced_timeslots($post_id,$form_data,$todaysDate); //phpcs:ignore
										}else{
											echo $this->saab_front_generate_timeslots($post_id,$form_data); //phpcs:ignore            
										}
									
								}else {
									$error = true;
									if( defined('WP_DEBUG') && true === WP_DEBUG ){ 
										error_log('Check End date! Selected date exceed the selected end date'); //phpcs:ignore
									}
								}
							} else {
								$error = true;
								if( defined('WP_DEBUG') && true === WP_DEBUG ){ 
									error_log('Array does not exist.'); //phpcs:ignore
								}
							}	
						// }
						
					echo "</ul>";
				}
				
				if($error === true){
					echo esc_html__('No timeslots found for selected date. ','smart-appointment-booking');
				}else{
					echo '<input class="saab-selected-capacity" type="number" name="saabslotcapacity" placeholder="Enter Slot Capacity" min="1" value="1">';
					echo '<p id="no-timeslots-message" style="display: none;">No Timeslots found!</p>';
				}
				wp_die();
				
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