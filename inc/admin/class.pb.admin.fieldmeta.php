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
//calid_5085_6_20_2023
//SELECT * FROM `wp_postmeta` WHERE (`meta_key` = '09:30 AM-10:30 AM' OR `meta_value` = '09:30 AM-10:30 AM') LIMIT 50
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
                <ul class="tab-navigation">
                    <li><a href="#tab1">General</a></li>
                    <li><a href="#tab2">Recurring Appointment</a></li>
                    <li><a href="#tab3">Confirmations</a></li>
                    <li><a href="#tab4">Notification</a></li>
                    <li><a href="#tab5">Preview</a></li>
                </ul>
                <!-- Tabination 1 content  -->            
                <div id="tab1" class="tab-content">
                    <h3>General</h3>
                    <label>
                    <input type="checkbox" name="enable_booking" value="1" <?php echo checked(1, $enable_booking, false); ?>>
                    Enable or disable booking form
                    </label>
                    <br>
                    <br>

                    <label><?php echo __('Select Weekdays : ', 'textdomain'); ?></label>
                    <input type="checkbox" name="weekdays[]" value="monday" <?php echo (is_array($weekdays) && in_array('monday', $weekdays)) ? 'checked' : ''; ?>> Monday
                    <input type="checkbox" name="weekdays[]" value="tuesday" <?php echo (is_array($weekdays) && in_array('tuesday', $weekdays)) ? 'checked' : ''; ?>> Tuesday
                    <input type="checkbox" name="weekdays[]" value="wednesday" <?php echo (is_array($weekdays) && in_array('wednesday', $weekdays)) ? 'checked' : ''; ?>> Wednesday
                    <input type="checkbox" name="weekdays[]" value="thursday" <?php echo (is_array($weekdays) && in_array('thursday', $weekdays)) ? 'checked' : ''; ?>> Thursday
                    <input type="checkbox" name="weekdays[]" value="friday" <?php echo (is_array($weekdays) && in_array('friday', $weekdays)) ? 'checked' : ''; ?>> Friday
                    <input type="checkbox" name="weekdays[]" value="saturday" <?php echo (is_array($weekdays) && in_array('saturday', $weekdays)) ? 'checked' : ''; ?>> Saturday
                    <input type="checkbox" name="weekdays[]" value="sunday" <?php echo (is_array($weekdays) && in_array('sunday', $weekdays)) ? 'checked' : ''; ?>> Sunday
                    <br>
                    <br>

                    <label><?php echo __('Appointment Type: ', 'textdomain'); ?></label>
                    <label><input type="radio" name="appointment_type" value="virtual" <?php if ($appointment_type == 'virtual') echo 'checked="checked"'; ?>> Virtual </label>
                    <label><input type="radio" name="appointment_type" value="physical" <?php if ($appointment_type == 'physical') echo 'checked="checked"'; ?>>Physical</label>
                    <br>

                    <?php if ($appointment_type == 'virtual') : ?>
                    <div class="vlink-container">
                        <label><?php echo __('Link: ', 'textdomain'); ?></label>
                        <input type="text" name="virtual_link" value="<?php echo esc_attr($virtual_link); ?>" pattern="https?://.+" style="width: 500px !important;" required>
                        <small class="validation-error" style="display:none;">Please enter a valid URL starting with http:// or https://</small>
                    </div>
                    <?php else : ?>
                    <div class="vlink-container hidden">
                        <label><?php echo __('Link: ', 'textdomain'); ?></label>
                        <input type="text" name="virtual_link" value="<?php echo esc_attr($virtual_link); ?>">
                    </div>
                    <?php endif; ?>


                    <br>
                    <label><?php echo __('Prefix Symbol : ', 'textdomain'); ?></label>
                    <input type="text" name="label_symbol" value="<?php echo esc_attr($symbol); ?>"><br>

                    <label><?php echo __('Cost : ', 'textdomain'); ?></label>
                    <input type="number" name="cost" value="<?php echo esc_attr($cost); ?>"><br>
                    <br>

                    <label><?php echo __('Select Date : ', 'textdomain'); ?></label>
                    <input type="date" name="selected_date" value="<?php echo esc_attr($selected_date); ?>"><br>
                    <br>


                    <label><?php echo __('Start Time: ', 'textdomain'); ?></label>
                    <input type="time" name="start_time" value="<?php echo isset($start_time) ? esc_attr($start_time) : ''; ?>" required><br>

                    <label><?php echo __('End Time: ', 'textdomain'); ?></label>
                    <input type="time" name="end_time" value="<?php echo isset($end_time) ? esc_attr($end_time) : ''; ?>" required>
                    <span class="validation-message" style="color: red;"></span><br>

                   
                      
                    <br>
                    <label><?php echo __('Timeslot Duration(hh:mm)', 'textdomain'); ?></label>
                    <input type="number" class="hours" name="timeslot_duration[hours]" min="0" max="23" placeholder="HH" value="<?php echo isset($timeslot_duration['hours']) ? esc_attr($timeslot_duration['hours']) : ''; ?>" required>
                    <span>:</span>
                    <input type="number" class="minutes" name="timeslot_duration[minutes]" min="0" max="59" placeholder="MM" value="<?php echo isset($timeslot_duration['minutes']) ? esc_attr($timeslot_duration['minutes']) : ''; ?>" required>
                   <span class="timeslot-validation-message" style="color: red;"></span>
                    <br>

                    <label><?php echo __('Timeslot Margin (Interval Gap in hh-mm): ', 'textdomain'); ?></label>
                    <input type="number" class="hours" name="steps_duration[hours]" min="0" max="23" placeholder="HH" value="<?php echo isset($steps_duration['hours']) ? esc_attr($steps_duration['hours']) : ''; ?>" required>
                    <span>:</span>
                    <input type="number" class="minutes" name="steps_duration[minutes]" min="0" max="59" placeholder="MM" value="<?php echo isset($steps_duration['minutes']) ? esc_attr($steps_duration['minutes']) : ''; ?>" required>
                    <!-- <span>:</span> -->
                    <!-- <input type="number" class="seconds" name="steps_duration[seconds]" min="0" max="59" placeholder="SS" value="<?php echo isset($steps_duration['seconds']) ? esc_attr($steps_duration['seconds']) : ''; ?>" required><br> -->
                    <br>
                
                    <!-- Booking per Timeslots -->
                    <label><?php echo __('No of Booking per Timeslots : ', 'textdomain'); ?></label>
                    <input type="number" name="no_of_booking" value="<?php echo esc_attr($no_of_booking); ?>"><br>
                    <br>
                    <!-- Add Breaks -->
                    <br><br>
                        <?php
                        // echo "<pre>";
                        // print_r($steps_duration);
                        // echo "<pre>";
                        // print_r($timeslot_duration);
                        ?>
                    <div class="breaktimeslot-repeater">
                        <label>Add Break Timeslots:</label>
                        <button type="button" class="add-breaktimeslot">Add Timeslot</button>
                        <?php foreach ($breaktimeslots as $index => $timeslot) : ?>
                            <div class="breaktimeslot">
                            <label>Start Time:</label>
                            <input type="time" name="breaktimeslots[<?php echo $index; ?>][start_time]" value="<?php echo esc_attr($timeslot['start_time']); ?>">
                            <br>
                            <label>End Time:</label>
                            <input type="time" name="breaktimeslots[<?php echo $index; ?>][end_time]" value="<?php echo esc_attr($timeslot['end_time']); ?>">
                            
                                <button type="button" class="remove-breaktimeslot">Remove Timeslot</button>
                           
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <br>
                    <!-- waiting List -->
                    
                    <label>
                    <input type="checkbox" name="waiting_list" value="1" <?php echo checked(1, $enable_waiting, false); ?>>
                        Allow Waiting List
                    </label>
                    <br>
                    <br>
                    <!-- Allow Auto Approve -->
                    <label>
                    <input type="checkbox" name="enable_auto_approve" value="1" <?php echo checked(1, $enable_auto_approve, false); ?>>
                        Allow Auto Approve
                    </label>
                    
                </div>
           
                <!-- Tabination 2 content  -->
                <div id="tab2" class="tab-content">               
                    <h3>Recurring Appointment</h3>
                    <label><input type="checkbox" id="enable_recurring_apt_i" name="enable_recurring_apt" value="1" <?php echo checked(1, $enable_recurring_apt, false); ?>> Enable Recurring Bookings</label><br> <br>
                    <!-- hide and show whole container on enable and disable button -->
                    <?php if ($enable_recurring_apt) : ?>
                        <div id="recurring_result">
                    <?php else : ?>
                        <div id="recurring_result" style="display: none;">
                    <?php endif; ?>
                        <label>Repeat :</label>
                        <select name="recurring_type" id="recurring_type">
                            <option value="any" <?php echo selected('any', $recurring_type, false); ?>>Select Any</option>
                            <option value="daily" <?php echo selected('daily', $recurring_type, false); ?>>Daily</option>
                            <option value="weekend" <?php echo selected('weekend', $recurring_type, false); ?>>Every Weekend</option>
                            <option value="weekdays" <?php echo selected('weekdays', $recurring_type, false); ?>>Every Weekday</option>
                            <option value="certain_weekdays" <?php echo selected('certain_weekdays', $recurring_type, false); ?>>Certain Weekdays</option>
                            <option value="advanced" <?php echo selected('advanced', $recurring_type, false); ?>>Advanced</option>
                        </select>
                        <br><br>

                        <div id="certain_weekdays_fields" style="display: none;">
                            <label>Select Weekdays:</label>
                            <input type="checkbox" name="recur_weekdays[]" value="monday" <?php echo (is_array($recur_weekdays) && in_array('monday', $recur_weekdays)) ? 'checked' : ''; ?>> Monday
                            <input type="checkbox" name="recur_weekdays[]" value="tuesday" <?php echo (is_array($recur_weekdays) && in_array('tuesday', $recur_weekdays)) ? 'checked' : ''; ?>> Tuesday
                            <input type="checkbox" name="recur_weekdays[]" value="wednesday" <?php echo (is_array($recur_weekdays) && in_array('wednesday', $recur_weekdays)) ? 'checked' : ''; ?>> Wednesday
                            <input type="checkbox" name="recur_weekdays[]" value="thursday" <?php echo (is_array($recur_weekdays) && in_array('thursday', $recur_weekdays)) ? 'checked' : ''; ?>> Thursday
                            <input type="checkbox" name="recur_weekdays[]" value="friday" <?php echo (is_array($recur_weekdays) && in_array('friday', $recur_weekdays)) ? 'checked' : ''; ?>> Friday
                            <input type="checkbox" name="recur_weekdays[]" value="saturday" <?php echo (is_array($recur_weekdays) && in_array('saturday', $recur_weekdays)) ? 'checked' : ''; ?>> Saturday
                            <input type="checkbox" name="recur_weekdays[]" value="sunday" <?php echo (is_array($recur_weekdays) && in_array('sunday', $recur_weekdays)) ? 'checked' : ''; ?>> Sunday
                        </div>
                       
                        <div id="advance-meta-box">
                            <button type="button" id="add-row">Add Date</button>
                            <?php foreach ($advancedata as $index => $data) { ?>
                                <div class="repeater-row">
                                    <label for="advance_date_<?php echo $index; ?>">Advance Date:</label>
                                    <input type="date" id="advance_date_<?php echo $index; ?>" name="advancedata[<?php echo $index; ?>][advance_date]" value="<?php echo esc_attr($data['advance_date']); ?>">
                                    <div class="timeslot-repeater timeslot-container">
                                        <label>Advance Timeslots:</label>
                                        <?php foreach ($data['advance_timeslot'] as $slot_index => $timeslot) { ?>
                                            <div class="timeslot-row">
                                                <label>Start Time:</label>
                                                <input type="time" name="advancedata[<?php echo $index; ?>][advance_timeslot][<?php echo $slot_index; ?>][start_time]" value="<?php echo esc_attr($timeslot['start_time']); ?>">
                                                <label>End Time:</label>
                                                <input type="time" name="advancedata[<?php echo $index; ?>][advance_timeslot][<?php echo $slot_index; ?>][end_time]" value="<?php echo esc_attr($timeslot['end_time']); ?>">
                                                <label>Bookings:</label>
                                                <input type="number" name="advancedata[<?php echo $index; ?>][advance_timeslot][<?php echo $slot_index; ?>][bookings]" value="<?php echo esc_attr($timeslot['bookings']); ?>">
                                                <button type="button" class="remove-timeslot">Remove Timeslot</button>
                                            </div>
                                        <?php } ?>
                                        <button type="button" class="add-timeslot">Add Timeslot</button>
                                    </div>
                                    <button type="button" class="remove-row">Remove Date</button>
                                </div>
                            <?php } ?>
                        </div>
                
                        <br><br>
                        <div class="holiday-repeater">
                            <label> Add Holidays: </label>
                            <?php if ($holiday_dates && is_array($holiday_dates)): ?>
                                <?php foreach ($holiday_dates as $holydate): ?>
                                    <div class="holidate-field">
                                        <input type="date" name="holidays[]" value="<?php echo esc_attr($holydate); ?>">
                                        <button type="button" class="remove-holidate">Remove Holiday</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="add-holidate">Add Holidays</button>
                        <br>
                        <br>
                        <label class="end_repeats_label">End Repeats:</label>
                        <br>
                        <div class="end_repeats_options">
                            <input type="radio" name="end_repeats" value="never" <?php echo checked('never', $end_repeats, false); ?>> Never
                            <br>
                            <input type="radio" name="end_repeats" value="on" <?php echo checked('on', $end_repeats, false); ?>> On
                            <input type="date" name="end_repeats_on" value="<?php echo esc_attr($end_repeats_on); ?>">
                            <br>
                        </div>
                       
                    </div>
                    
                </div>
                <div id="tab3" class="tab-content"> 
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
                    <h3>Confirmation Type</h3>
                    <div class="">
                        <input type="radio" name="confirmation" value="redirect_text" <?php if ($confirmation == 'redirect_text') echo 'checked="checked"'; ?>> Text<br>
                    </div> 
                    <div class="">
                        <input type="radio" name="confirmation" value="redirect_page" <?php if ($confirmation == 'redirect_page') echo 'checked="checked"'; ?>> Page<br> 
                    </div> 
                    <div class="">
                        <input type="radio" name="confirmation" value="redirect_to" <?php if ($confirmation == 'redirect_to') echo 'checked="checked"'; ?>> Redirect to<br>  
                    </div> 
                    <!-- Class is used for on change event display div: redirectto_main redirect_page , redirectto_main redirect_text, redirectto_main redirect_to -->
                    <div class="redirectto_main redirect_text text_zfb <?php echo $hiddenredirect_text; ?>">
                        <?php
                            wp_editor($redirect_text, 'redirect_text', array(
                                'textarea_name' => 'redirect_text',
                            ));
                        ?>
                    </div>
                    <div class="redirectto_main redirect_page page_zfb <?php echo $hiddenredirect_page; ?> ">
                        <label>Select a page:</label>
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
                    <div class="redirectto_main redirect_to redirect_zfb <?php //echo $hiddenredirect_to; ?>">
                        <label><?php //echo __('Url: ', 'textdomain'); ?></label>
                        <input type="text" name="redirect_to" id="redirect-url"   value="<?php echo esc_attr($redirect_to); ?>" pattern="https?://.+" style="width: 500px !important;" placeholder="Enter url with http or https">
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
                <div id="tab4" class="tab-content">
                    <h3>Notifcation</h3>
                    <?php 
                    $get_no_of_notification = get_post_meta($post_id,'no_of_notification',true);
                    if(empty($get_no_of_notification)){
                        ?>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-success btn-success" data-bs-toggle="modal" data-bs-target="#newnotication">
                        New Notification
                        </button>

                        <!-- Modal -->
                        <div class="modal fade mod_notification" id="newnotication" tabindex="-1" aria-labelledby="newnoticationLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content mod_notification_content">
                                <div class="modal-header mod_notification_header">
                                <h5>Add New Email Notification</h5>
                                <!-- <h1 class="modal-title fs-5 mod_notification_title" id="newnoticationLabel">Add New Notification</h1> -->
                                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
                                </div>
                                <div class="modal-body mod_notification_body">
                                    <a class="h5" data-bs-toggle="collapse" href="#notifycollapse1" role="button" aria-expanded="false" aria-controls="notifycollapse1">
                                            Notification Setting <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                                    <div class="row notify-row">
                                        <div class="col notify-col">
                                            <div class="collapse multi-collapse notify-multi-collapse" id="notifycollapse1">
                                            <fieldset id="form-fieldset" class="form-related-slug">
                                                    <div class="form-group">
                                                        <label for="from-field">Name</label>
                                                        <input type="text" id="from-field" name="name" class="form-control" placeholder="Enter Title of Notification"required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Choose State whether notification is enabled and sending messages or 
                                                            it is disabled and no messages are sent until you activate the notification.
                                                        </label>
                                                        <div class="form-check">
                                                            <input class="form-control" type="radio" name="state" id="disable" value="disable">
                                                            <label class="form-check-label" for="disable">Disable</label>
                                                            </div>
                                                        <div class="form-check">
                                                            <input class="form-control" type="radio" name="state" id="enable" value="enable">
                                                            <label class="form-check-label" for="enable">Enable</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="appointment-status">Appointment Status</label>
                                                        <select class="form-select form-control" id="appointment-status" name="appointment_status">
                                                            <option value="booked">Booked</option>
                                                            <option value="cancelled">Cancelled</option>
                                                            <option value="approved">Approved</option>
                                                        </select>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="h5" data-bs-toggle="collapse" href="#notifycollapse2" role="button" aria-expanded="false" aria-controls="notifycollapse2">
                                    Email Setting <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                                    
                                    <div class="row notify-row">
                                        <div class="col notify-col">
                                            <div class="collapse multi-collapse notify-multi-collapse" id="notifycollapse2">
                                                <div class="notify-card-body">
                                                <fieldset id="form-fieldset" class="form-related-slug">
                                                    <div class="form-group">
                                                        <label for="from-field">From</label>
                                                        <input type="text" id="from-field" name="from" class="form-control" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="subject-field">Subject</label>
                                                        <input type="text" id="subject-field" name="subject" class="form-control" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="additional-headers-field">Additional Headers</label>
                                                        <!-- <input type="text" id="additional-headers-field" name="additional_headers" class="form-control"> -->
                                                        <textarea id="message-body-field" name="additional_headers" class="form-control" rows="4" required></textarea>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="message-body-field">Message Body</label>
                                                        <?php
                                                            wp_editor('', 'message_body', array(
                                                                'textarea_name' => 'message_body',
                                                            ));
                                                        ?>
                                                        <!-- <textarea id="message-body-field" name="message_body" class="form-control" rows="4" required></textarea> -->
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <input type="checkbox" id="exclude-blank-lines-checkbox" name="exclude_blank_lines" value="1">
                                                        <label for="exclude-blank-lines-checkbox">Exclude lines with blank mail-tags from output</label>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <input type="checkbox" id="use-html-content-checkbox" name="use_html_content" value="1">
                                                        <label for="use-html-content-checkbox">Use HTML content type</label>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="file-attachments-field">File Attachments</label>
                                                        <input type="file" id="file-attachments-field" name="file_attachments[]" multiple>
                                                    </div>
                                                </fieldset>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer mod_notification_footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary">Save changes</button>
                                </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }else{
                        ?>
                        <table class="table">
                        <thead>
                            <tr>
                            <th scope="col">#</th>
                            <th scope="col">First</th>
                            <th scope="col">Last</th>
                            <th scope="col">Handle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <th scope="row">1</th>
                            <td>Mark</td>
                            <td>Otto</td>
                            <td>@mdo</td>
                            </tr>
                            <tr>
                            <th scope="row">2</th>
                            <td>Jacob</td>
                            <td>Thornton</td>
                            <td>@fat</td>
                            </tr>
                            <tr>
                            <th scope="row">3</th>
                            <td colspan="2">Larry the Bird</td>
                            <td>@twitter</td>
                            </tr>
                        </tbody>
                        </table><?php
                    }
                    ?>
                    
                    <!-- <fieldset id="form-fieldset" class="form-related-slug">
                        <legend>Form Fields</legend>
                        
                        <div class="form-group">
                            <label for="from-field">From</label>
                            <input type="text" id="from-field" name="from" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject-field">Subject</label>
                            <input type="text" id="subject-field" name="subject" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="additional-headers-field">Additional Headers</label>
                            <input type="text" id="additional-headers-field" name="additional_headers" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="message-body-field">Message Body</label>
                            <textarea id="message-body-field" name="message_body" class="form-control" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <input type="checkbox" id="exclude-blank-lines-checkbox" name="exclude_blank_lines" value="1">
                            <label for="exclude-blank-lines-checkbox">Exclude lines with blank mail-tags from output</label>
                        </div>
                        
                        <div class="form-group">
                            <input type="checkbox" id="use-html-content-checkbox" name="use_html_content" value="1">
                            <label for="use-html-content-checkbox">Use HTML content type</label>
                        </div>
                        
                        <div class="form-group">
                            <label for="file-attachments-field">File Attachments</label>
                            <input type="file" id="file-attachments-field" name="file_attachments[]" multiple>
                        </div>
                    </fieldset> -->

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
