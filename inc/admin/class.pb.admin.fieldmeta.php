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


	

        /**
         * Display BMS submission Entries
         */ 
        function bms_entries_render_meta_box_content( $post ){
        
            $form_data = get_post_meta( $post->ID, 'bms_submission_data', true );	
            $form_id = get_post_meta( $post->ID, 'bms_form_id', true );	
            if(!empty($form_id)){ 
                $booking_form_title = get_the_title($form_id); 
            }
            ?>
            <ul>
                <li><?php echo __('Form Title', 'textdomain')." : ".$booking_form_title; ?></li>
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

            $symbol = get_post_meta($post->ID, 'label_symbol', true);
            $cost = get_post_meta($post->ID, 'cost', true);
            
            $selected_date = get_post_meta($post->ID, 'selected_date', true);

            $start_time = get_post_meta( $post->ID, 'start_time', true );
            $end_time = get_post_meta( $post->ID, 'end_time', true );
            $timeslot_duration = get_post_meta($post->ID, 'timeslot_duration', true);
            $steps_duration = get_post_meta( $post->ID, 'steps_duration', true );
            $breaktimeslots = get_post_meta($post->ID, 'breaktimeslots', true);
          
            $btimes = get_post_meta( $post->ID, 'break_repeater_field', true );
            $no_of_booking = get_post_meta($post->ID, 'no_of_booking', true);  
            $holiday_dates = get_post_meta($post->ID, 'holiday_dates', true);
            $enable_waiting = get_post_meta($post->ID, 'waiting_list', true);
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
            // If no values are found, initialize an empty array
            if ( empty( $btimes ) ) {
                $btimes = array( array( 'hours' => '', 'minutes' => '', 'seconds' => '' ) );
            }
                      
            ?>
            <div id="custom-meta-box-tabs">
                <!-- Tab navigations -->
                <ul class="tab-navigation">
                    <li><a href="#tab1">General</a></li>
                    <li><a href="#tab2">Recurring Appointment</a></li>
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
                    <input type="number" class="hours" name="start_time[hours]" min="0" max="23" placeholder="HH" value="<?php echo isset($start_time['hours']) ? esc_attr($start_time['hours']) : ''; ?>" required>
                    <span>:</span>
                    <input type="number" class="minutes" name="start_time[minutes]" min="0" max="59" placeholder="MM" value="<?php echo isset($start_time['minutes']) ? esc_attr($start_time['minutes']) : ''; ?>" required>
                    <span>:</span>
                    <input type="number" class="seconds" name="start_time[seconds]" min="0" max="59" placeholder="SS" value="<?php echo isset($start_time['seconds']) ? esc_attr($start_time['seconds']) : ''; ?>" required><br>
                    <br>
                  
                    <label><?php echo __('End Time: ', 'textdomain'); ?></label>
                    <input type="number" class="hours" name="end_time[hours]" min="0" max="23" placeholder="HH" value="<?php echo isset($end_time['hours']) ? esc_attr($end_time['hours']) : ''; ?>" required>
                    <span>:</span>
                    <input type="number" class="minutes" name="end_time[minutes]" min="0" max="59" placeholder="MM" value="<?php echo isset($end_time['minutes']) ? esc_attr($end_time['minutes']) : ''; ?>" required>
                    <span>:</span>
                    <input type="number" class="seconds" name="end_time[seconds]" min="0" max="59" placeholder="SS" value="<?php echo isset($end_time['seconds']) ? esc_attr($end_time['seconds']) : ''; ?>" required>
                    <span class="validation-message" style="color: red;"></span><br>
                      
                    <br>
                    <label><?php echo __('Timeslot Duration: ', 'textdomain'); ?></label>
                    <input type="number" class="hours" name="timeslot_duration[hours]" min="0" max="23" placeholder="HH" value="<?php echo isset($timeslot_duration['hours']) ? esc_attr($timeslot_duration['hours']) : ''; ?>" required>
                    <span>:</span>
                    <input type="number" class="minutes" name="timeslot_duration[minutes]" min="0" max="59" placeholder="MM" value="<?php echo isset($timeslot_duration['minutes']) ? esc_attr($timeslot_duration['minutes']) : ''; ?>" required>
                    <span>:</span>
                    <input type="number" class="seconds" name="timeslot_duration[seconds]" min="0" max="59" placeholder="SS" value="<?php echo isset($timeslot_duration['seconds']) ? esc_attr($timeslot_duration['seconds']) : ''; ?>" required>
                    <br>
                    <span class="timeslot-validation-message" style="color: red;"></span>
                    <br>

                    <label><?php echo __('Timeslot Repeat Duration:: ', 'textdomain'); ?></label>
                    <input type="number" class="hours" name="steps_duration[hours]" min="0" max="23" placeholder="HH" value="<?php echo isset($steps_duration['hours']) ? esc_attr($steps_duration['hours']) : ''; ?>" required>
                    <span>:</span>
                    <input type="number" class="minutes" name="steps_duration[minutes]" min="0" max="59" placeholder="MM" value="<?php echo isset($steps_duration['minutes']) ? esc_attr($steps_duration['minutes']) : ''; ?>" required>
                    <span>:</span>
                    <input type="number" class="seconds" name="steps_duration[seconds]" min="0" max="59" placeholder="SS" value="<?php echo isset($steps_duration['seconds']) ? esc_attr($steps_duration['seconds']) : ''; ?>" required><br>
                    <br>
                
                    <!-- Booking per Timeslots -->
                    <label><?php echo __('No of Booking per Timeslots : ', 'textdomain'); ?></label>
                    <input type="number" name="no_of_booking" value="<?php echo esc_attr($no_of_booking); ?>"><br>
                    <br>

                    <!-- waiting List -->
                    
                    <label>
                    <input type="checkbox" name="waiting_list" value="1" <?php echo checked(1, $enable_waiting, false); ?>>
                        Allow Waiting List
                    </label>
                    <br>
                    <br>
           
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
             //Symbol
             if ( isset( $_POST['label_symbol'] ) ) {
                $selected_date = $_POST['label_symbol'];
                update_post_meta( $post_id, 'label_symbol', $selected_date );
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
                $sanitized_start_time = array(
                    'hours' => sanitize_text_field( $time_slot['hours'] ),
                    'minutes' => sanitize_text_field( $time_slot['minutes'] ),
                    'seconds' => sanitize_text_field( $time_slot['seconds'] ),
                );
    
                // Update the post meta data with the field value
                update_post_meta( $post_id, 'start_time', $sanitized_start_time );
            }
             //End Time
             if ( isset( $_POST['end_time'] ) ) {
                $time_slot = $_POST['end_time'];
                $sanitized_end_time = array(
                    'hours' => sanitize_text_field( $time_slot['hours'] ),
                    'minutes' => sanitize_text_field( $time_slot['minutes'] ),
                    'seconds' => sanitize_text_field( $time_slot['seconds'] ),
                );
        
                // Update the post meta data with the field value
                update_post_meta( $post_id, 'end_time', $sanitized_end_time );
            }
             //Steps Duration
             if ( isset( $_POST['steps_duration'] ) ) {
                $steps_duration = $_POST['steps_duration'];
                $sanitized_steps_duration = array(
                    'hours' => sanitize_text_field( $steps_duration['hours'] ),
                    'minutes' => sanitize_text_field( $steps_duration['minutes'] ),
                    'seconds' => sanitize_text_field( $steps_duration['seconds'] ),
                );
        
                // Update the post meta data with the field value
                update_post_meta( $post_id, 'steps_duration', $sanitized_steps_duration );
            }
             //timeslot_duration
             if ( isset( $_POST['timeslot_duration'] ) ) {
                $timeslot_duration = $_POST['timeslot_duration'];
                $sanitized_timeslot_duration = array(
                    'hours' => sanitize_text_field( $timeslot_duration['hours'] ),
                    'minutes' => sanitize_text_field( $timeslot_duration['minutes'] ),
                    'seconds' => sanitize_text_field( $timeslot_duration['seconds'] ),
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
                echo "<pre>";
                print_r($advancedata);
                
                update_post_meta($post_id, 'advancedata', $advancedata);
                exit;
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
