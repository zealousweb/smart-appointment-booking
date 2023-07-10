<?php
/**
 * PB_Admin_Fieldmeta Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Plugin name
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PB_Admin_Fieldmeta' ) ) {

	/**
	 * The PB_Admin Class
	 */
	class PB_Admin_Fieldmeta {

		function __construct() {
            add_action( 'add_meta_boxes', array( $this, 'bms_add_meta_box' ) );	
            add_action( 'save_post', array( $this, 'bms_save_post_function' ) );

          
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
		/*
		######## ##     ## ##    ##  ######  ######## ####  #######  ##    ##  ######
		##       ##     ## ###   ## ##    ##    ##     ##  ##     ## ###   ## ##    ##
		##       ##     ## ####  ## ##          ##     ##  ##     ## ####  ## ##
		######   ##     ## ## ## ## ##          ##     ##  ##     ## ## ## ##  ######
		##       ##     ## ##  #### ##          ##     ##  ##     ## ##  ####       ##
		##       ##     ## ##   ### ##    ##    ##     ##  ##     ## ##   ### ##    ##
		##        #######  ##    ##  ######     ##    ####  #######  ##    ##  ######
		*/
        function get_available_seats_per_timeslot($checktimeslot,$date){
            
            // $timeslot = '09:30 AM-10:30 AM';
            // $booking_date = 'calid_5085_6_20_2023';
            
            $args = array(
                'post_type' => 'bms_entries',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'timeslot',
                        'value' => $checktimeslot,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'booking_date',
                        'value' => $date,
                        'compare' => '='
                    )
                )
            );
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                $post_count = $query->found_posts;
                // echo "Number of posts with timeslot '{$timeslot}' and booking date '{$booking_date}': {$post_count}";
            } else {
                // echo "No posts found with timeslot '{$timeslot}' and booking date '{$booking_date}'.";
            }
            
            return $post_count;
        }
	

        /**
         * Display BMS submission Entries
         */ 
        function zfb_entries_render_meta_box_content( $post ){
            
            $form_data = get_post_meta( $post->ID, 'bms_submission_data', true );	
            $form_id = get_post_meta( $post->ID, 'bms_form_id', true );	

            $timeslot = get_post_meta( $post->ID, 'timeslot', true );
            // echo "<br>".	
            $booking_date = get_post_meta( $post->ID, 'booking_date', true );
           
            
            $array_of_date = explode('_',$booking_date);
            if(isset($array_of_date) && !empty( $array_of_date[2]) && !empty( $array_of_date[3]) && !empty( $array_of_date[4])){
                $bookedmonth = $array_of_date[2];
                $bookedday =$array_of_date[3];
                $bookedyear =$array_of_date[4];
                $booked_date = $bookedday."-".$bookedmonth."-".$bookedyear;
                // $totalbookings = get_post_meta( $post->ID, 'totalbookings', true );	
                $slotcapacity = get_post_meta( $post->ID, 'slotcapacity', true );	
            }
            // echo $checkseats = $this->get_available_seats_per_timeslot($timeslot,$booked_date);

            if(!empty($form_id)){ 
               $booking_form_title = get_the_title($form_id);               
            }
            $date_generated = get_the_date($post->ID);
            $status = get_post_meta( $post->ID, 'entry_status', true );
            if(empty($status)){
                $status = "Approval Pending";
            }elseif($status == 'completed'){
                $status = "Completed";
            }elseif($status == 'approval_pending'){
                $status = "Approval Pending";
            }elseif($status == 'cancelled'){
                $status = "Cancelled";
            }elseif($status == 'manual'){
                $status = "Manual";
            }elseif($status == 'expired'){
                $status = "Expired";
            }
           
            ?>
            <ul>
                <li><?php echo __('Form Title', 'textdomain')." : ".$booking_form_title; ?></li>
                <?php  if(isset($date_generated) ){ ?>
                <li><?php echo __('Date Generated', 'textdomain')." : ".$date_generated; ?></li>
                <?php } ?>
                <?php  if(isset($status) ){ ?>
                <li><?php echo __('Status', 'textdomain')." : ".$status; ?></li>
                <?php } ?>
                <li><?php echo __('Customer', 'textdomain'); ?> : <?php echo __('Guest', 'textdomain'); ?></li>
                <?php  if( isset($booked_date)){ ?>
                <li><?php echo __('Booking Date', 'textdomain'); ?> : <?php echo __($booked_date, 'textdomain');; ?></li>
                <?php } ?>
                <?php  if( isset($timeslot) && !empty($timeslot)){ ?>
                <li><?php echo __('Timeslot', 'textdomain'); ?> : <?php echo __($timeslot, 'textdomain'); ?></li>
                <?php } ?>
                <?php  if(isset($slotcapacity)){ ?>
                <li><?php echo __('No of Slots Booked', 'textdomain'); ?> : <?php echo __($slotcapacity, 'textdomain'); ?></li>
                <?php } ?>
            </ul>  
            <?php
           
        }

        /**
         * Display Form Io form builder
        */ 
        function formio_render_meta_box_content( $post ) {
            
            wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );
            $fields = get_post_meta( $post->ID, '_my_meta_value_key', true );	
            $get_type = gettype($fields);
            
            if(!empty($fields) && $get_type === 'string') {
                $myScriptData = $fields;
                ?>
            
                <div id='builder'></div>
                <form-builder form="form"></form-builder>
                <script type='text/javascript'>
                    
                    var myScriptData = <?php echo $myScriptData; ?>;
                    window.onload = function() {
                        
                        var formioBuilder = Formio.builder(document.getElementById('builder'), {
                            components: myScriptData // Use the stored meta value to populate the form
                        });
                                                    
                        formioBuilder.then(function(builder) {
                            // var fieldSlugs = []; // Array to store field slugs
                            // // Iterate through the form components and extract field slugs
                            // builder.instance.form.components.forEach(function(component) {
                            //     if (component.key) {
                            //         fieldSlugs.push(component.key);
                            //     }
                            // });

                            // console.log(fieldSlugs); // Output the field slugs

                            // Handle form submission
                            builder.on('change', function(submission) {
                                formdata = JSON.stringify(submission.components);
                                jQuery.post(ajaxurl, {
                                    action: 'bms_save_form_data',  // Ajax action to handle saving the form data
                                    post_id: <?php echo $post->ID; ?>,  // Current post ID
                                    form_data: formdata // Submitted form data									
                                }, function(response) {
                                    // console.log(submission.components);
                                    console.log(response);
                                });
                            });
                        });
                    };
                </script>
                <?php

            }else{
                ?>
                <div id='builder'></div>
                <form-builder form="form"></form-builder>
                <script type='text/javascript'>  
                    window.onload = function() {
                        var formioBuilder = Formio.builder(document.getElementById('builder'), {});
                        formioBuilder.then(function(builder) {
                            // Handle form submission
                            builder.on('change', function(submission) {
                                formdata = JSON.stringify(submission.components);
                                jQuery.post(ajaxurl, {
                                    action: 'bms_save_form_data',  // Ajax action to handle saving the form data
                                    post_id: <?php echo $post->ID; ?>,  // Current post ID
                                    form_data: formdata // Submitted form data									
                                }, function(response) {
                                    // console.log(submission.components);
                                    console.log(response);
                                });
                            });
                        });
                    };
                </script>
                <?php
            }
            
        }

        function bms_repeat_appointment($post) {

            // Retrieve saved meta box values
            $title = get_post_meta($post->ID, 'cal_title', true);
            $description = get_post_meta($post->ID, 'cal_description', true);
            $enable_booking = get_post_meta($post->ID, 'enable_booking', true);
            $weekdays = get_post_meta($post->ID, 'weekdays', true);
            // $weekend = get_post_meta($post->ID, 'weekend', true);
                        
            $appointment_type = get_post_meta($post->ID, 'appointment_type', true);
            $virtual_link = get_post_meta($post->ID, 'virtual_link', true);
            $symbol = get_post_meta($post->ID, 'label_symbol', true);
            $cost = get_post_meta($post->ID, 'cost', true);
            
            $selected_date = get_post_meta($post->ID, 'selected_date', true);

            $start_time = get_post_meta( $post->ID, 'start_time', true );
            $end_time = get_post_meta( $post->ID, 'end_time', true );
            $timeslot_duration = get_post_meta($post->ID, 'timeslot_duration', true);
            $steps_duration = get_post_meta( $post->ID, 'steps_duration', true );
            $timezone = get_post_meta($post->ID,'timezone',true);
            // $btimes = get_post_meta( $post->ID, 'break_repeater_field', true );
            $no_of_booking = get_post_meta($post->ID, 'no_of_booking', true);  
            $holiday_dates = get_post_meta($post->ID, 'holiday_dates', true);
            $enable_waiting = get_post_meta($post->ID, 'waiting_list', true);
            $enable_auto_approve = get_post_meta($post->ID, 'enable_auto_approve', true);
            // $breaktimeslots = get_post_meta($post->ID, 'breaktimeslots', true);

            $breaktimeslots = get_post_meta($post->ID, 'breaktimeslots', true);
            if (empty($breaktimeslots)) {
                $breaktimeslots = array(
                array(
                    'start_time' => '',
                    'end_time' => '',
                ),
                );
            }
            //section 2 
            $enable_recurring_apt = get_post_meta($post->ID, 'enable_recurring_apt', true);
            $recurring_type = get_post_meta($post->ID, 'recurring_type', true);
            //advanced field
            // $advancedates = get_post_meta($post->ID, 'advancedates', true);
            $advancedata = get_post_meta($post->ID, 'advancedata', true);
            if (empty($advancedata)) {
                $advancedata = array(
                    array(
                        'advance_date' => '',
                        'advance_timeslot' => array(
                            array(
                                'start_time' => '',
                                'end_time' => '',
                                'bookings' => ''
                            )
                        )
                    )
                );
            }
            // $advanced_date_value = get_post_meta($post->ID, 'advanced_date_value', true);
            // $advance_timeslots = get_post_meta($post->ID, 'advance_timeslots', true);
            $end_repeats = get_post_meta($post->ID, 'end_repeats', true);
            $end_repeats_on = get_post_meta($post->ID, 'end_repeats_on',true);
            // $end_repeats_after = get_post_meta($post->ID, 'end_repeats_after',true);
            $recur_weekdays = get_post_meta($post->ID, 'recur_weekdays', true);
            
           
            ?>
            <div id="custom-meta-box-tabs">
                <!-- Tab navigations -->
                <ul class="tab-navigation nav nav-tabs">
                    
                    <li class="nav-link"><a href="#tab1">General</a></li>
                    <li class="nav-link"><a href="#tab2">Timeslots</a></li>
                    <li class="nav-link"><a href="#tab3">Recurring Appointment</a></li>
                  
                    <li class="nav-link"><a href="#tab5">Preview</a></li>
                </ul>
                <!-- Tabination 1 content  -->            
                
                <div id="tab1" class="tab-content">
                    <div class="row">
                        <div class="col-6">
                            <!-- <div class=""> -->
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="enable_booking" value="1" <?php echo checked(1, $enable_booking, false); ?>>
                                    <label class="form-check-label h6" for="enable_booking"> Enable or disable booking form</label>
                                </div>
                                <div class="form-group form-general-group">
                                    <!--Timezone -->
                                    <label  for="title" class="h6">Enter Calender Title</label>
                                    <input class="form-control" type="text" name="cal_title" value="<?php echo esc_attr($title); ?>" width="30px" >
                                </div>
                                <div class="form-group form-general-group">
                                    <!--Timezone -->
                                    <label for="timezone"  class="h6">Description</label>
                                    <textarea class="form-control" rows="3" cols="50" name="cal_description"><?php echo $description; ?></textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label  class="h6"><?php echo __('Prefix Symbol : ', 'textdomain'); ?></label>
                                        <input type="text" class="form-control" name="label_symbol" value="<?php echo esc_attr($symbol); ?>">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label  class="h6"> <?php echo __('Cost : ', 'textdomain'); ?></label>
                                        <input type="number" class="form-control" name="cost" value="<?php echo esc_attr($cost); ?>">
                                    </div>
                                </div>
                            <!-- </div> -->
                        </div>
                        <div class="col-6">
                                 <!-- <div class="card"> -->
                                <label class="h6"><?php echo __('Select Weekdays: ', 'textdomain'); ?></label>
                                <div class="form-group">
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" name="weekdays[]" value="monday" <?php echo (is_array($weekdays) && in_array('monday', $weekdays)) ? 'checked' : ''; ?> id="weekday_monday">
                                        <label class="form-check-label" for="weekday_monday">Monday</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" name="weekdays[]" value="tuesday" <?php echo (is_array($weekdays) && in_array('tuesday', $weekdays)) ? 'checked' : ''; ?> id="weekday_tuesday">
                                        <label class="form-check-label" for="weekday_tuesday">Tuesday</label>
                                        
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" name="weekdays[]" value="wednesday" <?php echo (is_array($weekdays) && in_array('wednesday', $weekdays)) ? 'checked' : ''; ?> id="weekday_wednesday">
                                        <label class="form-check-label" for="weekday_wednesday">Wednesday</label>
                                       
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" name="weekdays[]" value="thursday" <?php echo (is_array($weekdays) && in_array('thursday', $weekdays)) ? 'checked' : ''; ?> id="weekday_thursday">
                                        <label class="form-check-label" for="weekday_thursday">Thursday</label>
                                        
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" name="weekdays[]" value="friday" <?php echo (is_array($weekdays) && in_array('friday', $weekdays)) ? 'checked' : ''; ?> id="weekday_friday">
                                        <label class="form-check-label" for="weekday_friday">Friday</label>
                                        
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" name="weekdays[]" value="saturday" <?php echo (is_array($weekdays) && in_array('saturday', $weekdays)) ? 'checked' : ''; ?> id="weekday_saturday">
                                        <label class="form-check-label" for="weekday_saturday">Saturday</label>
                                        
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" name="weekdays[]" value="sunday" <?php echo (is_array($weekdays) && in_array('sunday', $weekdays)) ? 'checked' : ''; ?> id="weekday_sunday">
                                        <label class="form-check-label" for="weekday_sunday">Sunday</label>
                                     
                                    </div>
                                    
                                </div>
                               
                                <div class="form-group form-general-group"><label  class="h6"><?php echo __('Appointment Type: ', 'textdomain'); ?></label>

                                    <div class="form-check form-check-inline">
                                    <input type="radio" name="appointment_type" id="appointment_type_virtual" value="virtual" <?php if ($appointment_type == 'virtual') echo 'checked="checked"'; ?>>
                                    <label class="form-check-label h6" for="appointment_type_virtual">Virtual</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                    <input type="radio" name="appointment_type" id="appointment_type_physical" value="physical" <?php if ($appointment_type == 'physical') echo 'checked="checked"'; ?>>
                                    <label class="form-check-label h6" for="appointment_type_physical">Physical</label>
                                    </div>
                                </div>
                            
                                <?php 
                                    if ($appointment_type == 'virtual') : ?>
                                    <div class="vlink-container form-group form-general-group">
                                        <label for="virtual_link"  class="h6">Link</label>
                                        <input type="text" class="form-control" name="virtual_link" value="<?php echo esc_attr($virtual_link); ?>" pattern="https?://.+" required>
                                        <small class="validation-error form-text text-muted" style="display:none;">Please enter a valid URL starting with http:// or https://</small>
                                    </div>
                                    <?php else : ?>
                                        <div class="vlink-container hidden form-group form-general-group">
                                            <label for="virtual_link"  class="h6"><?php echo __('Link: ', 'textdomain'); ?></label>
                                            <input type="text" class="form-control" name="virtual_link" value="<?php echo esc_attr($virtual_link); ?>">
                                        </div>
                                    <?php endif; 
                                ?>
                                <div class="form-group form-general-group">
                                    <!--Timezone -->
                                    <label  for="timezone" class="h6">Timezone</label>
                                    <?php echo $this->timezone_dropdown($post->ID); ?>
                                </div> 
                                
                            <!-- </div> -->
                        </div>
                    </div>
                </div>
                <div id="tab2" class="tab-content">
                    <div class="">
                        <div class="form-group form-general-group ">
                            <label  class="h6"><?php echo __('Select Date : ', 'textdomain'); ?></label>
                            <input type="date" class="form-control col-md-8" name="selected_date" value="<?php echo esc_attr($selected_date); ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label  class="h6"><?php echo __('Start Time: ', 'textdomain'); ?></label>
                                <input type="time" class="form-control" name="start_time" value="<?php echo isset($start_time) ? esc_attr($start_time) : ''; ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label  class="h6"><?php echo __('End Time: ', 'textdomain'); ?></label>
                                <input type="time" class="form-control" name="end_time" value="<?php echo isset($end_time) ? esc_attr($end_time) : ''; ?>" required>
                               </div>
                            <span class="validation-message" style="color: red;"></span>
                        </div>
                        
                        <!-- waiting List -->
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="waiting_list" value="1" <?php echo checked(1, $enable_waiting, false); ?>>
                            <label class="form-check-label h6" for="waiting_list">Allow Waiting List</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <!-- Allow Auto Approve -->
                            <input type="checkbox" name="enable_auto_approve" value="1" <?php echo checked(1, $enable_auto_approve, false); ?>>
                            <label class="form-check-label h6" for="waiting_list">Allow Auto Approve</label>
                            
                        </div>
                        <div class="form-group form-general-group">
                            <label  class="h6"><?php echo __('Timeslot Duration(hh:mm)', 'textdomain'); ?></label>
                            <input type="number" class="hours" name="timeslot_duration[hours]" min="0" max="23" placeholder="HH" value="<?php echo isset($timeslot_duration['hours']) ? esc_attr($timeslot_duration['hours']) : ''; ?>" required>
                            <span>:</span>
                            <input type="number" class="minutes" name="timeslot_duration[minutes]" min="0" max="59" placeholder="MM" value="<?php echo isset($timeslot_duration['minutes']) ? esc_attr($timeslot_duration['minutes']) : ''; ?>" required>
                            <span class="timeslot-validation-message" style="color: red;"></span>
                        </div>
                        <label for="steps_duration"  class="h6"><?php echo __("Step Duration","textdomain"); ?></label>
                        <div class="form-row">
                            
                            <div class="form-group col-md-4">
                                <input type="number" class="hours form-control" name="steps_duration[hours]" min="0" max="23" placeholder="HH" 
                                        value="<?php echo isset($steps_duration['hours']) ? esc_attr($steps_duration['hours']) : ''; ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <input type="number" class="minutes form-control" name="steps_duration[minutes]" min="0" max="59" placeholder="MM" 
                                        value="<?php echo isset($steps_duration['minutes']) ? esc_attr($steps_duration['minutes']) : ''; ?>" required>
                            </div>
                            <span class="validation-message" style="color: red;"></span>
                        </div>
                        <div class="form-group form-general-group">                
                            <!-- Booking per Timeslots -->
                            <label  class="h6"><?php echo __('No of Booking per Timeslots : ', 'textdomain'); ?></label>
                            <input class="form-control col-md-8" type="number" name="no_of_booking" value="<?php echo esc_attr($no_of_booking); ?>">
                        </div>
                        <div class="breaktimeslot-repeater">
                            <label  class="h6">Add Break Timeslots:</label>
                            <svg class="add-breaktimeslot" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                            </svg>
                           
                            <?php foreach ($breaktimeslots as $index => $timeslot) : ?>
                                    <div class="breaktimeslot">
                                        <label  class="h6">Start Time:</label>
                                        <input type="time"name="breaktimeslots[<?php echo $index; ?>][start_time]" value="<?php echo esc_attr($timeslot['start_time']); ?>">
                                    
                                        <label  class="h6">End Time:</label>
                                        <input type="time" name="breaktimeslots[<?php echo $index; ?>][end_time]" value="<?php echo esc_attr($timeslot['end_time']); ?>">                            
                                        <button class="remove-breaktimeslot rm-brktime-slot">
                                            <svg  xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                                            </svg>
                                        </button>
                                    </div>
                            <?php endforeach; ?>
                           
                        </div>
                    </div>
                </div>
                <!-- Tabination 2 content  -->
                <div id="tab3" class="tab-content">               
                    <!-- <div class="p-4 m-1"> -->
                       
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="enable_recurring_apt_i" name="enable_recurring_apt" value="1" <?php echo checked(1, $enable_recurring_apt, false); ?>>
                            <label class="form-check-label h6" for="waiting_list">Enable Recurring Bookings</label>
                        </div>
                        <!-- hide and show whole container on enable and disable button -->
                        <?php if ($enable_recurring_apt) : ?>
                            <div id="recurring_result">
                        <?php else : ?>
                            <div id="recurring_result" style="display: none;">
                        <?php endif; ?>
                            <label class="h6" for="recurring_type">Repeat Recurring</label>
                            <div class="form-group form-general-group col-md-3 pl-md-0">
                                <select class="form-control " name="recurring_type" id="recurring_type">
                                    <option value="any" <?php echo selected('any', $recurring_type, false); ?>>Select Any</option>
                                    <option value="daily" <?php echo selected('daily', $recurring_type, false); ?>>Daily</option>
                                    <option value="weekend" <?php echo selected('weekend', $recurring_type, false); ?>>Every Weekend</option>
                                    <option value="weekdays" <?php echo selected('weekdays', $recurring_type, false); ?>>Every Weekday</option>
                                    <option value="certain_weekdays" <?php echo selected('certain_weekdays', $recurring_type, false); ?>>Certain Days</option>
                                    <option value="advanced" <?php echo selected('advanced', $recurring_type, false); ?>>Advanced</option>
                                </select>
                            </div>
                            <div id="certain_weekdays_fields" class="form-group form-general-group" style="display: none;" >
                            <label for="recurring_type"><?php echo __('Select Weekdays: ', 'textdomain'); ?></label>
                                <div class="form-check">
                                    
                                    <input type="checkbox" name="recur_weekdays[]" value="monday" <?php echo (is_array($recur_weekdays) && in_array('monday', $recur_weekdays)) ? 'checked' : ''; ?> >
                                    <label class="form-check-label" for="weekday_monday">Monday</label>
                                    <input type="checkbox" name="recur_weekdays[]" value="tuesday" <?php echo (is_array($recur_weekdays) && in_array('tuesday', $recur_weekdays)) ? 'checked' : ''; ?> >
                                    <label class="form-check-label" for="weekday_tuesday">Tuesday</label>
                                    <input type="checkbox" name="recur_weekdays[]" value="wednesday" <?php echo (is_array($recur_weekdays) && in_array('wednesday', $recur_weekdays)) ? 'checked' : ''; ?> >
                                    <label class="form-check-label" for="weekday_wednesday">Wednesday</label>
                                    <input type="checkbox" name="recur_weekdays[]" value="thursday" <?php echo (is_array($recur_weekdays) && in_array('thursday', $recur_weekdays)) ? 'checked' : ''; ?> >
                                    <label class="form-check-label" for="weekday_thursday">Thursday</label>
                                    <input type="checkbox" name="recur_weekdays[]" value="friday" <?php echo (is_array($recur_weekdays) && in_array('friday', $recur_weekdays)) ? 'checked' : ''; ?> >
                                    <label class="form-check-label" for="weekday_friday">Friday</label>
                                    <input type="checkbox" name="recur_weekdays[]" value="saturday" <?php echo (is_array($recur_weekdays) && in_array('saturday', $recur_weekdays)) ? 'checked' : ''; ?> >
                                    <label class="form-check-label" for="weekday_saturday">Saturday</label>
                                    <input type="checkbox" name="recur_weekdays[]" value="sunday" <?php echo (is_array($recur_weekdays) && in_array('sunday', $recur_weekdays)) ? 'checked' : ''; ?> >
                                    <label class="form-check-label" for="weekday_sunday">Sunday</label>
                                </div>
                            </div>
                            <div id="advance-meta-box">
                                <!-- <button type="button" id="add-row" class="btn btn-info">Add Date Group</button> -->
                                <div id="add-row" class="adddatefieldgroup">
                                    <label class="h6">Add Date Field Group</label>
                                    <svg  xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                    </svg>
                                </div>
                                <?php foreach ($advancedata as $index => $data) { ?>
                                    <div class="repeater-row border m-0 mb-2 p-3 row">
                                        <div class="form-group col-md-3">
                                            <label class="h6" for="advance_date_<?php echo $index; ?>">Advance Date:</label>
                                            <input type="date" class="form-control" id="advance_date_<?php echo $index; ?>" name="advancedata[<?php echo $index; ?>][advance_date]" value="<?php echo esc_attr($data['advance_date']); ?>">
                                        </div>
                                        <div class="timeslot-repeater timeslot-container col-md-9 "  id="timeslot-repeater-<?php echo $index; ?>">
                                            <div class="add-timeslot" id="add_timeslot_m">
                                                <label class="h6 ml-1">Add Timeslots </label>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                                </svg>
                                            </div>
                                            <?php foreach ($data['advance_timeslot'] as $slot_index => $timeslot) { ?>
                                                <div class="form-row timeslot-row ">
                                                    <div class="form-group col-md-3">
                                                        <label>Start Time:</label>
                                                        <input type="time" class="form-control" name="advancedata[<?php echo $index; ?>][advance_timeslot][<?php echo $slot_index; ?>][start_time]" value="<?php echo esc_attr($timeslot['start_time']); ?>">
                                                    </div>
                                                    <div class="form-group col-md-3"> 
                                                        <label>End Time:</label>
                                                        <input type="time" class="form-control" name="advancedata[<?php echo $index; ?>][advance_timeslot][<?php echo $slot_index; ?>][end_time]" value="<?php echo esc_attr($timeslot['end_time']); ?>">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label>Bookings:</label>
                                                        <input type="number" class="form-control" name="advancedata[<?php echo $index; ?>][advance_timeslot][<?php echo $slot_index; ?>][bookings]" value="<?php echo esc_attr($timeslot['bookings']); ?>">
                                                    </div>
                                                    <div class="form-group col-2 remove-timeslot-wrapper">
                                                    <svg class="remove-timeslot" xmlns="http://www.w3.org/2000/svg" width="16" 
                                                        height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                                        <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <!-- <button type="btn button" class="add-timeslot btn btn-secondary">Add Timeslot</button> -->
                                        </div>
                                        <button type="button" class="remove-row btn btn-danger">Remove Date</button>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="holiday-repeater">
                               
                                <label class="h6">Add Holidays</label>
                                <svg class="add-holidate" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                </svg>
                                <?php if ($holiday_dates && is_array($holiday_dates)): ?>
                                    <?php foreach ($holiday_dates as $holydate): ?>
                                        <div class="form-row holidate-field">
                                            <div class="form-group col-md-2">
                                                 <input type="date" class="form-control" name="holidays[]" value="<?php echo esc_attr($holydate); ?>">
                                             </div>
                                            <div class="form-group col-md-2"> 
                                           
                                            <svg class="remove-holidate" xmlns="http://www.w3.org/2000/svg" width="16" 
                                                height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                                <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>
                                            </svg>
                                            </div>
                                        </div>
                                        <!-- <div class="holidate-field form-group col-md-3">
                                            <input type="date" class="form-control" name="holidays[]" value="<?php// echo esc_attr($holydate); ?>">
                                            <button type="btn button" class="remove-holidate">Remove Holiday</button>
                                        </div> -->
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class=" form-general-group">
                                <label class="end_repeats_label h6">End Repeats:</label>
                                
                                <div class="end_repeats_options form-group">
                                    <input type="radio" name="end_repeats" value="never" <?php echo checked('never', $end_repeats, false); ?>> Never
                                    <br>
                                    <input type="radio" name="end_repeats" value="on" <?php echo checked('on', $end_repeats, false); ?>> On
                                    <input type="date"  name="end_repeats_on" value="<?php echo esc_attr($end_repeats_on); ?>">
                                    <br>
                                </div>
                            </div>
                        </div>
                    <!-- </div> -->
                </div>
                
                <div id="tab5" class="tab-content">
                    <div class="preview_main">
                        <p id="preview_timeslot" pid="<?php echo get_the_ID(); ?>">Click Here to Preview Timeslots</p>
                        <?php 
                               $post_id = get_the_ID();
                               $start_time = get_post_meta($post_id, 'start_time', true);
                               $end_time = get_post_meta($post_id, 'end_time', true);
                               $duration_minutes = get_post_meta($post_id, 'timeslot_duration', true);
                               if($start_time && $end_time && $duration_minutes){
                                 echo '<div id="preview_output"></div>';
                               }else{
                                echo "<p class='note_preview' > To Preview Timeslots, Set General Setting : start time, end time , Duration, steps , breaks </p>";
                               }
                                // $break_times = get_post_meta($post_id, 'breaktimeslots', true);
                           
                                //$gap_minutes = get_post_meta($post_id, 'steps_duration', true);
                        ?>
                       
                    </div>  
                </div>
               
            </div>
            <?php
        }
       
        function bms_save_post_function( $post_id ) {
            $get_type =  get_post_type($post_id);
            if($get_type !== 'bms_forms'){
                return;
            }
            if (isset($_POST['cal_title'])) {
                update_post_meta($post_id, 'cal_title', $_POST['cal_title']);
            } 
            if (isset($_POST['cal_description'])) {
                update_post_meta($post_id, 'cal_description', $_POST['cal_description']);
            } 
            // Section Tab 1 
            // Check if the enable_booking field is set and save the value
            if (isset($_POST['enable_booking'])) {
                update_post_meta($post_id, 'enable_booking', 1);
            } else {
                delete_post_meta($post_id, 'enable_booking');
            }
            //Weekdays
            if (isset($_POST['weekdays'])) {
                update_post_meta($post_id, 'weekdays', $_POST['weekdays']);
            } else {
                update_post_meta($post_id, 'weekdays', array());
            }
            // Save the radio button value for appointment Type
            if (isset($_POST['appointment_type'])) {
                $selected_option = sanitize_text_field($_POST['appointment_type']);
                update_post_meta($post_id, 'appointment_type', $selected_option);
            }

            // Save the  link value if Appointment Type "Virtual" is selected
            if (isset($_POST['virtual_link'])) {
                $link_value = sanitize_text_field($_POST['virtual_link']);
                update_post_meta($post_id, 'virtual_link', $link_value);
            }
           
             //Symbol
             if ( isset( $_POST['label_symbol'] ) ) {
                $label_symbol = $_POST['label_symbol'];
                update_post_meta( $post_id, 'label_symbol', $label_symbol );
            }
             //Cost
            if ( isset( $_POST['cost'] ) ) {
                $selected_date = $_POST['cost'];
                update_post_meta( $post_id, 'cost', $selected_date );
            }
              //selected_date
            if ( isset( $_POST['selected_date'] ) ) {
                $selected_date = $_POST['selected_date'];
                update_post_meta( $post_id, 'selected_date', $selected_date );
            }
            //Start Time
            if ( isset( $_POST['start_time'] ) ) {
              $time_slot = $_POST['start_time'];
                // $sanitized_start_time = array(
                //     'hours' => sanitize_text_field( $time_slot['hours'] ),
                //     'minutes' => sanitize_text_field( $time_slot['minutes'] ),
                //     'seconds' => sanitize_text_field( $time_slot['seconds'] ),
                //     'ampm' => sanitize_text_field( $time_slot['ampm'] ),
                // );
    
                // Update the post meta data with the field value
                update_post_meta( $post_id, 'start_time', $time_slot );
            }
             //End Time
             if ( isset( $_POST['end_time'] ) ) {
                $time_slot = $_POST['end_time'];
                // $sanitized_end_time = array(
                //     'hours' => sanitize_text_field( $time_slot['hours'] ),
                //     'minutes' => sanitize_text_field( $time_slot['minutes'] ),
                //     'seconds' => sanitize_text_field( $time_slot['seconds'] ),
                //     'ampm' => sanitize_text_field( $time_slot['ampm'] ),
                // );
        
                // Update the post meta data with the field value
                update_post_meta( $post_id, 'end_time', $time_slot );
            }
             //Steps Duration
             if ( isset( $_POST['steps_duration'] ) ) {
                $steps_duration = $_POST['steps_duration'];
                $sanitized_steps_duration = array(
                    'hours' => sanitize_text_field( $steps_duration['hours'] ),
                    'minutes' => sanitize_text_field( $steps_duration['minutes'] )
                    // 'seconds' => sanitize_text_field( $steps_duration['seconds'] ),                   
                );
        
                // Update the post meta data with the field value
                update_post_meta( $post_id, 'steps_duration', $sanitized_steps_duration );
            }
             //timeslot_duration
             if ( isset( $_POST['timeslot_duration'] ) ) {
                $timeslot_duration = $_POST['timeslot_duration'];
                $sanitized_timeslot_duration = array(
                    'hours' => sanitize_text_field( $timeslot_duration['hours'] ),
                    'minutes' => sanitize_text_field( $timeslot_duration['minutes'] )
                );
        
                // Update the post meta data with the field value
                update_post_meta( $post_id, 'timeslot_duration', $sanitized_timeslot_duration );
            }
          
            //no_of_booking
            if ( isset( $_POST['no_of_booking'] ) ) {
                $selected_date = $_POST['no_of_booking'];
                update_post_meta( $post_id, 'no_of_booking', $selected_date );
            }
            //waiting List
            if (isset($_POST['waiting_list'])) {
                update_post_meta($post_id, 'waiting_list', 1);
            } else {
                delete_post_meta($post_id, 'waiting_list');
            }
            //enable_auto_approve
            if (isset($_POST['enable_auto_approve'])) {
                update_post_meta($post_id, 'enable_auto_approve', 1);
            } else {
                delete_post_meta($post_id, 'enable_auto_approve');
            }
            //multiple breaks
            if (isset($_POST['breaktimeslots'])) {
                $breaktimeslots = $_POST['breaktimeslots'];
            
                // Sanitize and save the values
                $sanitized_breaktimeslots = array();
                foreach ($breaktimeslots as $breaktimeslot) {
                  $breakstart_time = sanitize_text_field($breaktimeslot['start_time']);
                  $breakend_time = sanitize_text_field($breaktimeslot['end_time']);
                  $sanitized_breaktimeslots[] = array(
                    'start_time' => $breakstart_time,
                    'end_time' => $breakend_time,
                  );
                }            
                update_post_meta($post_id, 'breaktimeslots', $sanitized_breaktimeslots);
              }else{
                    $breaktimeslots = get_post_meta($post_id, 'timeslots', true);
                    if (empty($timeslots)) {
                        $sanitized_breaktimeslots = array(
                            array(
                            'start_time' => '',
                            'end_time' => '',
                            ),
                        );
                    }
                    update_post_meta($post_id, 'breaktimeslots', $sanitized_breaktimeslots);
              }
            
           
             //Enable Recurring Events
            if (isset($_POST['enable_recurring_apt'])) {
                // echo $_POST['enable_recurring_apt'];
                update_post_meta($post_id, 'enable_recurring_apt', 1);
            } else {
                delete_post_meta($post_id, 'enable_recurring_apt');
            }
             // Check if the meta values are set
            if (isset($_POST['recurring_type'])) {
                $recurring_type = sanitize_text_field($_POST['recurring_type']);
                update_post_meta($post_id, 'recurring_type', $recurring_type);
            }
           
            // Check if the 'recur_weekdays' field is present in the $_POST data
            if (isset($_POST['recur_weekdays'])) {
                // Sanitize the array of weekdays
                $sanitized_recur_weekdays = array_map('sanitize_text_field', $_POST['recur_weekdays']);

                // Save the selected weekdays as post meta data
                update_post_meta($post_id, 'recur_weekdays', $sanitized_recur_weekdays);               

            }
            if (isset($_POST['advancedata'])) {
                
                $advancedata = $_POST['advancedata'];
             
                update_post_meta($post_id, 'advancedata', $advancedata);
                
            }
               // Holidays
            if (isset($_POST['holidays'])) {
                $holidays = array_map('sanitize_text_field', $_POST['holidays']);
                update_post_meta($post_id, 'holiday_dates', $holidays);
            }

            // Save the "End Repeats" option
            if (isset($_POST['end_repeats'])) {
                $end_repeats = sanitize_text_field($_POST['end_repeats']);
                update_post_meta($post_id, 'end_repeats', $end_repeats);
            }

            // Save the corresponding input field values based on the "End Repeats" option
            if (isset($_POST['end_repeats_on'])) {
                $end_repeats_on = sanitize_text_field($_POST['end_repeats_on']);
                update_post_meta($post_id, 'end_repeats_on', $end_repeats_on);
            }

            if (isset($_POST['end_repeats_after'])) {
                $end_repeats_after = sanitize_text_field($_POST['end_repeats_after']);
                update_post_meta($post_id, 'end_repeats_after', $end_repeats_after);
            }
          
         }
        /**
	 	* Adds the meta box container.
		*/
		function bms_add_meta_box( $post_type ) {
			// Limit meta box to certain post types.
			$post_types = array( 'bms_entries');

			if ( in_array( $post_type, $post_types ) ) {
				add_meta_box(
					'form_submission_data',
					__( 'General Details', 'textdomain' ),
					array( $this, 'zfb_entries_render_meta_box_content' ),
					$post_type,
					'advanced',
					'high'
				);

                add_meta_box(
					'edit_form_data',
					__( 'Edit Forms Details', 'textdomain' ),
					array( $this, 'zfb_edit_form_details' ),
					$post_type,
					'advanced',
					'high'
				);
			}

			$post_types = array( 'bms_forms');

			if ( in_array( $post_type, $post_types ) ) {

				add_meta_box(
					'create_bms_form',
					__( 'Form Configuration', 'textdomain' ),
					array( $this, 'formio_render_meta_box_content' ),
					$post_type,
					'normal',
                    'high'
				);

				add_meta_box(
					'appointment_setting', // Unique ID
					__( 'Booking Configuration', 'textdomain' ),
					array( $this, 'bms_repeat_appointment' ),
					$post_type,
					'normal',
                    'high'
				);
			}
		}
        function zfb_edit_form_details($post){
            // echo $post_id;
            $form_id = get_post_meta( $post->ID, 'bms_form_id', true );	
            $form_schema = get_post_meta($form_id, '_my_meta_value_key', true);
            $form_data = get_post_meta($post->ID, 'bms_submission_data', true );
            //  echo "<pre>";print_r( $form_data );
			if ($form_schema) {
                ?>
               <div id="formio"></div>

                <script>
                var myScriptData = <?php echo $form_schema; ?>;                                                          
                var value = myScriptData;
                var entryData = <?php echo json_encode($form_data['data']); ?>; // Extract the form data from the entry data

                Formio.createForm(document.getElementById('formio'), {
                    components: value,
                    readOnly: false, // Enable editing
                    noAlerts: true, // Disable default Form.io alerts
                    options: {
                    noSubmit: true // Disable form submission
                    }
                }).then(function(form) {
                    form.setSubmission({
                    data: entryData // Set the pre-filled entry data
                    });
                    form.redraw();
                    form.on('submit', function(submission) {
                    event.preventDefault();

                    // Retrieve the entry ID
                    var entryId = <?php echo $post->ID; ?>;

                    // Retrieve the form field values from the submission
                    var updatedData = submission.data;

                    // Perform AJAX request to update the entry data in the post meta
                    jQuery.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'post',
                        data: {
                        action: 'update_form_entry_data', // AJAX action to handle the update
                        entry_id: entryId,
                        updated_data: updatedData
                        },
                        success: function(response) {
                        if (response.success) {
                            // Handle success message or redirect after update
                            console.log('Form data updated successfully');
                        } else {
                            // Handle error response
                            console.log('Failed to update form data');
                        }
                        },
                        error: function() {
                        // Handle AJAX error
                        console.log('Failed to update form data');
                        }
                    });

                    return false;
                    });

                });
                </script>

             <?php

            }
        }
        function timezone_dropdown($post_id){
            $get_timezone_value = get_post_meta( $post_id,'timezone',true);
            $dropdown_timezone = '<select name="timezone" id="zfb-timezone">
            <optgroup label="Africa">
            <option value="Africa/Abidjan">Abidjan</option>
            <option value="Africa/Accra">Accra</option>
            <option value="Africa/Addis_Ababa">Addis Ababa</option>
            <option value="Africa/Algiers">Algiers</option>
            <option value="Africa/Asmara">Asmara</option>
            <option value="Africa/Bamako">Bamako</option>
            <option value="Africa/Bangui">Bangui</option>
            <option value="Africa/Banjul">Banjul</option>
            <option value="Africa/Bissau">Bissau</option>
            <option value="Africa/Blantyre">Blantyre</option>
            <option value="Africa/Brazzaville">Brazzaville</option>
            <option value="Africa/Bujumbura">Bujumbura</option>
            <option value="Africa/Cairo">Cairo</option>
            <option value="Africa/Casablanca">Casablanca</option>
            <option value="Africa/Ceuta">Ceuta</option>
            <option value="Africa/Conakry">Conakry</option>
            <option value="Africa/Dakar">Dakar</option>
            <option value="Africa/Dar_es_Salaam">Dar es Salaam</option>
            <option value="Africa/Djibouti">Djibouti</option>
            <option value="Africa/Douala">Douala</option>
            <option value="Africa/El_Aaiun">El Aaiun</option>
            <option value="Africa/Freetown">Freetown</option>
            <option value="Africa/Gaborone">Gaborone</option>
            <option value="Africa/Harare">Harare</option>
            <option value="Africa/Johannesburg">Johannesburg</option>
            <option value="Africa/Juba">Juba</option>
            <option value="Africa/Kampala">Kampala</option>
            <option value="Africa/Khartoum">Khartoum</option>
            <option value="Africa/Kigali">Kigali</option>
            <option value="Africa/Kinshasa">Kinshasa</option>
            <option value="Africa/Lagos">Lagos</option>
            <option value="Africa/Libreville">Libreville</option>
            <option value="Africa/Lome">Lome</option>
            <option value="Africa/Luanda">Luanda</option>
            <option value="Africa/Lubumbashi">Lubumbashi</option>
            <option value="Africa/Lusaka">Lusaka</option>
            <option value="Africa/Malabo">Malabo</option>
            <option value="Africa/Maputo">Maputo</option>
            <option value="Africa/Maseru">Maseru</option>
            <option value="Africa/Mbabane">Mbabane</option>
            <option value="Africa/Mogadishu">Mogadishu</option>
            <option value="Africa/Monrovia">Monrovia</option>
            <option value="Africa/Nairobi">Nairobi</option>
            <option value="Africa/Ndjamena">Ndjamena</option>
            <option value="Africa/Niamey">Niamey</option>
            <option value="Africa/Nouakchott">Nouakchott</option>
            <option value="Africa/Ouagadougou">Ouagadougou</option>
            <option value="Africa/Porto-Novo">Porto-Novo</option>
            <option value="Africa/Sao_Tome">Sao Tome</option>
            <option value="Africa/Tripoli">Tripoli</option>
            <option value="Africa/Tunis">Tunis</option>
            <option value="Africa/Windhoek">Windhoek</option>
            </optgroup>
            <optgroup label="America">
            <option value="America/Adak">Adak</option>
            <option value="America/Anchorage">Anchorage</option>
            <option value="America/Anguilla">Anguilla</option>
            <option value="America/Antigua">Antigua</option>
            <option value="America/Araguaina">Araguaina</option>
            <option value="America/Argentina/Buenos_Aires">Argentina - Buenos Aires</option>
            <option value="America/Argentina/Catamarca">Argentina - Catamarca</option>
            <option value="America/Argentina/Cordoba">Argentina - Cordoba</option>
            <option value="America/Argentina/Jujuy">Argentina - Jujuy</option>
            <option value="America/Argentina/La_Rioja">Argentina - La Rioja</option>
            <option value="America/Argentina/Mendoza">Argentina - Mendoza</option>
            <option value="America/Argentina/Rio_Gallegos">Argentina - Rio Gallegos</option>
            <option value="America/Argentina/Salta">Argentina - Salta</option>
            <option value="America/Argentina/San_Juan">Argentina - San Juan</option>
            <option value="America/Argentina/San_Luis">Argentina - San Luis</option>
            <option value="America/Argentina/Tucuman">Argentina - Tucuman</option>
            <option value="America/Argentina/Ushuaia">Argentina - Ushuaia</option>
            <option value="America/Aruba">Aruba</option>
            <option value="America/Asuncion">Asuncion</option>
            <option value="America/Atikokan">Atikokan</option>
            <option value="America/Bahia">Bahia</option>
            <option value="America/Bahia_Banderas">Bahia Banderas</option>
            <option value="America/Barbados">Barbados</option>
            <option value="America/Belem">Belem</option>
            <option value="America/Belize">Belize</option>
            <option value="America/Blanc-Sablon">Blanc-Sablon</option>
            <option value="America/Boa_Vista">Boa Vista</option>
            <option value="America/Bogota">Bogota</option>
            <option value="America/Boise">Boise</option>
            <option value="America/Cambridge_Bay">Cambridge Bay</option>
            <option value="America/Campo_Grande">Campo Grande</option>
            <option value="America/Cancun">Cancun</option>
            <option value="America/Caracas">Caracas</option>
            <option value="America/Cayenne">Cayenne</option>
            <option value="America/Cayman">Cayman</option>
            <option value="America/Chicago">Chicago</option>
            <option value="America/Chihuahua">Chihuahua</option>
            <option value="America/Ciudad_Juarez">Ciudad Juarez</option>
            <option value="America/Costa_Rica">Costa Rica</option>
            <option value="America/Creston">Creston</option>
            <option value="America/Cuiaba">Cuiaba</option>
            <option value="America/Curacao">Curacao</option>
            <option value="America/Danmarkshavn">Danmarkshavn</option>
            <option value="America/Dawson">Dawson</option>
            <option value="America/Dawson_Creek">Dawson Creek</option>
            <option value="America/Denver">Denver</option>
            <option value="America/Detroit">Detroit</option>
            <option value="America/Dominica">Dominica</option>
            <option value="America/Edmonton">Edmonton</option>
            <option value="America/Eirunepe">Eirunepe</option>
            <option value="America/El_Salvador">El Salvador</option>
            <option value="America/Fortaleza">Fortaleza</option>
            <option value="America/Fort_Nelson">Fort Nelson</option>
            <option value="America/Glace_Bay">Glace Bay</option>
            <option value="America/Goose_Bay">Goose Bay</option>
            <option value="America/Grand_Turk">Grand Turk</option>
            <option value="America/Grenada">Grenada</option>
            <option value="America/Guadeloupe">Guadeloupe</option>
            <option value="America/Guatemala">Guatemala</option>
            <option value="America/Guayaquil">Guayaquil</option>
            <option value="America/Guyana">Guyana</option>
            <option value="America/Halifax">Halifax</option>
            <option value="America/Havana">Havana</option>
            <option value="America/Hermosillo">Hermosillo</option>
            <option value="America/Indiana/Indianapolis">Indiana - Indianapolis</option>
            <option value="America/Indiana/Knox">Indiana - Knox</option>
            <option value="America/Indiana/Marengo">Indiana - Marengo</option>
            <option value="America/Indiana/Petersburg">Indiana - Petersburg</option>
            <option value="America/Indiana/Tell_City">Indiana - Tell City</option>
            <option value="America/Indiana/Vevay">Indiana - Vevay</option>
            <option value="America/Indiana/Vincennes">Indiana - Vincennes</option>
            <option value="America/Indiana/Winamac">Indiana - Winamac</option>
            <option value="America/Inuvik">Inuvik</option>
            <option value="America/Iqaluit">Iqaluit</option>
            <option value="America/Jamaica">Jamaica</option>
            <option value="America/Juneau">Juneau</option>
            <option value="America/Kentucky/Louisville">Kentucky - Louisville</option>
            <option value="America/Kentucky/Monticello">Kentucky - Monticello</option>
            <option value="America/Kralendijk">Kralendijk</option>
            <option value="America/La_Paz">La Paz</option>
            <option value="America/Lima">Lima</option>
            <option value="America/Los_Angeles">Los Angeles</option>
            <option value="America/Lower_Princes">Lower Princes</option>
            <option value="America/Maceio">Maceio</option>
            <option value="America/Managua">Managua</option>
            <option value="America/Manaus">Manaus</option>
            <option value="America/Marigot">Marigot</option>
            <option value="America/Martinique">Martinique</option>
            <option value="America/Matamoros">Matamoros</option>
            <option value="America/Mazatlan">Mazatlan</option>
            <option value="America/Menominee">Menominee</option>
            <option value="America/Merida">Merida</option>
            <option value="America/Metlakatla">Metlakatla</option>
            <option value="America/Mexico_City">Mexico City</option>
            <option value="America/Miquelon">Miquelon</option>
            <option value="America/Moncton">Moncton</option>
            <option value="America/Monterrey">Monterrey</option>
            <option value="America/Montevideo">Montevideo</option>
            <option value="America/Montserrat">Montserrat</option>
            <option value="America/Nassau">Nassau</option>
            <option value="America/New_York">New York</option>
            <option value="America/Nome">Nome</option>
            <option value="America/Noronha">Noronha</option>
            <option value="America/North_Dakota/Beulah">North Dakota - Beulah</option>
            <option value="America/North_Dakota/Center">North Dakota - Center</option>
            <option value="America/North_Dakota/New_Salem">North Dakota - New Salem</option>
            <option value="America/Nuuk">Nuuk</option>
            <option value="America/Ojinaga">Ojinaga</option>
            <option value="America/Panama">Panama</option>
            <option value="America/Paramaribo">Paramaribo</option>
            <option value="America/Phoenix">Phoenix</option>
            <option value="America/Port-au-Prince">Port-au-Prince</option>
            <option value="America/Port_of_Spain">Port of Spain</option>
            <option value="America/Porto_Velho">Porto Velho</option>
            <option value="America/Puerto_Rico">Puerto Rico</option>
            <option value="America/Punta_Arenas">Punta Arenas</option>
            <option value="America/Rankin_Inlet">Rankin Inlet</option>
            <option value="America/Recife">Recife</option>
            <option value="America/Regina">Regina</option>
            <option value="America/Resolute">Resolute</option>
            <option value="America/Rio_Branco">Rio Branco</option>
            <option value="America/Santarem">Santarem</option>
            <option value="America/Santiago">Santiago</option>
            <option value="America/Santo_Domingo">Santo Domingo</option>
            <option value="America/Sao_Paulo">Sao Paulo</option>
            <option value="America/Scoresbysund">Scoresbysund</option>
            <option value="America/Sitka">Sitka</option>
            <option value="America/St_Barthelemy">St Barthelemy</option>
            <option value="America/St_Johns">St Johns</option>
            <option value="America/St_Kitts">St Kitts</option>
            <option value="America/St_Lucia">St Lucia</option>
            <option value="America/St_Thomas">St Thomas</option>
            <option value="America/St_Vincent">St Vincent</option>
            <option value="America/Swift_Current">Swift Current</option>
            <option value="America/Tegucigalpa">Tegucigalpa</option>
            <option value="America/Thule">Thule</option>
            <option value="America/Tijuana">Tijuana</option>
            <option value="America/Toronto">Toronto</option>
            <option value="America/Tortola">Tortola</option>
            <option value="America/Vancouver">Vancouver</option>
            <option value="America/Whitehorse">Whitehorse</option>
            <option value="America/Winnipeg">Winnipeg</option>
            <option value="America/Yakutat">Yakutat</option>
            </optgroup>
            <optgroup label="Antarctica">
            <option value="Antarctica/Casey">Casey</option>
            <option value="Antarctica/Davis">Davis</option>
            <option value="Antarctica/DumontDUrville">DumontDUrville</option>
            <option value="Antarctica/Macquarie">Macquarie</option>
            <option value="Antarctica/Mawson">Mawson</option>
            <option value="Antarctica/McMurdo">McMurdo</option>
            <option value="Antarctica/Palmer">Palmer</option>
            <option value="Antarctica/Rothera">Rothera</option>
            <option value="Antarctica/Syowa">Syowa</option>
            <option value="Antarctica/Troll">Troll</option>
            <option value="Antarctica/Vostok">Vostok</option>
            </optgroup>
            <optgroup label="Arctic">
            <option value="Arctic/Longyearbyen">Longyearbyen</option>
            </optgroup>
            <optgroup label="Asia">
            <option value="Asia/Aden">Aden</option>
            <option value="Asia/Almaty">Almaty</option>
            <option value="Asia/Amman">Amman</option>
            <option value="Asia/Anadyr">Anadyr</option>
            <option value="Asia/Aqtau">Aqtau</option>
            <option value="Asia/Aqtobe">Aqtobe</option>
            <option value="Asia/Ashgabat">Ashgabat</option>
            <option value="Asia/Atyrau">Atyrau</option>
            <option value="Asia/Baghdad">Baghdad</option>
            <option value="Asia/Bahrain">Bahrain</option>
            <option value="Asia/Baku">Baku</option>
            <option value="Asia/Bangkok">Bangkok</option>
            <option value="Asia/Barnaul">Barnaul</option>
            <option value="Asia/Beirut">Beirut</option>
            <option value="Asia/Bishkek">Bishkek</option>
            <option value="Asia/Brunei">Brunei</option>
            <option value="Asia/Chita">Chita</option>
            <option value="Asia/Choibalsan">Choibalsan</option>
            <option value="Asia/Colombo">Colombo</option>
            <option value="Asia/Damascus">Damascus</option>
            <option value="Asia/Dhaka">Dhaka</option>
            <option value="Asia/Dili">Dili</option>
            <option value="Asia/Dubai">Dubai</option>
            <option value="Asia/Dushanbe">Dushanbe</option>
            <option value="Asia/Famagusta">Famagusta</option>
            <option value="Asia/Gaza">Gaza</option>
            <option value="Asia/Hebron">Hebron</option>
            <option value="Asia/Ho_Chi_Minh">Ho Chi Minh</option>
            <option value="Asia/Hong_Kong">Hong Kong</option>
            <option value="Asia/Hovd">Hovd</option>
            <option value="Asia/Irkutsk">Irkutsk</option>
            <option value="Asia/Jakarta">Jakarta</option>
            <option value="Asia/Jayapura">Jayapura</option>
            <option value="Asia/Jerusalem">Jerusalem</option>
            <option value="Asia/Kabul">Kabul</option>
            <option value="Asia/Kamchatka">Kamchatka</option>
            <option value="Asia/Karachi">Karachi</option>
            <option value="Asia/Kathmandu">Kathmandu</option>
            <option value="Asia/Khandyga">Khandyga</option>
            <option selected="selected" value="Asia/Kolkata">Kolkata</option>
            <option value="Asia/Krasnoyarsk">Krasnoyarsk</option>
            <option value="Asia/Kuala_Lumpur">Kuala Lumpur</option>
            <option value="Asia/Kuching">Kuching</option>
            <option value="Asia/Kuwait">Kuwait</option>
            <option value="Asia/Macau">Macau</option>
            <option value="Asia/Magadan">Magadan</option>
            <option value="Asia/Makassar">Makassar</option>
            <option value="Asia/Manila">Manila</option>
            <option value="Asia/Muscat">Muscat</option>
            <option value="Asia/Nicosia">Nicosia</option>
            <option value="Asia/Novokuznetsk">Novokuznetsk</option>
            <option value="Asia/Novosibirsk">Novosibirsk</option>
            <option value="Asia/Omsk">Omsk</option>
            <option value="Asia/Oral">Oral</option>
            <option value="Asia/Phnom_Penh">Phnom Penh</option>
            <option value="Asia/Pontianak">Pontianak</option>
            <option value="Asia/Pyongyang">Pyongyang</option>
            <option value="Asia/Qatar">Qatar</option>
            <option value="Asia/Qostanay">Qostanay</option>
            <option value="Asia/Qyzylorda">Qyzylorda</option>
            <option value="Asia/Riyadh">Riyadh</option>
            <option value="Asia/Sakhalin">Sakhalin</option>
            <option value="Asia/Samarkand">Samarkand</option>
            <option value="Asia/Seoul">Seoul</option>
            <option value="Asia/Shanghai">Shanghai</option>
            <option value="Asia/Singapore">Singapore</option>
            <option value="Asia/Srednekolymsk">Srednekolymsk</option>
            <option value="Asia/Taipei">Taipei</option>
            <option value="Asia/Tashkent">Tashkent</option>
            <option value="Asia/Tbilisi">Tbilisi</option>
            <option value="Asia/Tehran">Tehran</option>
            <option value="Asia/Thimphu">Thimphu</option>
            <option value="Asia/Tokyo">Tokyo</option>
            <option value="Asia/Tomsk">Tomsk</option>
            <option value="Asia/Ulaanbaatar">Ulaanbaatar</option>
            <option value="Asia/Urumqi">Urumqi</option>
            <option value="Asia/Ust-Nera">Ust-Nera</option>
            <option value="Asia/Vientiane">Vientiane</option>
            <option value="Asia/Vladivostok">Vladivostok</option>
            <option value="Asia/Yakutsk">Yakutsk</option>
            <option value="Asia/Yangon">Yangon</option>
            <option value="Asia/Yekaterinburg">Yekaterinburg</option>
            <option value="Asia/Yerevan">Yerevan</option>
            </optgroup>
            <optgroup label="Atlantic">
            <option value="Atlantic/Azores">Azores</option>
            <option value="Atlantic/Bermuda">Bermuda</option>
            <option value="Atlantic/Canary">Canary</option>
            <option value="Atlantic/Cape_Verde">Cape Verde</option>
            <option value="Atlantic/Faroe">Faroe</option>
            <option value="Atlantic/Madeira">Madeira</option>
            <option value="Atlantic/Reykjavik">Reykjavik</option>
            <option value="Atlantic/South_Georgia">South Georgia</option>
            <option value="Atlantic/Stanley">Stanley</option>
            <option value="Atlantic/St_Helena">St Helena</option>
            </optgroup>
            <optgroup label="Australia">
            <option value="Australia/Adelaide">Adelaide</option>
            <option value="Australia/Brisbane">Brisbane</option>
            <option value="Australia/Broken_Hill">Broken Hill</option>
            <option value="Australia/Darwin">Darwin</option>
            <option value="Australia/Eucla">Eucla</option>
            <option value="Australia/Hobart">Hobart</option>
            <option value="Australia/Lindeman">Lindeman</option>
            <option value="Australia/Lord_Howe">Lord Howe</option>
            <option value="Australia/Melbourne">Melbourne</option>
            <option value="Australia/Perth">Perth</option>
            <option value="Australia/Sydney">Sydney</option>
            </optgroup>
            <optgroup label="Europe">
            <option value="Europe/Amsterdam">Amsterdam</option>
            <option value="Europe/Andorra">Andorra</option>
            <option value="Europe/Astrakhan">Astrakhan</option>
            <option value="Europe/Athens">Athens</option>
            <option value="Europe/Belgrade">Belgrade</option>
            <option value="Europe/Berlin">Berlin</option>
            <option value="Europe/Bratislava">Bratislava</option>
            <option value="Europe/Brussels">Brussels</option>
            <option value="Europe/Bucharest">Bucharest</option>
            <option value="Europe/Budapest">Budapest</option>
            <option value="Europe/Busingen">Busingen</option>
            <option value="Europe/Chisinau">Chisinau</option>
            <option value="Europe/Copenhagen">Copenhagen</option>
            <option value="Europe/Dublin">Dublin</option>
            <option value="Europe/Gibraltar">Gibraltar</option>
            <option value="Europe/Guernsey">Guernsey</option>
            <option value="Europe/Helsinki">Helsinki</option>
            <option value="Europe/Isle_of_Man">Isle of Man</option>
            <option value="Europe/Istanbul">Istanbul</option>
            <option value="Europe/Jersey">Jersey</option>
            <option value="Europe/Kaliningrad">Kaliningrad</option>
            <option value="Europe/Kirov">Kirov</option>
            <option value="Europe/Kyiv">Kyiv</option>
            <option value="Europe/Lisbon">Lisbon</option>
            <option value="Europe/Ljubljana">Ljubljana</option>
            <option value="Europe/London">London</option>
            <option value="Europe/Luxembourg">Luxembourg</option>
            <option value="Europe/Madrid">Madrid</option>
            <option value="Europe/Malta">Malta</option>
            <option value="Europe/Mariehamn">Mariehamn</option>
            <option value="Europe/Minsk">Minsk</option>
            <option value="Europe/Monaco">Monaco</option>
            <option value="Europe/Moscow">Moscow</option>
            <option value="Europe/Oslo">Oslo</option>
            <option value="Europe/Paris">Paris</option>
            <option value="Europe/Podgorica">Podgorica</option>
            <option value="Europe/Prague">Prague</option>
            <option value="Europe/Riga">Riga</option>
            <option value="Europe/Rome">Rome</option>
            <option value="Europe/Samara">Samara</option>
            <option value="Europe/San_Marino">San Marino</option>
            <option value="Europe/Sarajevo">Sarajevo</option>
            <option value="Europe/Saratov">Saratov</option>
            <option value="Europe/Simferopol">Simferopol</option>
            <option value="Europe/Skopje">Skopje</option>
            <option value="Europe/Sofia">Sofia</option>
            <option value="Europe/Stockholm">Stockholm</option>
            <option value="Europe/Tallinn">Tallinn</option>
            <option value="Europe/Tirane">Tirane</option>
            <option value="Europe/Ulyanovsk">Ulyanovsk</option>
            <option value="Europe/Vaduz">Vaduz</option>
            <option value="Europe/Vatican">Vatican</option>
            <option value="Europe/Vienna">Vienna</option>
            <option value="Europe/Vilnius">Vilnius</option>
            <option value="Europe/Volgograd">Volgograd</option>
            <option value="Europe/Warsaw">Warsaw</option>
            <option value="Europe/Zagreb">Zagreb</option>
            <option value="Europe/Zurich">Zurich</option>
            </optgroup>
            <optgroup label="Indian">
            <option value="Indian/Antananarivo">Antananarivo</option>
            <option value="Indian/Chagos">Chagos</option>
            <option value="Indian/Christmas">Christmas</option>
            <option value="Indian/Cocos">Cocos</option>
            <option value="Indian/Comoro">Comoro</option>
            <option value="Indian/Kerguelen">Kerguelen</option>
            <option value="Indian/Mahe">Mahe</option>
            <option value="Indian/Maldives">Maldives</option>
            <option value="Indian/Mauritius">Mauritius</option>
            <option value="Indian/Mayotte">Mayotte</option>
            <option value="Indian/Reunion">Reunion</option>
            </optgroup>
            <optgroup label="Pacific">
            <option value="Pacific/Apia">Apia</option>
            <option value="Pacific/Auckland">Auckland</option>
            <option value="Pacific/Bougainville">Bougainville</option>
            <option value="Pacific/Chatham">Chatham</option>
            <option value="Pacific/Chuuk">Chuuk</option>
            <option value="Pacific/Easter">Easter</option>
            <option value="Pacific/Efate">Efate</option>
            <option value="Pacific/Fakaofo">Fakaofo</option>
            <option value="Pacific/Fiji">Fiji</option>
            <option value="Pacific/Funafuti">Funafuti</option>
            <option value="Pacific/Galapagos">Galapagos</option>
            <option value="Pacific/Gambier">Gambier</option>
            <option value="Pacific/Guadalcanal">Guadalcanal</option>
            <option value="Pacific/Guam">Guam</option>
            <option value="Pacific/Honolulu">Honolulu</option>
            <option value="Pacific/Kanton">Kanton</option>
            <option value="Pacific/Kiritimati">Kiritimati</option>
            <option value="Pacific/Kosrae">Kosrae</option>
            <option value="Pacific/Kwajalein">Kwajalein</option>
            <option value="Pacific/Majuro">Majuro</option>
            <option value="Pacific/Marquesas">Marquesas</option>
            <option value="Pacific/Midway">Midway</option>
            <option value="Pacific/Nauru">Nauru</option>
            <option value="Pacific/Niue">Niue</option>
            <option value="Pacific/Norfolk">Norfolk</option>
            <option value="Pacific/Noumea">Noumea</option>
            <option value="Pacific/Pago_Pago">Pago Pago</option>
            <option value="Pacific/Palau">Palau</option>
            <option value="Pacific/Pitcairn">Pitcairn</option>
            <option value="Pacific/Pohnpei">Pohnpei</option>
            <option value="Pacific/Port_Moresby">Port Moresby</option>
            <option value="Pacific/Rarotonga">Rarotonga</option>
            <option value="Pacific/Saipan">Saipan</option>
            <option value="Pacific/Tahiti">Tahiti</option>
            <option value="Pacific/Tarawa">Tarawa</option>
            <option value="Pacific/Tongatapu">Tongatapu</option>
            <option value="Pacific/Wake">Wake</option>
            <option value="Pacific/Wallis">Wallis</option>
            </optgroup>
            <optgroup label="UTC">
            <option value="UTC">UTC</option>
            </optgroup>
            <optgroup label="Manual Offsets">
            <option value="UTC-12">UTC-12</option>
            <option value="UTC-11.5">UTC-11:30</option>
            <option value="UTC-11">UTC-11</option>
            <option value="UTC-10.5">UTC-10:30</option>
            <option value="UTC-10">UTC-10</option>
            <option value="UTC-9.5">UTC-9:30</option>
            <option value="UTC-9">UTC-9</option>
            <option value="UTC-8.5">UTC-8:30</option>
            <option value="UTC-8">UTC-8</option>
            <option value="UTC-7.5">UTC-7:30</option>
            <option value="UTC-7">UTC-7</option>
            <option value="UTC-6.5">UTC-6:30</option>
            <option value="UTC-6">UTC-6</option>
            <option value="UTC-5.5">UTC-5:30</option>
            <option value="UTC-5">UTC-5</option>
            <option value="UTC-4.5">UTC-4:30</option>
            <option value="UTC-4">UTC-4</option>
            <option value="UTC-3.5">UTC-3:30</option>
            <option value="UTC-3">UTC-3</option>
            <option value="UTC-2.5">UTC-2:30</option>
            <option value="UTC-2">UTC-2</option>
            <option value="UTC-1.5">UTC-1:30</option>
            <option value="UTC-1">UTC-1</option>
            <option value="UTC-0.5">UTC-0:30</option>
            <option value="UTC+0">UTC+0</option>
            <option value="UTC+0.5">UTC+0:30</option>
            <option value="UTC+1">UTC+1</option>
            <option value="UTC+1.5">UTC+1:30</option>
            <option value="UTC+2">UTC+2</option>
            <option value="UTC+2.5">UTC+2:30</option>
            <option value="UTC+3">UTC+3</option>
            <option value="UTC+3.5">UTC+3:30</option>
            <option value="UTC+4">UTC+4</option>
            <option value="UTC+4.5">UTC+4:30</option>
            <option value="UTC+5">UTC+5</option>
            <option value="UTC+5.5">UTC+5:30</option>
            <option value="UTC+5.75">UTC+5:45</option>
            <option value="UTC+6">UTC+6</option>
            <option value="UTC+6.5">UTC+6:30</option>
            <option value="UTC+7">UTC+7</option>
            <option value="UTC+7.5">UTC+7:30</option>
            <option value="UTC+8">UTC+8</option>
            <option value="UTC+8.5">UTC+8:30</option>
            <option value="UTC+8.75">UTC+8:45</option>
            <option value="UTC+9">UTC+9</option>
            <option value="UTC+9.5">UTC+9:30</option>
            <option value="UTC+10">UTC+10</option>
            <option value="UTC+10.5">UTC+10:30</option>
            <option value="UTC+11">UTC+11</option>
            <option value="UTC+11.5">UTC+11:30</option>
            <option value="UTC+12">UTC+12</option>
            <option value="UTC+12.75">UTC+12:45</option>
            <option value="UTC+13">UTC+13</option>
            <option value="UTC+13.75">UTC+13:45</option>
            <option value="UTC+14">UTC+14</option>
            </optgroup>                         
            </select>';
            return $dropdown_timezone;
        }
        
	}			

	add_action( 'plugins_loaded', function() {
		PB()->admin = new PB_Admin_Fieldmeta;
	} );
}
?>
