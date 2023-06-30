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
        function bms_entries_render_meta_box_content( $post ){
            
            $form_data = get_post_meta( $post->ID, 'bms_submission_data', true );	
            $form_id = get_post_meta( $post->ID, 'bms_form_id', true );	
            $timeslot = get_post_meta( $post->ID, 'timeslot', true );
            // echo "<br>".	
            $booking_date = get_post_meta( $post->ID, 'booking_date', true );
            $array_of_date = explode('_',$booking_date);
            // echo "<pre>";
            // print_r($array_of_date);
            $bookedmonth = $array_of_date[2];
            $bookedday =$array_of_date[3];
            $bookedyear =$array_of_date[4];
            $booked_date = $bookedday."-".$bookedmonth."-".$bookedyear;
            // $totalbookings = get_post_meta( $post->ID, 'totalbookings', true );	
            $slotcapacity = get_post_meta( $post->ID, 'slotcapacity', true );	

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
            <h3>General</h3>
            <ul>
                <li><?php echo __('Form Title', 'textdomain')." : ".$booking_form_title; ?></li>
                <li><?php echo __('Date Generated', 'textdomain')." : ".$date_generated; ?></li>
                <li><?php echo __('Status', 'textdomain')." : ".$status; ?></li>
                <li><?php echo __('Customer', 'textdomain'); ?> : <?php echo __('Guest', 'textdomain');; ?></li>
                <li><?php echo __('Booking Date', 'textdomain'); ?> : <?php echo __($booked_date, 'textdomain');; ?></li>
                <li><?php echo __('Timeslot', 'textdomain'); ?> : <?php echo __($timeslot, 'textdomain'); ?></li>
                <li><?php echo __('No of Slots Booked', 'textdomain'); ?> : <?php echo __($slotcapacity, 'textdomain'); ?></li>
            </ul>   
            <h3>Booking Details</h3>
            <ul>
                <?php
                foreach($form_data['data'] as $form_key => $form_value){
                    if($form_key !== 'submit'){
                        echo "<li>".$form_key." : ".$form_value."</li>";
                    }
                }
                ?>
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
            $title = get_post_meta($post->ID, 'title', true);
            $description = get_post_meta($post->ID, 'description', true);
            $enable_booking = get_post_meta($post->ID, 'enable_booking', true);
            $weekdays = get_post_meta($post->ID, 'weekdays', true);
            // $weekend = get_post_meta($post->ID, 'weekend', true);
                        
            $appointment_type = get_post_meta($post->ID, 'appointment_type', true);
            $virtual_link = get_post_meta($post->ID, 'virtual_link', true);
            $redirect_to= get_post_meta($post->ID, 'redirect_to', true);
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
            
            //section 3
           
            $redirect_url = get_post_meta($post->ID, 'redirect_url', true);
            $redirect_page = get_post_meta($post->ID, 'redirect_page', true);
            $redirect_text = get_post_meta($post->ID, 'redirect_text', true);
            $confirmation = get_post_meta($post->ID, 'confirmation', true);  
            ?>
            <div id="custom-meta-box-tabs">
                <!-- Tab navigations -->
                <ul class="tab-navigation nav nav-tabs">
                    
                    <li class="nav-link"><a href="#tab1">General</a></li>
                    <li class="nav-link"><a href="#tab2">Timeslots</a></li>
                    <li class="nav-link"><a href="#tab3">Recurring Appointment</a></li>
                    <li class="nav-link"><a href="#tab4">Confirmations</a></li>
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
                                    <input class="form-control" type="text" name="title" value="<?php echo esc_attr($title); ?>" width="30px" >
                                </div>
                                <div class="form-group form-general-group">
                                    <!--Timezone -->
                                    <label for="timezone"  class="h6">Description</label>
                                    <textarea class="form-control" rows="3" cols="50"><?php echo $description; ?></textarea>
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
                                    <input class="form-control" type="text" name="timezone" value="<?php echo esc_attr($timezone); ?>" >
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
                            <div class="form-group form-general-group col-md-3">
                                    <select class="form-control " name="recurring_type" id="recurring_type">
                                        <option value="any" <?php echo selected('any', $recurring_type, false); ?>>Select Any</option>
                                        <option value="daily" <?php echo selected('daily', $recurring_type, false); ?>>Daily</option>
                                        <option value="weekend" <?php echo selected('weekend', $recurring_type, false); ?>>Every Weekend</option>
                                        <option value="weekdays" <?php echo selected('weekdays', $recurring_type, false); ?>>Every Weekday</option>
                                        <option value="certain_weekdays" <?php echo selected('certain_weekdays', $recurring_type, false); ?>>Certain Weekdays</option>
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
                                <label class="h6">Add Date Field Group</label>
                                <svg id="add-row" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                </svg>
                                <?php foreach ($advancedata as $index => $data) { ?>
                                    <div class="repeater-row border-left m-1 ">
                                        <div class="form-group col-md-3">
                                            <label for="advance_date_<?php echo $index; ?>">Advance Date:</label>
                                            <input type="date" class="form-control" id="advance_date_<?php echo $index; ?>" name="advancedata[<?php echo $index; ?>][advance_date]" value="<?php echo esc_attr($data['advance_date']); ?>">
                                        </div>
                                        <div class="timeslot-repeater timeslot-container "  id="timeslot-repeater-<?php echo $index; ?>">
                                            <svg class="add-timeslot" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                            </svg><label class="h6 ml-1">Add Timeslots </label>
                                            <?php foreach ($data['advance_timeslot'] as $slot_index => $timeslot) { ?>
                                                <div class="form-row timeslot-row ">
                                                    <div class="form-group col-md-2">
                                                        <label>Start Time:</label>
                                                        <input type="time" class="form-control" name="advancedata[<?php echo $index; ?>][advance_timeslot][<?php echo $slot_index; ?>][start_time]" value="<?php echo esc_attr($timeslot['start_time']); ?>">
                                                    </div>
                                                    <div class="form-group col-md-2"> 
                                                        <label>End Time:</label>
                                                        <input type="time" class="form-control" name="advancedata[<?php echo $index; ?>][advance_timeslot][<?php echo $slot_index; ?>][end_time]" value="<?php echo esc_attr($timeslot['end_time']); ?>">
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label>Bookings:</label>
                                                        <input type="number" class="form-control" name="advancedata[<?php echo $index; ?>][advance_timeslot][<?php echo $slot_index; ?>][bookings]" value="<?php echo esc_attr($timeslot['bookings']); ?>">
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                    <svg class="remove-timeslot" xmlns="http://www.w3.org/2000/svg" width="16" 
                                                        height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                                        <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <!-- <button type="btn button" class="add-timeslot btn btn-secondary">Add Timeslot</button> -->
                                        </div>
                                        <button type="btn button" class="remove-row btn btn-light">Remove Date</button>
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
                <div id="tab4" class="tab-content"> 
                    <?php
                    if ($confirmation == 'redirect_text'){
                        $hiddenredirect_to = 'hidden';
                        $hiddenredirect_page = 'hidden';
                        
                    }elseif($confirmation == 'redirect_page'){
                          $hiddenredirect_text = 'hidden';
                         $hiddenredirect_to = 'hidden';
                       
                    }elseif($confirmation == 'redirect_to'){
                          $hiddenredirect_text = 'hidden';
                         $hiddenredirect_page = 'hidden';
                       
                    }
                    if(empty($confirmation) || !isset($confirmation)){
                        $hiddenredirect_text = "hidden";
                        $hiddenredirect_page = "hidden";
                        $hiddenredirect_to = "hidden";
                    }
                    ?> 
                        <div class="form-check form-check-inline ">
                            <input  type="radio" name="confirmation" id="radioText" value="redirect_text" <?php if ($confirmation == 'redirect_text') echo 'checked="checked"'; ?>>
                            <label class="form-check-label" for="radioText">
                                Text
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input  type="radio" name="confirmation" id="radioPage" value="redirect_page" <?php if ($confirmation == 'redirect_page') echo 'checked="checked"'; ?>>
                            <label class="form-check-label" for="radioPage">
                                Page
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="confirmation" id="radioRedirect" value="redirect_to" <?php if ($confirmation == 'redirect_to') echo 'checked="checked"'; ?>>
                            <label class="form-check-label" for="radioRedirect">
                                Redirect to
                            </label>
                        </div>
                
                        <!-- Class is used for on change event display div: redirectto_main redirect_page , redirectto_main redirect_text, redirectto_main redirect_to -->
                        <div class="form-group redirectto_main redirect_text text_zfb <?php echo $hiddenredirect_text; ?> ">
                            <?php
                                wp_editor($redirect_text, 'redirect_text', array(
                                    'textarea_name' => 'redirect_text',
                                ));
                            ?>
                        </div>
                        <div class="form-group redirectto_main redirect_page page_zfb <?php echo $hiddenredirect_page; ?>  ">
                            <label  class="h6">Select a page:</label>
                            <input type="text" id="redirectpage-search" placeholder="Search...">
                            <select name="redirect_page" id="redirectpage-dropdown">
                                <option value="">Select a page</option>
                                <?php
                                $args = array(
                                    'post_type' => 'page',
                                    'posts_per_page' => -1,
                                    'orderby' => 'title',
                                    'order' => 'ASC'
                                );
                                $pages = get_posts($args);
                                foreach ($pages as $page) {
                                    $selected = '';
                                    $selected_page_id = get_post_meta(get_the_ID(), 'selected_page', true);
                                    if ($selected_page_id == $page->ID) {
                                        $selected = 'selected="selected"';
                                    }
                                    echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group redirectto_main redirect_to redirect_zfb <?php //echo $hiddenredirect_to; ?> ">
                            <label class="h6"><?php echo __('Enter Url: ', 'textdomain'); ?></label>
                            <input type="text" name="redirect_to" id="redirect-url" class="form-control" value="<?php echo esc_attr($redirect_to); ?>" pattern="https?://.+" style="width: 500px !important;" placeholder="Enter url with http or https">
                            <small class="redirecturl-error" style="display:none;">Please enter a valid URL starting with http:// or https://</small>
                        </div>  
                    
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
            if (isset($_POST['redirect_to'])) {
                $link_value = sanitize_text_field($_POST['redirect_to']);
                update_post_meta($post_id, 'redirect_to', $link_value);
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
            //section 3
            if (isset($_POST['confirmation'])) {
                $redirect_url = sanitize_text_field($_POST['confirmation']);
                update_post_meta($post_id, 'confirmation', $redirect_url);
            }
            if (isset($_POST['redirect_text'])) {
                $wp_editor_value = wp_kses_post($_POST['redirect_text']);
                update_post_meta($post_id, 'redirect_text', $wp_editor_value);
            }
            if (isset($_POST['redirect_page'])) {
                $redirect_page = sanitize_text_field($_POST['redirect_page']);
                update_post_meta($post_id, 'redirect_page', $redirect_page);
            }
            if (isset($_POST['redirect_url'])) {
                $redirect_url = sanitize_text_field($_POST['redirect_url']);
                update_post_meta($post_id, 'redirect_url', $redirect_url);
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
					__( 'Form Builder Library', 'textdomain' ),
					array( $this, 'bms_entries_render_meta_box_content' ),
					$post_type,
					'advanced',
					'high'
				);
			}

			$post_types = array( 'bms_forms');

			if ( in_array( $post_type, $post_types ) ) {

				add_meta_box(
					'create_bms_form',
					__( 'BMS Form', 'textdomain' ),
					array( $this, 'formio_render_meta_box_content' ),
					$post_type,
					'advanced',
					'high'
				);

				add_meta_box(
					'appointment_setting', // Unique ID
					__( 'Appointment Setting', 'textdomain' ),
					array( $this, 'bms_repeat_appointment' ),
					$post_type,
					'normal', // Context
					'default' // Priority
				);
			}
		}
        
	}			

	add_action( 'plugins_loaded', function() {
		PB()->admin = new PB_Admin_Fieldmeta;
	} );
}
?>
