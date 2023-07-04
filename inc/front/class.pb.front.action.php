<?php
/**
 * PB_Front_Action Class
 *
 * Handles the Frontend Actions.
 *
 * @package WordPress
 * @subpackage Plugin name
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PB_Front_Action' ) ){

	/**
	 *  The PB_Front_Action Class
	 */
	class PB_Front_Action {

		function __construct()  {

		
			add_action('wp_ajax_bms_front_save_post_meta', array( $this, 'bms_front_save_post_meta' ) );
			add_action('wp_ajax_nopriv_bms_front_save_post_meta', array( $this, 'bms_front_save_post_meta' ) );

			add_action('wp_ajax_action_reload_calender', array( $this, 'action_reload_calender' ) );
			add_action('wp_ajax_nopriv_action_reload_calender', array( $this, 'action_reload_calender' ) );

			//on click of any date
			add_action('wp_ajax_action_display_available_timeslots', array( $this, 'action_display_available_timeslots' ) );
			add_action('wp_ajax_nopriv_action_display_available_timeslots', array( $this, 'action_display_available_timeslots' ) );

			add_action( 'wp_enqueue_scripts',  array( $this, 'action__enqueue_styles' ));
			add_action( 'wp_enqueue_scripts', array( $this, 'action__wp_enqueue_scripts' ));

			add_shortcode('booking_form',array( $this, 'zealbms_get_booking_form' ));

			add_action('wp_ajax_bms_zfb_cancel_booking', array( $this, 'zfb_cancel_booking' ) );
			add_action('wp_ajax_nopriv_zfb_cancel_booking', array( $this, 'zfb_cancel_booking' ) );

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
				wp_enqueue_script( PB_PREFIX . '_bookingform', PB_URL . 'assets/js/booking-form.js', array( 'jquery-core' ), PB_VERSION );
			}
			
			wp_enqueue_script( PB_PREFIX . '_front', PB_URL . 'assets/js/front.js', array( 'jquery-core' ), PB_VERSION );
			// wp_enqueue_script( PB_PREFIX . '_front_js', PB_URL . 'assets/js/front.min.js', array( 'jquery-core' ), PB_VERSION );
			wp_enqueue_script( 'bms_formio_full_min', PB_URL.'assets/js/formio.full.min.js', array( 'jquery' ), 1.1, false );
			wp_localize_script('bms_formio_full_min', 'myAjax', array(
				'ajaxurl' => admin_url('admin-ajax.php')
			));
			wp_enqueue_script( 'bms_bootstrap.min', PB_URL.'assets/js/bootstrap.min.js', array( 'jquery' ), 1.1, false );
			wp_enqueue_script( 'bms_jquery-3.7.0.slim.min', PB_URL.'assets/js/jquery-3.7.0.slim.min.js', array( 'jquery' ), 1.1, false );
			wp_enqueue_script( 'bms_jquery-3.7.0.min',PB_URL.'assets/js/jquery-3.7.0.min.js', array( 'jquery' ), 1.1, false );
		}
		function action__enqueue_styles() {

			wp_enqueue_style( 'bms_front',PB_URL.'assets/css/front.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'bms_boostrap_min',PB_URL.'assets/css/bootstrap.min.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'bms_formio_full_min',PB_URL.'assets/css/formio.full.min.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'bms_font-awesomev1','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', array(), 1.1, 'all' );
				
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
		
		function zealbms_get_booking_form_test() {
			ob_start();
			?>
			<p id="clickajax">click</p>
			<?php
			
			
			return ob_get_clean();
		}
		function bms_front_save_post_meta() {
			$error = '';
			$timeslot = $_POST['timeslot'];
			$booking_date = $_POST['booking_date'];
			// $totalbookings = $_POST['totalbookings'];
			$slotcapacity = $_POST['slotcapacity'];
			$bookedseats = $_POST['bookedseats'];
			$form_id = $_POST['fid'];
			$FormTitle = get_the_title($form_id);
			$form_data = $_POST['form_data'];
			// 	print_r($form_data['data']);
			$enable_auto_approve = get_post_meta($form_id, 'enable_auto_approve', true);
			$check_waiting = get_post_meta($form_id, 'waiting_list', true);
			$no_of_seats = $this->get_available_seats_per_timeslot($timeslot, $booking_date);
			// $tot_no_of_seats =  $slotcapacity - $no_of_seats;
			$tot_no_of_seats = $slotcapacity - $bookedseats;
			$waiting_list = 'false';
			
			if ($tot_no_of_seats < 0 || $no_of_seats === 'not_available' || $tot_no_of_seats === 0) {
				if ($check_waiting) {
					$register_booking = 'true';
					$waiting_list = 'true';
				} else {
					$register_booking = 'false';
					$error = 'No available seats';
				}
			} else {
				$register_booking = 'true';
			}
		
			if ($register_booking === 'true') {
				
				$pid = get_option('tot_bms_entries');
				if (empty($pid)) {
					$pid = 1;
				} else {
					$pid++;
				}
				$new_post = array(
					'post_title'   => 'booking_#' . $pid,
					'post_type'    => 'bms_entries',
					'post_status'  => 'publish'
				);
		
				$created_post_id = wp_insert_post($new_post);
			
				update_option('tot_bms_entries', $pid);
				update_post_meta($created_post_id, 'bms_submission_data', $form_data);
				update_post_meta($created_post_id, 'bms_form_id', $form_id);
		
				update_post_meta($created_post_id, 'timeslot', $timeslot);
				update_post_meta($created_post_id, 'booking_date', $booking_date);
				// update_post_meta($created_post_id, 'totalbookings', $totalbookings);
				update_post_meta($created_post_id, 'slotcapacity', $bookedseats);
				$submission_key_val = array();				
				foreach($form_data['data'] as $form_key => $form_value){
                    if($form_key !== 'submit'){
						$submission_key_val[$form_key] = esc_attr($form_value);
                    }
                }
				$explode_booking_date = explode('_',$booking_date);
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
				$format_bookingdate = $explode_booking_date[4] . "-" . $explode_booking_date[2] . "-" . $explode_booking_date[3];
				$converted_bookingdate = date('Y-m-d', strtotime($format_bookingdate));
				
				$encrypted_booking_id = base64_encode($created_post_id);
				$cancelbooking_url = home_url('/cancel-booking/?booking_id=' . $encrypted_booking_id . '&status=cancel');

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
					'BookingSeats' => $no_of_seats,
					'BookedDate' =>$converted_bookingdate,					
					'Service' => $service,
					'prefixlabel' => $prefixlabel,
					'cost' => $cost,					
					'slotcapacity' => $slotcapacity,
					'bookedseats' => $bookedseats,	
					'form_data' => $form_data,
					'no_of_seats' => $no_of_seats,
					'tot_no_of_seats' => $tot_no_of_seats,
					'StartTime' => $explode_timeslot[0],
					'EndTime' => $explode_timeslot[1],
					'CancelBooking' => $cancelbooking_url,
				);

				$listform_label_val = array_merge($submission_key_val, $other_label_val);
				// echo "<pre>"; print_r($listform_label_val);
				if ($enable_auto_approve) {
					if ($waiting_list === 'true') {

						$status = 'waiting';
						$listform_label_val['Status'] = $status;
						update_post_meta($created_post_id, 'entry_status', 'waiting');
						$message = $this->zfb_send_notification($status,$form_id, $created_post_id, $listform_label_val );

					} else {
						
						$status = 'booked';
						$listform_label_val['Status'] = $status;
						update_post_meta($created_post_id, 'entry_status', 'completed');
						$message = $this->zfb_send_notification($status,$form_id,$created_post_id,$listform_label_val );
					}
				} else {
					$status = 'pending';
					$listform_label_val['Status'] = $status;
					update_post_meta($created_post_id, 'entry_status', 'approval_pending');
					$message = $this->zfb_send_notification($status, $form_id, $created_post_id,$listform_label_val );

				}
				$confirmation = get_post_meta($form_id, 'confirmation', true);
				$success_message = get_post_meta($form_id, 'your_field_key', true);
				$formatted_message = wpautop($success_message);
				$redirect_url = '';
				
				// if ($confirmation == 'redirect_text') {
				// 	$wp_editor_value = get_post_meta($form_id, 'redirect_text', true);
				// } elseif ($confirmation == 'redirect_page') {
				// 	$redirect_page = get_post_meta($form_id, 'redirect_page', true);
				// 	$redirect_url = get_permalink($redirect_page);
				// } elseif ($confirmation == 'redirect_to') {
				// 	$redirect_url = get_post_meta($form_id, 'redirect_to', true);
				// }
				
				// Send success response
				wp_send_json_success(array(					
					'message' => 'Sucessfully Submitted',
					'redirect_page' => $redirect_url,
					'wp_editor_value' => $formatted_message,
					'redirect_url' => $redirect_url,
					'confirmation' => $confirmation
				));
			} else {
				// Send error response
				wp_send_json_error(array(
					'message' => 'Something went wrong, Please try again later',
					'error' => $error,
				));
			}
			
			wp_die();
		}
		function zfb_send_notification($status,$form_id, $post_id, $form_data	) {
			
			$get_notification_array = get_post_meta($form_id, 'notification_data', true);	
			
			foreach ($get_notification_array as $notification) {
				
				if ($notification['state'] === 'enabled' && $notification['type'] === $status) {
					// echo $status;
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
					$result = wp_mail($to, $subject, $check_body, $headers);		
					if ($result === true) {
						return 'Email sent successfully';
					} else {
						return 'Failed to send email';
					}
				}
				
			}
		
			return 'No matching notification found';
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
									$Value_exploded = null; // This avoids the need for duplicate unset() statements 
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
			echo $fieldValue;
			return $fieldValue;
		}
		
		function front_get_shortcodes($form_id){
			$shortcode_list = array();
			$form_data1 = get_post_meta( $form_id, '_my_meta_value_key', true ); 
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
				if ($check_type) {
					$weekdays_num = $arrayofdates = array();
					$weekdays = get_post_meta($post_id, 'weekdays', true);					
					$all_days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
					$todays_date = date("Y-m-d");
                    $selected_date = get_post_meta($post_id, 'selected_date', true);
					$selected_date = date("Y-m-d", strtotime($selected_date));
					
                    if($date < $selected_date || $date < $todays_date){
                        $arrayofdates = array();
                    }else{
                        $recurring_type = get_post_meta($post_id, 'recurring_type', true);
                        $holiday_dates = get_post_meta($post_id, 'holiday_dates', true);
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
                          
                        }elseif ($recurring_type == 'weekdays') {
                            
                            foreach ($weekdays as $wdays) {
                                $weekdays_num[] = date('N', strtotime($wdays));
                            }
                            
                        }elseif ($recurring_type == 'certain_weekdays') {
                            $recur_weekdays = get_post_meta($post_id, 'recur_weekdays', true);
                            foreach ($recur_weekdays as $wdays) {
                                $weekdays_num[] = date('N', strtotime($wdays));
                            }
                        }else{
                            foreach ($weekdays as $wdays) {
                                $weekdays_num[] = date('N', strtotime($wdays));
                            }
                        }
                        if($recurring_type == 'advanced'){
                            $advancedata = get_post_meta($post_id, 'advancedata', true);
                            foreach ($advancedata as $index => $data) {
                                if (!in_array($data['advance_date'], $holiday_dates)) {
                                    $arrayofdates[] = $data['advance_date'];
                                }
                            }
                        }else{
                            $current_date = $selected_date;
                            while ($current_date <= $end_repeats_on_date) {
                                if ($recurring_type === 'daily') {						
                                    if (!in_array($current_date, $holiday_dates)) {
                                        $arrayofdates[] = $current_date;
                                    }
                                }else{
                                    $day_of_week = date('N', strtotime($current_date));
                                    if (in_array($day_of_week, $weekdays_num)) {
                                        if (!in_array($current_date, $holiday_dates)) {
                                            $arrayofdates[] = $current_date;
                                        }
                                    }
                                }
                                
                                $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
                            }
                        }
                    }
					
					return $arrayofdates;
				}
			}		
			
		}
		function get_advanced_timeslots($post_id,$inputdate){

			$output_timeslot = '';
			$check_type = get_post_meta($post_id, 'enable_recurring_apt', true);
			if ($check_type) {
				$recurring_type = get_post_meta($post_id, 'recurring_type', true);				
			}

			if($check_type && $recurring_type== 'advanced'){
				$advancedata = get_post_meta($post_id, 'advancedata', true);
				foreach ($advancedata as $index => $data) {
					if($data['advance_date'] == $inputdate){
						foreach ($data['advance_timeslot'] as $slot_index => $timeslot) {
							$start_hours = date('h', strtotime($timeslot['start_time']));
							$sampm = date('a', strtotime($start_hours));

							$end_hours = date('h', strtotime($timeslot['end_time']));
							$sampm = date('a', strtotime($end_hours));							

							if ($start_hours >= 12) {
								$sampm = 'PM';
							} else {
								$sampm = 'AM';
							}

							if ($end_hours >= 12) {
								$eampm = 'PM';
							} else {
								$eampm = 'AM';
							}

							$start_timeslot = $timeslot['start_time'].":00". " ".$sampm;
							$end_timeslot = $timeslot['end_time'].":00". " ".$eampm;
							$output_timeslot .= "<p class='p_timeslot'  start-time='".$start_timeslot."' end-time='".$end_timeslot."'>".$start_timeslot." - " . $end_timeslot."</p>";
						}
					}
				}
			}
			return $output_timeslot;
		}
        function front_generate_timeslots($post_id, $todaysDate = null){

            $output_timeslot = '';
            $start_time = get_post_meta($post_id, 'start_time', true);
            $end_time = get_post_meta($post_id, 'end_time', true);
            $break_times = get_post_meta($post_id, 'breaktimeslots', true);
            $duration_minutes = get_post_meta($post_id, 'timeslot_duration', true);
            $gap_minutes = get_post_meta($post_id, 'steps_duration', true);
            $no_of_booking = get_post_meta($post_id, 'no_of_booking', true); 

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
                $checktimeslot = $start_time_slot."-".$end_time_slot;
				
                $iswaiting_alllowed = get_post_meta( $post_id,'waiting_list', true );
				if(!$iswaiting_alllowed){
					$iswaiting_alllowed = 0;
				}
                $checkseats = $this->get_available_seats_per_timeslot($checktimeslot,$todaysDate);
				$selected_date = get_post_meta( $post_id,'selected_date', true );
				$check_date = 0;
				if($todaysDate < $selected_date){
					$check_date = 1;
				}
				// echo "<pre>"; print_r($checkseats);
                // echo $no_of_booking. ' - '. $checkseats;
                $available_seats = $no_of_booking - $checkseats;
                $output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
                $output_timeslot .= '<span>'.$start_time_slot.' - ' . $end_time_slot.'</span>';
                // $output_timeslot .= '<input class="zfb-selected-capacity" type="number" name="zfbslotcapacity" placeholder="Enter Slot Capacity" min="1" value="1">';
                $output_timeslot .= '<input class="zfb-selected-time" name="booking_slots"  type="hidden" value="'.$start_time_slot."-".$end_time_slot.'">';					
                $output_timeslot .= '<span class="zfb-tooltip-text" data-seats="'.$available_seats.'" >Available seats : '.$available_seats.'</span>';
				$output_timeslot .= '<span class="zfb-waiting" style="display:none;" class="hidden" data-checkdate="'.$check_date.'" data-waiting="'.$iswaiting_alllowed.'" >'.$iswaiting_alllowed.'</span>';
                $output_timeslot .= '</li>';
				// Move to the next available timeslot (including the gap)
				$current_timestamp = $end_timeslot + ($gap * 60);
			}

			return $output_timeslot;
		}
        function get_available_seats_per_timeslot($timeslot,$booking_date){
           
            $array_data = explode('_',$booking_date);
            //  print_r($array_data);
            $post_id = $array_data[1];
            $current_month = $array_data[2];
            $current_day = $array_data[3];
            $current_year = $array_data[4];
            $args = array(
                'post_type' => 'bms_entries',
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
			// echo "<pre>";
			// print_r($query);
			if ($query->have_posts()) {
				$post_count = $query->found_posts;
				$no_of_booking = 0; // Initialize the variable to store the number of bookings
				$arr = array();
				while ($query->have_posts()) {
					$query->the_post();
					$slotcapacity = get_post_meta(get_the_ID(), 'slotcapacity', true);
									
					// Update the total number of bookings
					// $arr[]=array(get_the_ID,$slotcapacity);
					$no_of_booking += $slotcapacity;
				}
				
				wp_reset_postdata();
				
				// Now you have the total number of bookings
				// echo "Total number of bookings: {$slotcapacity}";
			} else {
				$no_of_booking = 0;
				// echo "No posts found with timeslot '{$timeslot}' and booking date '{$booking_date}'.";
			}

            
            return $no_of_booking;
        }
		function get_timeslots_old($post_id){			
			
				$output_timeslot = '';
				$start_time = get_post_meta( $post_id, 'start_time', true );
				$end_time = get_post_meta( $post_id, 'end_time', true );
				$timeslot_duration = get_post_meta($post_id, 'timeslot_duration', true);
				$steps_duration = get_post_meta( $post_id, 'steps_duration', true );

				$start_hours = $start_time['hours'];
				$start_minutes = $start_time['minutes'];
				$start_seconds = $start_time['seconds'];

				$end_hours = $end_time['hours'];
				$end_minutes = $end_time['minutes'];
				$end_seconds = $end_time['seconds'];

				$timeslot_hours = $timeslot_duration['hours'];
				$timeslot_minutes = $timeslot_duration['minutes'];
				$timeslot_seconds = $timeslot_duration['seconds'];

				$gap_hours = $steps_duration['hours'];
				$gap_minutes = $steps_duration['minutes'];
				$gap_seconds = $steps_duration['seconds'];

				$start_time_seconds = ($start_hours * 3600) + ($start_minutes * 60) + $start_seconds;
				$end_time_seconds = ($end_hours * 3600) + ($end_minutes * 60) + $end_seconds;
				$timeslot_duration_seconds = ($timeslot_hours * 3600) + ($timeslot_minutes * 60) + $timeslot_seconds;
				$gap_seconds = ($gap_hours * 3600) + ($gap_minutes * 60) + $gap_seconds;

				$current_time = $start_time_seconds;
				while ($current_time <= $end_time_seconds) {

					$st_hours = floor($current_time / 3600);
					$st_minutes = floor(($current_time % 3600) / 60);
					// $st_seconds = $current_time % 60;
					 $st_seconds = $current_time;


					if ($st_hours >= 12) {
						$sampm = 'PM';
					} else {
						$sampm = 'AM';
					}

					$current_time += $timeslot_duration_seconds;
					$et_hours = floor($current_time / 3600);
					$et_minutes = floor(($current_time % 3600) / 60);
					// $et_seconds = $current_time % 60;
					$et_seconds = $current_time;

					if ($et_hours >= 12) {
						$eampm = 'PM';
					} else {
						$eampm = 'AM';
					}

					$st_formatted_time = sprintf('%02d:%02d', $st_hours, $st_minutes);
					$et_formatted_time = sprintf('%02d:%02d', $et_hours, $et_minutes);
					
					$start_timeslot = $st_formatted_time." ".$sampm;
					$end_timeslot = $et_formatted_time ." " . $eampm;
					$selected_date = get_post_meta( $post_id, 'selected_date', true );
					// $output_timeslot .= "<p class='zfb_timeslot'  start-time='".$start_timeslot."' end-time='".$end_timeslot."'>".$start_timeslot." - " . $end_timeslot."</p>";
					$output_timeslot .= '<li class="zfb_timeslot" onclick="selectTimeslot(this)" >';
					$output_timeslot .= '<span>'.$start_timeslot.' - ' . $end_timeslot.'</span>';
					// $output_timeslot .= '<input class="zfb-selected-capacity" type="number" name="zfbslotcapacity" placeholder="Enter Slot Capacity" min="1" value="1">';
					$output_timeslot .= '<input class="zfb-selected-time" name="booking_slots" type="hidden" value="'.$st_seconds."-".$et_seconds.'">';					
					$output_timeslot .= '<span class="zfb-tooltip-text"> 5 seats Available </span>';
					$output_timeslot .= '</li>';
					$current_time += $gap_seconds;
				}
				return $output_timeslot;
			
		}
		function zealbms_get_booking_form($attr) {
			ob_start();	
			$post_id = $attr['form_id'];			
			?>
			<!-- <div id="calender"></div>	 -->
			<?php
			// Get the current month and year
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
						<span class='zfb-caltitle'>Sample Title</span>
						<p class='zfb-cal-desc'>It is not neccessary that user will add : content, and title.Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley 
							of type and scrambled it to make a type specimen book. </p>
					</div>
					<div class="month-navigation zfb-cal-container" id="month-navigationid">
						<div class="header-calender">
							<input type="hidden" id="zealform_id" value="<?php echo $post_id; ?>">
							
							<span class="arrow" id="prev-month" onclick="getClicked_prev(this)">&larr;</span>
							<!-- months -->
							<select name='bms_month_n' id='bms_month'>
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
							<select name="bms_year_n" id="bms_year">
								<?php
								$startYear = $currentYear + 5;
								$endYear = 2023;
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
						
					
						<table class="zfb-cal-table zfb-cal-table-bordered">
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
											$lastdateid = 'calid_' . $post_id . '_' . $currentMonth . '_' . $date . '_' . $currentYear;
											$lastday = $date;
											$lastmonth = $currentMonth;
											$lastyear = $currentYear;
										}
										echo "<td id='calid_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' data_day='bms_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' class='bms_cal_day $isToday' onclick='getClickedId(this)'>$date</td>";
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
						$TodaysDate = date('F d, Y');	
                        $todaysDate = date('Y-m-d');
						echo "<h3 id='head_avail_time'><span class='gfb-timezone'>Timezone: America/New_York</span></h3>";
						echo "<h4 id='headtodays_date'>$TodaysDate</h4>";			
						// Get array of available dates 
						$is_available = $this->processDate($post_id,$todaysDate);
						// echo "<pre>";
						// print_r($is_available);
						?>
						<ul id='zfb-slot-list'>
							<?php
							
							// echo $todaysDate;				
							if(isset($is_available) && is_array($is_available) && in_array($todaysDate,$is_available)){
								
								$check_type = get_post_meta($post_id, 'enable_recurring_apt', true);
								$recurring_type = get_post_meta($post_id, 'recurring_type', true);
								
								if($check_type && $recurring_type== 'advanced'){
									echo $this->get_advanced_timeslots($post_id,$todaysDate);	
								}else{
                                    echo $this->front_generate_timeslots($post_id,$lastdateid);	
									// echo $this->get_timeslots($post_id);	
								}						
												
							}else{						
								echo "<p class='not_avail'>Not Available</p>";
							}
								
							?>
						</ul>
						<input class="zfb-selected-capacity" type="number" name="zfbslotcapacity" placeholder="Enter Slot Capacity" min="1" value="1">
			
					</div>
					<div class="zfb-cost-label">
						<span class="zfb-cost">Cost: $100</span>
					</div>
					<input type="hidden" id="booking_date" name="booking_date" value="<?php echo $lastdateid; ?>" name="booking_date" >
				</div>
				<div class="step step2">
				<?php
				$timeslot = '';			
				?>
				
				
				<?php					
					if (get_post($post_id)) {
						$post_status = get_post_status($post_id);
						if ($post_status === 'publish') {
							$fields = get_post_meta($post_id, '_my_meta_value_key', true);
							if ($fields) {
							?>
							<div id="formio"></div>
							<script type='text/javascript'>								
								var myScriptData = <?php echo $fields; ?>;															
								var value = myScriptData;
								// console.log(value);
								Formio.createForm(document.getElementById('formio'), {
								components: value
								}).then(function(form) {
									form.on('submit', function(submission) {
										event.preventDefault();
										var formid = <?php echo json_encode($post_id); ?>;
										var booking_date = jQuery('input[name="booking_date"]').val();
										var timeslot = ""; // Declare the variable outside the if block
										var slotcapacity = "";
										jQuery('.zfb_timeslot').each(function() {
											if (jQuery(this).hasClass('selected')) {
												timeslot = jQuery(this).find('input[name="booking_slots"]').val();	
												slotcapacity = jQuery(this).find('.zfb-tooltip-text').attr('data-seats');
											} 
										});
										bookedseats = jQuery('input[name="zfbslotcapacity"]').val();
										bookedseats = jQuery('input[name="zfbslotcapacity"]').val();
										
										jQuery.ajax({
											url: '<?php echo admin_url('admin-ajax.php'); ?>',
											type : 'post',
											data: { 
											action: "bms_front_save_post_meta",
											form_data: submission,
											fid:formid,
											timeslot:timeslot,
											booking_date:booking_date,
											bookedseats:bookedseats,
											slotcapacity:slotcapacity,
											},
											success: function (response) {
												if (response.success) {
													var confirmationType = response.data.confirmation;
													var message = response.data.message;
													var redirectPage = response.data.redirect_page;
													var wpEditorValue = response.data.wp_editor_value;
													var redirectUrl = response.data.redirect_url;
													// // Check the confirmation type
													// if (confirmationType === 'redirect_text') {
													// 	// Replace div content with wpEditorValue or message
													// 	jQuery('#calender_reload').html(wpEditorValue);
													// } else if (confirmationType === 'redirect_to') {
													// 	jQuery('#calender_reload').html('<p>' + message + '</p>');
													// 	setTimeout(function() {
													// 		window.location.href = redirectUrl;
													// 	}, 2000); // Redirect after 3 seconds (adjust as needed)
													// } else if (confirmationType === 'redirect_page') {
													// 	jQuery('#calender_reload').html('<p>' + message + '</p>');
													// 	setTimeout(function() {
													// 		window.location.href = redirectUrl;
													// 	}, 2000); // Redirect after 3 seconds (adjust as needed)
													// }
													jQuery('#nextButton').css('display', 'none');
													jQuery('#backButton').css('display', 'none');
													
												} else {
													var errorMessage = response.data.error;
												}
											}
											
										});
										return false;
									});
										
								});
								
							</script>
							<?php
							} else {
							echo __('Form data not found.', 'bms');
							}
						} else {
							// Post exists but is not published
							echo __("Post exists but is not published.", 'bms');
						}
					} else {
						// Post does not exist
						echo __("Post does not exist.", 'bms');
					}
				?>
				</div>
			</div>
			<div class="dc_backButton alignwide">
				<button id="backButton">Back</button>
				<button id="nextButton">Next</button>
			</div>
			
			<?php
			return ob_get_clean();
		  }
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
			$currentMonth = $_POST['currentMonth'];
			$currentYear = $_POST['currentYear'];
			$post_id = $_POST['form_id'];
			ob_start();
			?>
			<div class="header-calender">
				<input type="hidden" id="zealform_id" value="<?php echo $post_id; ?>" >
                <span class="arrow" id="prev-month" onclick="getClicked_prev(this)">&larr;</span>
                <select name='bms_month_n' id='bms_month'>
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
                <select name="bms_year_n" id="bms_year">
                    <?php
					//here
					$futureYear = date("Y", strtotime("+10 years"));
                  
                    $endYear = 2023;
                    for ($year = $futureYear; $year >= $endYear; $year--) {
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
                $daysInPreviousMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth - 1, $currentYear);

                // Calculate the number of cells needed
                $totalCells = ($totalDays + $firstDayOfWeek - 1) % 7 === 0 ? $totalDays + $firstDayOfWeek - 1 : ceil(($totalDays + $firstDayOfWeek - 1) / 7) * 7;

                $dayCounter = 1;
                $date = 1;
                $monthYear = $currentMonth . '-' . $currentYear;

                while ($dayCounter <= $totalCells) {
                    echo "<tr>";
                    for ($i = 1; $i <= 7; $i++) {
						$calselected_date = "";
						if ($date == 1) {
                           $calselected_date = "calselected_date";
                        }
                        if ($dayCounter >= $firstDayOfWeek && $date <= $totalDays) {
                            // $isToday = ($date == date('j') && $monthYear == date('n-Y')) ? "calselected_date" : "";
                            echo "<td  id='calid_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' data_day='bms_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' class='bms_cal_day ".$calselected_date."' onclick='getClickedId(this)'>$date</td>";
                            $date++;
                        } elseif ($dayCounter < $firstDayOfWeek) {
                            $prevDate = $daysInPreviousMonth - ($firstDayOfWeek - $dayCounter - 1);
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
			<?php
			$output = ob_get_clean();
			echo $output;
			wp_die();
		  }
		  function action_display_available_timeslots(){
				$error = false;
				if(isset( $_POST['form_data'])){
					$form_data = $_POST['form_data'];
					$array_data = explode('_',$form_data);
					// print_r($array_data);
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
                    //check if selected date is equals or greater or else show timeslots have been expired.
				
					$is_available = $this->processDate($post_id,$todaysDate);
					// echo "<pre>";print_r($is_available);
                    if (isset($is_available) && is_array($is_available)) {
                        if (in_array($todaysDate, $is_available)) {
                            $check_type = get_post_meta($post_id, 'enable_recurring_apt', true);
                            $recurring_type = get_post_meta($post_id, 'recurring_type', true);
                            
                            if($check_type && $recurring_type== 'advanced'){
                                echo $this->get_advanced_timeslots($post_id,$todaysDate);	
                            }else{
                                // echo $this->get_timeslots($post_id);
                                echo $this->front_generate_timeslots($post_id,$form_data);		
                            }	
                        } else {
							$error = true;
                            error_log('Check End date! Selected date exceed the selected end date');
                            
                        }
                    } else {
						$error = true;
                        // echo "<p class='not_avail'>Something went wrong</p>";
						// echo '<li class="zfb_timeslot zfb_timeslot_msg"><?php echo __("No timeslots found for selected date. Event may be not scheduled or might have expired.","textdomain")';
                		// echo '<span class="zfb-tooltip-text" data-seats="0" >Available seats : "0"</span>';
                		// echo '</li>';
                        error_log('Array does not exist.');
                    }	
				echo "</ul>";
				if($error === true){
					echo __('No timeslots found for selected date. Event may be not scheduled or might have expired.','textdomain');
				}else{
					echo '<input class="zfb-selected-capacity" type="number" name="zfbslotcapacity" placeholder="Enter Slot Capacity" min="1" value="1">';
				}
				wp_die();
		  }
		function zfb_cancel_booking(){

			$encrypt_bookingId = $_POST['bookingId'];	
			if (isset($_POST['bookingId']) && isset($_POST['status'])) {
				$booking_id = base64_decode($encrypt_bookingId);
				$status = $_POST['status'];

				if ($status === 'cancelled') {
					update_post_meta($booking_id, 'entry_status', 'cancelled');
					$msg = __('Booking has been cancelled successfully','textdomain');
					
				} else {
					$msg = __('Booking has been cancelled successfully','textdomain');
					
				}
			} else {
				$msg = __('Invalid URL.','textdomain');
				
			}

			wp_die();
		}

	}

	add_action( 'plugins_loaded', function() {
		// PB()->front->action = new PB_Front_Action;
		$PB_Front_Action = new PB_Front_Action();
	} );
}
function PB_Front_Action() {
	return new PB_Front_Action();	
}
PB_Front_Action();