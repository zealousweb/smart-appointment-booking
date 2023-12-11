<?php
/**
 * SAAB_Admin_Fieldmeta Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Smart Appointment & Booking
 * @since 1.0
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'SAAB_Admin_Fieldmeta' ) ) {

    /**
     * The SAAB_Admin Class
     */
    class SAAB_Admin_Fieldmeta {
        function __construct() {
            
            add_action( 'add_meta_boxes', array( $this, 'saab_add_meta_box' ) ); 
            add_action( 'save_post', array( $this, 'saab_save_post_function' ) );
            add_action('save_post', array( $this, 'saab_save_notes_data' ) );
        }

        function saab_get_available_seats_per_timeslot($checktimeslot,$date){
            
            $args = array(
                'post_type' => 'manage_entries',
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
            }
            
            return $post_count;
        }
    

        /**
         * Display saab submission Entries
         */ 
        function saab_entries_render_meta_box_content( $post ){

            //Add a nonce field to the meta box.
            wp_nonce_field('saab_entries_nonce', 'saab_entries_nonce_field');

            $form_data = get_post_meta( $post->ID, 'saab_submission_data', true );   
            $form_id = get_post_meta( $post->ID, 'saab_form_id', true ); 
            $timeslot = get_post_meta( $post->ID, 'saab_timeslot', true );
            if(isset($timeslot) && !empty($timeslot)){
                $times = explode("-", $timeslot);
                $start_time = trim(gmdate("h:i", strtotime($times[0])));
                $end_time = trim(gmdate("h:i", strtotime($times[1])));
              
            }
           
            $booking_date = get_post_meta( $post->ID, 'saab_booking_date', true );
            if($booking_date && !empty($booking_date)){
                $array_of_date = explode('_', $booking_date);
            }
           
            if(isset($array_of_date) && !empty($array_of_date[2]) && !empty($array_of_date[3]) && !empty($array_of_date[4])){
                $bookedmonth = $array_of_date[2];
                $bookedday = $array_of_date[3];
                $bookedyear = $array_of_date[4];
                $booked_date = $bookedday . "-" . $bookedmonth . "-" . $bookedyear;
                $booked_date = gmdate('Y-m-d', strtotime($booked_date));
                $slotcapacity = get_post_meta( $post->ID, 'saab_slotcapacity', true );   
            }
           
            if(!empty($form_id)){ 
                $booking_form_title = get_the_title($form_id);               
            }
           
            $date_generated = get_the_date($post->ID);
            $status = get_post_meta( $post->ID, 'saab_entry_status', true );
           
            $post_id = $post->ID; 
            $title = get_the_title($post_id);
            echo '<div class="form-pair" style="margin-top:30px;">';
            echo '<p class="entry-title h5">' . esc_html($title) . '</p>';
            $published_date = get_the_date( 'F j, Y @ h:i a', $post_id );
            echo '<p class="published_on" style="font-size:18px;">Published on ' . esc_html($published_date) . '</p>';
            echo '</div>';
            ?>
            <div class="form-pair">
                <span style="font-size:20px;"  class="h6" style="font-weight: 800;">Form  </span> 
                <div class="value" style="font-size:18px;"><?php echo esc_html($booking_form_title); ?></div>
            </div>
            <?php
            $enable_booking = get_post_meta($form_id, 'saab_enable_booking', true);
            if( $enable_booking ){
                ?>
                <div class="saab-entry-details">
                
                    <div class="row">
                        <div class="col-4">                       
                            <div class="group-pair">
                                <p  class="h6">Status</p>
                                <div class="value">
                                    <select name="booking_status" class="form-control" id="custom_status">
                                        <?php 
                                            if($status === "confirmation" || $status === "completed" || $status === "booked" || $status === "pending" || $status === "submitted"  ){
                                                echo $selected = 'selected';
                                            }else{ 
                                                $selected = ''; 
                                            }
                                        ?>
                                        <option value="any">Status</option>
                                        <option value="booked" <?php echo $selected; ?>>Booked</option>
                                        <option value="approved" <?php echo ($status === "approved") ? 'selected' : ''; ?>>Approved</option>
                                        <option value="cancelled" <?php echo ($status === "cancelled") ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="pending" <?php echo ($status === "pending") ? 'selected' : ''; ?>>Pending</option>
                                        <option value="waiting" <?php echo ($status === "waiting") ? 'selected' : ''; ?>>Waiting</option>
                                        <option value="submitted" <?php echo ($status === "submitted") ? 'selected' : ''; ?>>Submitted</option>
                                    </select>
                                    <input type="hidden" name="form_id" value="<?php echo esc_attr($form_id); ?>">
                                </div>
                            </div>
                            <div class="group-pair">
                                <p class="h6">No of Bookings</p>
                                <div class="value">
                                    <input type="number" class="form-control" name="no_of_bookings" id="no_of_bookings" value="<?php echo esc_attr($slotcapacity); ?>">
                                </div>
                            </div>
                            <?php 
                             $symbol = get_post_meta($post->ID, 'saab_label_symbol', true);
                             $cost = get_post_meta($post->ID, 'saab_cost', true);
                             
                            if ($cost || $symbol) {                               
                            ?>
                            <div class="group-pair">
                                <p class="h6">Cost: <?php echo esc_html($symbol) . ' ' . esc_html($cost);?> </p>
                            </div>
                            <?php  } ?>
                            <?php 
                            $appointment_type = get_post_meta($post->ID, 'saab_appointment_type', true);                            
                            if ($appointment_type) {                               
                            ?>
                            <div class="group-pair">
                                <p  class="h6">Appointment Type: <?php echo esc_html($appointment_type);?> </p>
                               
                            </div>
                            <?php  } ?>
                        </div>
                        <div class="col-4">
                            <div class="group-pair">
                                <p class="h6">Booking Date</p>
                                <div class="value">
                                <input type="date" class="form-control" name="booking_date" id="no_of_bookings" value="<?php echo esc_attr($booked_date); ?>">

                                </div>

                            </div>
                            <div class="group-pair">
                                <p class="h6">Booked Timeslot</p>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <p  for="start_time" class="h6"><?php echo esc_html__('From: ', 'smart-appointment-booking'); ?></p>
                                        <input type="time" class="form-control" id="start_time" name="start_time" value="<?php echo isset($start_time) ? esc_attr($start_time) : ''; ?>" >
                                        
                                    </div>
                                    
                                    <div class="form-group col-md-4">
                                        <p for="end_time" class="h6"><?php echo esc_html__('To: ', 'smart-appointment-booking'); ?></p>
                                        <input type="time" class="form-control" name="end_time" value="<?php echo isset($end_time) ? esc_attr($end_time) : ''; ?>" >
                                    </div>
                                    <span class="validation-message" style="color: red;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="waitinglist_main">
                    <?php
                       
                        $current_page =  1;
                        $args = array(
                            'post_type' => 'manage_entries',
                            'posts_per_page' => 5, 
                            'paged' => $current_page,
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
                            echo '<div class="border-top section-break mb-2"></div>';
                            echo '<p class="h6">Waiting List</p>';
                            echo '<table class="table table-bordered waitingtable " style="width:70%;text-align: center;">';
                            echo '<tr>';
                            echo '<th style="width:3%">No</th>';
                            echo '<th style="width:10%">Post ID</th>';
                            echo '<th style="width:20%">Post Title</th>';
                            echo '<th style="width:20%">Status</th>';
                            echo '<th style="width:20%">No of Bookings</th>';
                            echo '<th style="width:25%">Published Date</th>';
                            echo '<th style="width:5%">Edit</th>';
                            echo '</tr>';
                            $i = 1;
                            while ($query->have_posts()) {
                                $query->the_post();
                                $post_id = get_the_ID();
                                $post_title = get_the_title();
                                $booking_status = get_post_meta($post_id, 'saab_entry_status', true);
                                $no_of_bookings = get_post_meta($post_id, 'saab_slotcapacity', true);

                                if ($booking_status === 'waiting') {
                                 
                                    // Start a table row (tr) for each record.
                                    echo '<tr>';
                                    echo '<td>' . esc_attr($i) . '</td>';
                                    echo '<td>' . esc_attr($post_id) . '</td>';
                                    echo '<td>' . esc_html($post_title) . '</td>';
                                    echo '<td>' . esc_html($booking_status) . '</td>';
                                    echo '<td>' . esc_html($no_of_bookings) . '</td>';
                                    echo '<td>' . esc_html(get_the_date('F j, Y @ h:i a', $post_id)) . '</td>';
                                    echo '<td><a href="' . esc_url(get_edit_post_link($post_id)) . '"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></td>';
                                    echo '</tr>';
                                   
                                    
                                }
                                $i++;
                            }

                            echo '</table>';

                           
                            wp_reset_postdata();
                        }
                        if ($query->have_posts()) {
                            // Calculate the total number of pages
                            $total_pages = $query->max_num_pages;
                            echo '<div id="pagination-links" style="font-size: 15px;font-weight: 600;">';
                            echo '<span class="item-count" style="margin-right: 5px;">' . esc_html($query->found_posts) . ' Items</span>';
                            if ($total_pages > 1) {
                                
                                    echo '<select id="saabpage-number"  data-timeslot="' . esc_attr($timeslot) . '" data-booking_date="' . esc_attr($booking_date) . '" data-nonce="'.wp_create_nonce('get_paginated_items_nonce').'">';
                                        for ($page = 1; $page <= $total_pages; $page++) {
                                            echo '<option value="' . esc_attr($page) . '"';
                                            if ($page == $current_page) {
                                                echo ' selected';
                                            }
                                            echo '>' . esc_html($page) . '</option>';
                                        }
                                    echo '</select>';
                                    echo '<span class="item-count" style="margin-right:5px;margin-left: 7px; font-size: 15px;font-weight: 600;">'; 
                                    echo esc_html__('of List Items ','smart-appointment-booking');
                                    echo esc_attr($total_pages);
                            
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <hr>
                </div>
                <?php
            }
        }

        /**
         * 
         * Form Configuration add metabox callback
         * 
        */ 
        function formio_render_meta_box_content( $post ) {
            
            wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );
            $fields = get_post_meta( $post->ID, 'saab_formschema', true );  
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
                            components: myScriptData 
                        });
                                                    
                        formioBuilder.then(function(builder) {
                          
                            builder.on('change', function(submission) {
                                formdata = JSON.stringify(submission.components);
                                var nonce = ajax_object.nonce;
                                jQuery.post(ajaxurl, {
                                    action: 'saab_save_form_data', 
                                    post_id: <?php echo esc_js($post->ID); ?>, 
                                    form_data: formdata ,
                                    security: nonce,                                     
                                }, function(response) {                                   
                                    console.log('success');
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
                                var nonce = ajax_object.nonce;
                                jQuery.post(ajaxurl, {
                                    action: 'saab_save_form_data', 
                                    post_id: <?php echo esc_js($post->ID); ?>, 
                                    form_data: formdata,
                                    security: nonce,                                  
                                }, function(response) {
                                    // console.log('success');
                                });
                            });
                        });
                    };
                </script>
                <?php
            }
            
        }
        /**
         * Booking Configuration - add meta box callback
         */
        function saab_repeat_appointment($post) {
           
            wp_nonce_field('saab_repeat_appointment_nonce', 'saab_repeat_appointment_nonce_field');
            // Retrieve saved meta box values
            $title = get_post_meta($post->ID, 'saab_cal_title', true);
            $description = get_post_meta($post->ID, 'saab_cal_description', true);
            $enable_booking = get_post_meta($post->ID, 'saab_enable_booking', true);
            $weekdays = get_post_meta($post->ID, 'saab_weekdays', true);
            $appointment_type = get_post_meta($post->ID, 'saab_appointment_type', true);
            $virtual_link = get_post_meta($post->ID, 'saab_virtual_link', true);
            $symbol = get_post_meta($post->ID, 'saab_label_symbol', true);
            $cost = get_post_meta($post->ID, 'saab_cost', true);
            $selected_date = get_post_meta($post->ID, 'saab_selected_date', true);
            $end_time = get_post_meta( $post->ID, 'saab_end_time', true );
            $timeslot_duration = get_post_meta($post->ID, 'saab_timeslot_duration', true);
            $steps_duration = get_post_meta( $post->ID, 'saab_steps_duration', true );
            $timezone = get_post_meta($post->ID,'saab_timezone',true);
            $no_of_booking = get_post_meta($post->ID, 'saab_no_of_booking', true);  
            $holiday_dates = get_post_meta($post->ID, 'saab_holiday_dates', true);
            $enable_waiting = get_post_meta($post->ID, 'saab_waiting_list', true);
            $start_time = get_post_meta( $post->ID, 'saab_start_time', true );
            $timeslot_BookAllow = get_post_meta($post->ID, 'saab_timeslot_BookAllow', true);
            $booking_stops_after = get_post_meta( $post->ID, 'saab_booking_stops_after', true );           
            $enable_auto_approve = get_post_meta($post->ID, 'saab_enable_auto_approve', true);

            $breaktimeslots = get_post_meta($post->ID, 'saab_breaktimeslots', true);
            if (empty($breaktimeslots)) {
                $breaktimeslots = array(
                array(
                    'start_time' => '',
                    'end_time' => '',
                ),
                );
            }
            $generatetimeslots = get_post_meta($post->ID, 'saab_generatetimeslot', true);
          
            //section 2 
            $enable_advance_setting = get_post_meta($post->ID, 'saab_enable_advance_setting', true);
            $enable_recurring_apt = get_post_meta($post->ID, 'saab_enable_recurring_apt', true);
            $recurring_type = get_post_meta($post->ID, 'saab_recurring_type', true);
            //advanced field
            $advancedata = get_post_meta($post->ID, 'saab_advancedata', true);
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
            $end_repeats = get_post_meta($post->ID, 'saab_end_repeats', true);
            $end_repeats_on = get_post_meta($post->ID, 'saab_end_repeats_on',true);
            $recur_weekdays = get_post_meta($post->ID, 'saab_recur_weekdays', true);
            ?>
            <div id="custom-meta-box-tabs">
                <!-- Tab navigations -->
                <ul class="tab-navigation nav nav-tabs">
                    
                    <li class="nav-link"><a href="#tab1">General</a></li>
                    <li class="nav-link"><a href="#tab2">Timeslots</a></li>
                    <li class="nav-link"><a href="#tab4">Advanced Selection</a></li>
                    <li class="nav-link"><a href="#tab3">Recurring Appointment</a></li>
                </ul>
                <!-- Tabination 1 content  -->            
                
                <div id="tab1" class="tab-content">
                    <div class="row">
                        <div class="col-6">
                            <!-- <div class=""> -->
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="enable_booking" id="enable_booking" value="1" <?php echo checked(1, $enable_booking, false); ?>>
                                    <label class="form-check-label h6" for="enable_booking"> Enable or disaable booking form</label>
                                </div>
                                <div class="form-group form-general-group">
                                    <label  for="cal_title" class="h6">Enter Calender Title</label>
                                    <input class="form-control" type="text" id="cal_title" name="cal_title" value="<?php echo esc_attr($title); ?>" width="30px" >
                                </div>
                                <div class="form-group form-general-group">
                                    <label for="timezone"  class="h6">Description</label>
                                    <textarea class="form-control" id="timezone" rows="3" cols="50" name="cal_description"><?php echo esc_textarea($description); ?></textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label  class="h6"><?php echo esc_html__('Prefix Symbol : ', 'smart-appointment-booking'); ?></label>
                                        <input type="text" class="form-control" name="label_symbol" value="<?php echo esc_attr($symbol); ?>">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label  class="h6"> <?php echo esc_html__('Cost : ', 'smart-appointment-booking'); ?></label>
                                        <input type="number" class="form-control" name="cost" value="<?php echo esc_attr($cost); ?>">
                                    </div>
                                </div>
                            <!-- </div> -->
                        </div>
                        <div class="col-6">
                                 <!-- <div class="card"> -->
                                <label class="h6"><?php echo esc_html__('Select Weekdays: ', 'smart-appointment-booking'); ?></label>
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
                                <div class="form-group form-general-group"><label  class="h6"><?php echo esc_html__('Appointment Type: ', 'smart-appointment-booking'); ?></label>

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
                                        <input type="text" class="form-control" id="virtual_link"  name="virtual_link" value="<?php echo esc_url($virtual_link); ?>" pattern="https?://.+" >
                                        <small class="validation-error form-text text-muted" style="display:none;">Please enter a valid URL starting with http:// or https://</small>
                                    </div>
                                    <?php else : ?>
                                        <div class="vlink-container hidden form-group form-general-group">
                                            <label for="virtual_link"  class="h6"><?php echo esc_html__('Link: ', 'smart-appointment-booking'); ?></label>
                                            <input type="text" class="form-control" id="virtual_link" name="virtual_link" value="<?php echo esc_url($virtual_link); ?>">
                                        </div>
                                    <?php endif; 
                                ?>
                                <div class="form-group form-general-group">
                                    <!--Timezone -->
                                    <label  for="timezone" class="h6">Timezone</label>
                                    <?php echo esc_html($this->timezone_dropdown($post->ID)); ?>
                                </div> 
                                <div class="form-group form-general-group">
                                    <label class="h6" for="bookemail-map">Map Booking Email:</label>
                                    <select class="form-control" id="bookemail-map" name="bookmap_email">
                                        <option value="any" >Any</option>
                                        <?php           
                                          $bookmap_email = get_post_meta($post->ID, 'saab_map_email', true);                                        
                                            // $fieldFirstName = do_action('get_shortcode_list', $post_id);
                                            $reg_lastName = $this->saab_admin_get_shortcodes_keylabel_second($post->ID);
                                            foreach ($reg_lastName as $option) {
                                                $fieldKey = $option['fieldkey'];
                                                $fieldLabel = $option['fieldlabel'];
                                                $selected = ($fieldKey == $bookmap_email) ? 'selected' : '';
                                                echo '<option value="' . esc_attr($fieldKey) . '" ' . esc_attr($selected) . '>' . esc_html($fieldLabel) . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                        </div>
                    </div>
                </div>
                <div id="tab2" class="tab-content">
                    <div class="">
                        <div class="form-group form-general-group ">
                            <label  class="h6"><?php echo esc_html__('Select Date : ', 'smart-appointment-booking'); ?></label>
                            <input type="date" class="form-control col-md-4" name="selected_date" value="<?php echo esc_attr($selected_date); ?>">
                        </div>
                        <div class="generatetimeslot-repeater  border m-0 mb-2 p-3">
                            <?php 
                           
                            ?>
                            <label  class="h6">Add/Update Generated Timeslots:</label>
                            <svg class="add-generatetimeslot" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                            </svg>
                            <?php 
                                foreach ($generatetimeslots as $index => $timeslot) : ?>                                 
                                    <div class="form-row timeslot-row generatetimeslot">
                                        <div class="form-group col-md-3">
                                            <label>Start Time:</label>
                                            <input type="time" class="form-control" name="generatetimeslot[<?php echo $index; ?>][start_time]" pattern="^(0[1-9]|1[0-2]):[0-5][0-9] (AM|PM)$" value="<?php echo esc_attr($timeslot['start_time']); ?>">
                                        </div>
                                        <div class="form-group col-md-3"> 
                                            <label>End Time:</label>
                                            <input type="time" class="form-control" name="generatetimeslot[<?php echo $index; ?>][end_time]" pattern="^(0[1-9]|1[0-2]):[0-5][0-9] (AM|PM)$" value="<?php echo esc_attr($timeslot['end_time']); ?>">                            
                                        </div>
                                        
                                        <div class="form-group col-2 remove-generatetimeslot">
                                            <button class="remove-generatetimeslot btn btn-danger">
                                            <svg class="remove-generatetimeslot" xmlns="http://www.w3.org/2000/svg" width="16" 
                                                height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                                <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>
                                                </svg>
                                            </button>                                      
                                        </div>
                                    </div>
                                <?php
                                endforeach;                            
                            ?>
                        </div>
                       
                        <div class="form-group">                
                            <!-- Booking per Timeslots -->
                            <label  for="no_of_booking" class="h6"><?php echo esc_html__('No of Booking per Timeslots : ', 'smart-appointment-booking'); ?></label>
                            <input class="form-control col-md-2" type="number" id="no_of_booking" name="no_of_booking" value="<?php echo esc_attr($no_of_booking); ?>">
                        </div>
                        <div class="form-check form-check-inline">
                            <!-- Allow Auto Approve -->
                            <input type="checkbox" name="enable_auto_approve" id="enable_auto_approve"value="1" <?php echo checked(1, $enable_auto_approve, false); ?>>
                            <label class="form-check-label h6" for="enable_auto_approve">Allow Auto Approve</label>
                            
                        </div>
                        <!-- waiting List -->
                        <div class="form-check ">
                            <input type="checkbox" name="waiting_list" id="waiting_list" value="1" <?php echo checked(1, $enable_waiting, false); ?>>
                            <label class="form-check-label h6" for="waiting_list">Allow Waiting List</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="timeslot_BookAllow" id="timeslot_BookAllow" value="1" <?php echo checked(1, $timeslot_BookAllow, false); ?>>
                            <label class="form-check-label h6" for="timeslot_BookAllow">Allow bookings during running timeslot</label>
                        </div>
                        <div class="form-group ">

                            <label class="h6">Bookings stops after minutes of start time</label>
                            <input type="number" class="hours col-md-2" name="booking_stops_after[hours]" min="0" max="23" placeholder="HH" value="<?php echo isset($booking_stops_after['hours']) && is_array($booking_stops_after) && !empty($booking_stops_after['hours']) ? esc_attr($booking_stops_after['hours']) : ''; ?>">
                            <span>:</span>
                            <input type="number" class="minutes col-md-2" name="booking_stops_after[minutes]" min="0" max="59" placeholder="MM" value="<?php echo isset($booking_stops_after['minutes']) && is_array($booking_stops_after) && !empty($booking_stops_after['minutes']) ? esc_attr($booking_stops_after['minutes']) : ''; ?>">

                            <span class="timeslot-validation-message" style="color: red;"></span>
                        </div>
                    </div>
                </div>
                <!-- Tabination 2 content  -->
                <div id="tab3" class="tab-content">  
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="enable_recurring_apt_i" name="enable_recurring_apt" value="1" <?php echo checked(1, $enable_recurring_apt, false); ?>>
                            <label class="form-check-label h6" for="waiting_list">Enable Recurring Bookings</label>
                        </div>
                        <!-- hide and show whole container on enable and disaable button -->
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
                                </select>
                            </div>
                            <div id="certain_weekdays_fields" class="form-group form-general-group" style="display: none;" >
                            <label for="recurring_type"><?php echo esc_html__('Select Weekdays: ', 'smart-appointment-booking'); ?></label>
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
                </div>
                <div id="tab4" class="tab-content">
                 
                <div class="form-check form-check-inline">
                    <input type="checkbox"  name="enable_advance_setting" value="1" <?php echo checked(1, $enable_advance_setting, false); ?>>
                    <label class="form-check-label h6" for="waiting_list">Enable advanced Setting</label>
                </div>
                <div id="advance-meta-box">
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
                                <label class="h6" for="advance_date_<?php echo esc_attr($index); ?>">Advance Date:</label>
                                <input type="date" class="form-control" id="advance_date_<?php echo esc_attr($index); ?>" name="advancedata[<?php echo esc_attr($index); ?>][advance_date]" value="<?php echo esc_attr($data['advance_date']); ?>">
                            </div>
                            <div class="timeslot-repeater timeslot-container col-md-9 "  id="timeslot-repeater-<?php echo esc_attr($index); ?>">
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
                                            <input type="time" class="form-control" name="advancedata[<?php echo esc_attr($index); ?>][advance_timeslot][<?php echo esc_attr($slot_index); ?>][start_time]" value="<?php echo esc_attr($timeslot['start_time']); ?>">
                                        </div>
                                        <div class="form-group col-md-3"> 
                                            <label>End Time:</label>
                                            <input type="time" class="form-control" name="advancedata[<?php echo esc_attr($index); ?>][advance_timeslot][<?php echo esc_attr($slot_index); ?>][end_time]" value="<?php echo esc_attr($timeslot['end_time']); ?>">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Bookings:</label>
                                            <input type="number" class="form-control" name="advancedata[<?php echo esc_attr($index); ?>][advance_timeslot][<?php echo esc_attr($slot_index); ?>][bookings]" value="<?php echo esc_attr($timeslot['bookings']); ?>">
                                        </div>
                                        <div class="form-group col-2 remove-timeslot-wrapper">
                                        <svg class="remove-timeslot" xmlns="http://www.w3.org/2000/svg" width="16" 
                                            height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                            <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>
                                            </svg>
                                        </div>
                                    </div>
                                <?php } ?>
                                
                            </div>
                            <button type="button" class="remove-row btn btn-danger">Remove Date</button>
                        </div>
                    <?php } ?>
                </div>
                </div>
            </div>
            <?php
        }
        function saab_admin_get_shortcodes_keylabel_second($post_id){
            $shortcode_list = array();
            $form_data = get_post_meta( $post_id, 'saab_formschema', true ); 
            if(isset($form_data) && !empty($form_data)){
                $form_data=json_decode($form_data);
                foreach ($form_data as $obj) {   
                  if ($obj->key !== 'submit') {
                    $shortcode_list[] = array(

                        'fieldkey'=>esc_attr($obj->key),
                        'fieldlabel'=>esc_html($obj->label),
                    );
                  }
                   
                }
            }
            
            return $shortcode_list;
        }
        /**
        * Save Form and Booking Configuration
        */
        function saab_save_post_function( $post_id ) {

            $get_type =  get_post_type($post_id);
            if($get_type !== 'saab_form_builder' ){
                return;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }
        
            if (!isset($_POST['saab_repeat_appointment_nonce_field'])) {
                return $post_id;
            }
            if ( ! isset( $_POST['saab_repeat_appointment_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['saab_repeat_appointment_nonce_field'] ) ) , 'saab_repeat_appointment_nonce' ) )
            {
              return $post_id;
            }
            if (isset($_POST['cal_title'])) {
                $cal_title = sanitize_text_field($_POST['cal_title']);
                update_post_meta($post_id, 'saab_cal_title', $cal_title);
            } 
            
            if (isset($_POST['cal_description'])) {
                $cal_description = sanitize_text_field($_POST['cal_description']);
                update_post_meta($post_id, 'saab_cal_description', $cal_description);
            }
            // Section Tab 1 
            // Check if the enable_booking field is set and save the value
            if (isset($_POST['enable_booking'])) {
                $enable_booking = absint($_POST['enable_booking']);
                update_post_meta($post_id, 'saab_enable_booking', $enable_booking);
            } else {
                delete_post_meta($post_id, 'saab_enable_booking');
            }
            //Weekdays
            if (isset($_POST['weekdays'])) {
                $selected_weekdays = array_map('sanitize_text_field', $_POST['weekdays']);
                update_post_meta($post_id, 'saab_weekdays', $selected_weekdays);
            } else {
                update_post_meta($post_id, 'saab_weekdays', array());
            }
            
            // Save the radio button value for appointment Type
            if (isset($_POST['appointment_type'])) {
                $selected_option = sanitize_text_field($_POST['appointment_type']);
                update_post_meta($post_id, 'saab_appointment_type', $selected_option);
            }

            // Save the  link value if Appointment Type "Virtual" is selected
            if (isset($_POST['virtual_link'])) {
                $link_value = sanitize_text_field($_POST['virtual_link']);
                update_post_meta($post_id, 'saab_virtual_link', $link_value);
            }
            
            //Symbol
            if ( isset( $_POST['label_symbol'] ) ) {
                $label_symbol = sanitize_text_field( $_POST['label_symbol'] );
                update_post_meta( $post_id, 'saab_label_symbol', $label_symbol );
            }

             //Symbol
             if ( isset( $_POST['cost'] ) ) {
                $cost = sanitize_text_field( $_POST['cost'] );
                update_post_meta( $post_id, 'saab_cost', $cost );
            }
            
            if ( isset( $_POST['timezone'] ) ) {
                $timezone = sanitize_text_field( $_POST['timezone'] );
                update_post_meta( $post_id, 'saab_timezone', $timezone );
            }
            
            if ( isset( $_POST['bookmap_email'] ) ) {
                $map_email = sanitize_text_field( $_POST['bookmap_email'] );
                update_post_meta( $post_id, 'saab_map_email', $map_email );              
            }
            
            if ( isset( $_POST['cost'] ) ) {
                $cost = sanitize_text_field( $_POST['cost'] );
                update_post_meta( $post_id, 'saab_saab_cost', $cost );
            }
            
            //selected_date
            if (isset($_POST['selected_date'])) {
                update_post_meta($post_id, 'saab_selected_date', sanitize_text_field($_POST['selected_date']));
            }
            
            if (isset($_POST['start_time'])) {
                update_post_meta($post_id, 'saab_start_time', sanitize_text_field($_POST['start_time']));
            }
            
            if (isset($_POST['end_time'])) {
                update_post_meta($post_id, 'saab_end_time', sanitize_text_field($_POST['end_time']));
            }
            
             //Steps Duration
            if ( isset( $_POST['steps_duration'] ) ) {
                $steps_duration = sanitize_text_field($_POST['steps_duration']);
                $sanitized_steps_duration = array(
                    'hours' => sanitize_text_field( $steps_duration['hours'] ),
                    'minutes' => sanitize_text_field( $steps_duration['minutes'] )
                );
        
                // Update the post meta data with the field value
                update_post_meta( $post_id, 'saab_steps_duration', $sanitized_steps_duration );
            }
            //timeslot_duration
            if ( isset( $_POST['booking_stops_after'] ) ) {
                $booking_stops_after_duration = sanitize_text_field($_POST['booking_stops_after']);
                $sanitized_booking_stops_after_duration = array(
                    'hours' => sanitize_text_field( $booking_stops_after_duration['hours'] ),
                    'minutes' => sanitize_text_field( $booking_stops_after_duration['minutes'] )
                );
        
                // Update the post meta data with the field value
                update_post_meta( $post_id, 'saab_booking_stops_after', $sanitized_booking_stops_after_duration );
            }
            //timeslot_duration
            if ( isset( $_POST['timeslot_duration'] ) ) {
                $timeslot_duration = sanitize_text_field($_POST['timeslot_duration']);
                $sanitized_timeslot_duration = array(
                    'hours' => sanitize_text_field( $timeslot_duration['hours'] ),
                    'minutes' => sanitize_text_field( $timeslot_duration['minutes'] )
                );
        
                update_post_meta( $post_id, 'saab_timeslot_duration', $sanitized_timeslot_duration );
            }
            
            //no_of_booking
            if ( isset( $_POST['no_of_booking'] ) ) {
                $selected_date = absint($_POST['no_of_booking']);
                update_post_meta( $post_id, 'saab_no_of_booking', $selected_date );
            }
            //waiting List
            if (isset($_POST['waiting_list']) && filter_var($_POST['waiting_list'], FILTER_VALIDATE_BOOLEAN)) {
                update_post_meta($post_id, 'saab_waiting_list', 1);
            } else {
                delete_post_meta($post_id, 'saab_waiting_list');
            }
            //timeslotBookingAllowed
            if (isset($_POST['timeslot_BookAllow']) && filter_var($_POST['timeslot_BookAllow'], FILTER_VALIDATE_BOOLEAN)) {
                update_post_meta($post_id, 'saab_timeslot_BookAllow', 1);
            } else {
                delete_post_meta($post_id, 'saab_timeslot_BookAllow');
            }
            //enable_auto_approve
            if (isset($_POST['enable_auto_approve']) && filter_var($_POST['enable_auto_approve'], FILTER_VALIDATE_BOOLEAN)) {
                update_post_meta($post_id, 'saab_enable_auto_approve', 1);
            } else {
                delete_post_meta($post_id, 'saab_enable_auto_approve');
            }
            //multiple breaks
            if (isset($_POST['breaktimeslots'])) {
                $breaktimeslots = sanitize_text_field($_POST['breaktimeslots']);
            
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
                update_post_meta($post_id, 'saab_breaktimeslots', $sanitized_breaktimeslots);
            }else{
                    $breaktimeslots = get_post_meta($post_id, 'saab_breaktimeslots', true);
                    if (empty($timeslots)) {
                        $sanitized_breaktimeslots = array(
                            array(
                            'start_time' => '',
                            'end_time' => '',
                            ),
                        );
                    }
                    update_post_meta($post_id, 'saab_breaktimeslots', $sanitized_breaktimeslots);
                }
            
            if (isset($_POST['generatetimeslot'])) {
                $generatetimeslots = sanitize_text_field($_POST['generatetimeslot']);   
                // Sanitize and save the values
                $sanitized_generatetimeslots = array();
                foreach ($generatetimeslots as $generatetimeslot) {
                    $generatestart_time = sanitize_text_field($generatetimeslot['start_time']);
                    $generateend_time = sanitize_text_field($generatetimeslot['end_time']);
                    $sanitized_generatetimeslots[] = array(
                    'start_time' => $generatestart_time,
                    'end_time' => $generateend_time,
                    );
                }            
                update_post_meta($post_id, 'saab_generatetimeslot', $sanitized_generatetimeslots);
            }else{
                $generatetimeslots = get_post_meta($post_id, 'saab_generatetimeslot', true);
                if (empty($timeslots)) {
                    $sanitized_generatetimeslots = array(
                        array(
                        'start_time' => '',
                        'end_time' => '',
                        ),
                    );
                }
                update_post_meta($post_id, 'saab_generatetimeslot', $sanitized_generatetimeslots);
            }
            
            //Enable Recurring Events
            if (isset($_POST['enable_recurring_apt']) && filter_var($_POST['enable_recurring_apt'], FILTER_VALIDATE_BOOLEAN)) {
                update_post_meta($post_id, 'saab_enable_recurring_apt', 1);
            } else {
                delete_post_meta($post_id, 'saab_enable_recurring_apt');
            }
            if (isset($_POST['enable_advance_setting']) && filter_var($_POST['enable_advance_setting'], FILTER_VALIDATE_BOOLEAN)) {
                update_post_meta($post_id, 'saab_enable_advance_setting', 1);
            } else {
                delete_post_meta($post_id, 'saab_enable_advance_setting');
            }
            if (isset($_POST['recurring_type'])) {
                $recurring_type = sanitize_text_field($_POST['recurring_type']);
                update_post_meta($post_id, 'saab_recurring_type', $recurring_type);
            }
            if (isset($_POST['recur_weekdays'])) {
                $sanitized_recur_weekdays = array_map('sanitize_text_field', $_POST['recur_weekdays']);
                update_post_meta($post_id, 'saab_recur_weekdays', $sanitized_recur_weekdays); 
            }
            if (isset($_POST['advancedata'])) {                
                $advancedata = sanitize_text_field($_POST['advancedata']);                
                update_post_meta($post_id, 'saab_advancedata', $advancedata);
            }
            if (isset($_POST['holidays'])) {
                $holidays = array_map('sanitize_text_field', $_POST['holidays']);
                update_post_meta($post_id, 'saab_holiday_dates', $holidays);
            }
            if (isset($_POST['end_repeats'])) {
                $end_repeats = sanitize_text_field($_POST['end_repeats']);
                update_post_meta($post_id, 'saab_end_repeats', $end_repeats);
            }
            if (isset($_POST['end_repeats_on'])) {
                $end_repeats_on = sanitize_text_field($_POST['end_repeats_on']);
                update_post_meta($post_id, 'saab_end_repeats_on', $end_repeats_on);
            }
            if (isset($_POST['end_repeats_after'])) {
                $end_repeats_after = sanitize_text_field($_POST['end_repeats_after']);
                update_post_meta($post_id, 'saab_end_repeats_after', $end_repeats_after);
            }
         }
        
     
        /**         * 
         *  Save Booking Entries post type data and send notification to user on update
         * 
         */
        function saab_save_notes_data($post_id) {
            if (!isset($_POST['notes_nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash ( $_POST['notes_nonce'] ) ), 'save_notes')) {
                return;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }

            if (isset($_POST['notes'])) {
                $notes = sanitize_textarea_field($_POST['notes']);
                update_post_meta($post_id, 'saab_notes', $notes);
            }
            
            if (isset($_POST['form_id'])) {
                $form_id = sanitize_text_field($_POST['form_id']);
            }
            
            if (isset($_POST['no_of_bookings'])) {
                $no_of_bookings = absint($_POST['no_of_bookings']);
                update_post_meta($post_id, 'saab_slotcapacity', $no_of_bookings);
            }
            
            if (isset($_POST['booking_date'])) {
                $booking_date = sanitize_text_field($_POST['booking_date']);
                $currentMonth = gmdate('n',strtotime($booking_date));
                $currentYear = gmdate('Y',strtotime($booking_date));
                $currentday = gmdate('j', strtotime($booking_date));
                $booking_date = 'saabid_'.$form_id.'_'.$currentMonth.'_'.$currentday.'_'.$currentYear;
                update_post_meta($post_id, 'saab_booking_date', $booking_date);
            }
            if (isset($_POST['start_time']) && isset($_POST['end_time'])) {
                $start_time = trim(gmdate("h:i A", strtotime( sanitize_text_field($_POST['start_time']) )));
                $end_time = trim(gmdate("h:i A", strtotime( sanitize_text_field($_POST['end_time']) )));
                $timeslot = $start_time.'-'.$end_time;
                update_post_meta($post_id, 'saab_timeslot', $timeslot);
            }
          
            if (isset($_POST['manual_notification']) &&  sanitize_text_field($_POST['manual_notification']  !== 'any')) {
                $selected_action = isset($_POST['manual_notification']) ? sanitize_text_field($_POST['manual_notification']) : ''; 
                $booking_status = isset($_POST['booking_status']) ? sanitize_text_field($_POST['booking_status']) : ''; 
                // update_post_meta($post_id, 'saab_entry_status', $booking_status);                
                $bookingId = isset($_POST['post_id']) ? absint($_POST['post_id']) : '';                 
                $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';                 
                $formdata = get_post_meta($bookingId,'saab_submission_data',true);                
                $listform_label_val =$this->saab_admin_getkey_value_formshortcodes($post_id,$formdata);
                $listform_label_val['Status'] = $booking_status;
                    
                $send_notification =$this->saab_admin_send_notification($selected_action,$form_id, $post_id, $listform_label_val);
                update_post_meta($post_id, 'saab_manual_notification', $selected_action);
                
            }else{
                $booking_status = isset($_POST['booking_status']) ? sanitize_text_field($_POST['booking_status']) : ''; 
                update_post_meta($post_id, 'saab_entry_status', $booking_status);
                $formdata = get_post_meta($post_id,'saab_submission_data',true);
                $listform_label_val =$this->saab_admin_getkey_value_formshortcodes($post_id,$formdata);
                $listform_label_val['Status'] = $booking_status; 
                $send_notification =$this->saab_admin_send_notification($booking_status,$form_id, $post_id, $listform_label_val);
            }
          
        }
        /**
         * Collect Shortcode key - value 
         */
        function saab_admin_getkey_value_formshortcodes($bookingId,$form_data){
            
            $form_id = get_post_meta($bookingId,'saab_form_id',true);
            $FormTitle = get_the_title( $form_id );
            $emailTo = $first_name = $last_name = $service = '';
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
                $cancelbooking_url = home_url('/?booking_id=' . $encrypted_booking_id . '&status=cancel');
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
        /**
         * Send Notification on status change, or manual notification
         */
        function saab_admin_send_notification($status,$form_id, $post_id, $form_data    ) {
           
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
                    
                    $shortcodesArray = $this->admin_get_shortcodes($form_id);

                    $to = $this->admin_check_shortcode_exist($check_to,$form_id, $form_data,$shortcodesArray );
                    $from = $this->admin_check_shortcode_exist($check_from,$form_id, $form_data,$shortcodesArray );
                    $replyto = $this->admin_check_shortcode_exist($check_replyto,$form_id, $form_data,$shortcodesArray );
                    $bcc = $this->admin_check_shortcode_exist($check_bcc,$form_id, $form_data ,$shortcodesArray );
                    $cc = $this->admin_check_shortcode_exist($check_cc,$form_id, $form_data,$shortcodesArray );
                    $check_body = $this->admin_check_shortcodes_exist_in_editor($check_body,$form_id, $form_data,$shortcodesArray );
                    $subject = $this->admin_check_shortcodes_exist_in_editor($subject,$form_id, $form_data,$shortcodesArray );

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
         * Check if shortcode exists in confirmation for editor field and replace with its value
         */
        function admin_check_shortcodes_exist_in_editor($fieldValue, $form_id, $form_data, $shortcodes) {
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
         * Check if shortcode exists in confirmation for text input fields and replace with its value
         */
        function admin_check_shortcode_exist($fieldValue, $form_id, $form_data,$dataArray) {
            
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
         * Collect basic neccessary shortcode
         */
        function admin_get_shortcodes($form_id){
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
        * Adds the meta box container.
        */
        function saab_add_meta_box( $post_type ) {
          
            $post_types = array( 'manage_entries');

            if ( in_array( $post_type, $post_types ) ) {
                add_meta_box(
                    'form_submission_data',
                    ' ',
                    array( $this, 'saab_entries_render_meta_box_content' ),
                    $post_type,
                    'normal',
                    'high'
                );
                
                add_meta_box(
                    'edit_form_data',
                    esc_html__( 'Edit Forms Details', 'smart-appointment-booking' ),
                    array( $this, 'saab_edit_form_details' ),
                    $post_type,
                    'normal',
                    'high'
                );
                add_meta_box(
                    'manual_notification', 
                    esc_html__('Send Manual Notification','smart-appointment-booking'), 
                    array( $this, 'notification_logs' ),
                    $post_type,
                    'side', 
                    'default'
                );
                add_meta_box(
                    'notes-meta-box',
                    esc_html__('Notes','smart-appointment-booking'), 
                    array( $this, 'saab_render_notes_meta_box' ),
                    $post_type,
                    'side',
                    'default'
                );
            
            }

            $post_types = array( 'saab_form_builder');

            if ( in_array( $post_type, $post_types ) ) {
             
                add_meta_box(
                    'form_shortcode_data',
                    esc_html__('Form Shortcode','smart-appointment-booking'), 
                    array( $this, 'saab_render_meta_box_shortcode' ),
                    $post_type,
                    'normal',
                    'high'
                );               
                add_meta_box(
                    'create_saab_form',
                    esc_html__( 'Form Configuration', 'smart-appointment-booking' ),
                    array( $this, 'formio_render_meta_box_content' ),
                    $post_type,
                    'normal',
                    'high'
                );
                add_meta_box(
                    'appointment_setting',
                    esc_html__( 'Booking Configuration', 'smart-appointment-booking' ),
                    array( $this, 'saab_repeat_appointment' ),
                    $post_type,
                    'normal',
                    'high'
                );

                add_meta_box(
                    'configure_notifications',
                    'Other Form Settings',
                    array( $this, 'saab_render_configure_notifications' ),
                    $post_type,
                    'side',
                    'default'
                );
             
            }
        }
        /**
         * Render Meta box for shortcode to copy and paste
         */
        function saab_render_meta_box_shortcode($post){
            $post_id = $post->ID;    
            if ($post_id && get_post_status($post_id) === 'publish') {                  
                echo "<p class='edit_preview_shortcode'>[booking_form form_id='" . esc_attr($post_id) . "']</p>";       
            }else{
                echo "<p class='edit_preview_shortcode'>Publish Post to generate Shortcode. </p>";
            }           
        }
        /**
         * configure_notifications - add meta box callback
         */
        function saab_render_configure_notifications($post){
            $post_id = $post->ID;
            $post_type = get_post_type( $post_id );
            if($post_type === 'saab_form_builder'){
                $form_id = get_post_meta($post_id,'saab_form_id',true);
                $page_slug = 'notification-settings';
                $post_type = 'saab_form_builder';
                // $post_id = 5508;
                $nonce = wp_create_nonce('other_setting');
                $admin_url = esc_url(admin_url('admin.php'));
                $view_entry_url = add_query_arg(
                    array(
                        'page' => $page_slug,
                        'post_type' => $post_type,
                        'post_id' => $post_id,
                        'nonce' => $nonce,
                    ),
                    $admin_url
                );

                echo '<a href="' . esc_url($view_entry_url) . '" style="color:black;background-color:#5f809d;" target="_blank"><div class="btn btn-secondary" style="border:1px solid lightgray;color:#666;background-color:#f5f5f5;" id="misc-notification"> 
                        <b>Configure Email Notifications & Confirmations </b>
                        </div></a>';
                ?>
            
                <?php
            }
        }
       // Render the meta box content
       
        function notification_logs($post) {
            $post_id = $post->ID;
            $form_id = get_post_meta($post_id,'saab_form_id',true);
            $message = get_post_meta($post_id,'saab_manual_notification',true);
            $enable_booking = get_post_meta($form_id, 'saab_enable_booking', true);
            
            ?>
            <select name="manual_notification" id="manual_notification" data-formid="<?php echo $form_id; ?>" data-postid="<?php echo $post_id; ?>" >
            <option value="any">Choose an action</option>
                <?php 
                if($enable_booking){
                ?>
                <option value="booked" ><?php echo esc_html__('Booked','smart-appointment-booking'); ?></option>
                <option value="approved" ><?php echo esc_html__('Approved','smart-appointment-booking'); ?></option>
                <option value="cancelled" ><?php echo esc_html__('Cancelled','smart-appointment-booking'); ?></option>
                <option value="waiting" ><?php echo esc_html__('Waiting','smart-appointment-booking'); ?></option>
                <option value="pending" ><?php echo esc_html__('Pending','smart-appointment-booking'); ?></option>
                <?php 
                }
                ?>
                 <option value="submitted" >Submitted</option>
            </select>
            <button type="button" id="send_notification_button" style="height: 33px; color: grey;border-color: grey;"> > </button>
            
            <?php
        }
        
        function saab_edit_form_details($post){
            // echo $post_id;
            $form_id = get_post_meta( $post->ID, 'saab_form_id', true ); 
            $form_schema = get_post_meta($form_id, 'saab_formschema', true);
            $form_data = get_post_meta($post->ID, 'saab_submission_data', true );
            if ($form_schema) {
                ?>
               <div id="formio"></div>
                <script>
                    var myScriptData = <?php echo $form_schema; ?>;                                                          
                    var value = myScriptData;
                    var entryData = <?php echo wp_json_encode($form_data['data']); ?>;

                    Formio.createForm(document.getElementById('formio'), {
                        components: value,
                        readOnly: false, 
                        noAlerts: true, 
                       
                    }).then(function(form) {
                        form.setSubmission({
                        data: entryData 
                        });
                        form.redraw();
                       
                        var submitButton = form.getComponent('submit');
                        if (submitButton) {
                            submitButton.component.label = 'Update';
                            submitButton.redraw();
                        }
                      
                        form.on('submit', function(submission) {
                        event.preventDefault();

                        if (!submitButton.disabled) { 
                            submitButton.disabled = true;
                            submitButton.loading = true;
                            submitButton.updateValue();
                        }

                        var entryId = <?php echo $post->ID; ?>;
                        var updatedData = submission.data;
                        var nonce = ajax_object.nonce;
                        jQuery.ajax({
                            url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                            type: 'post',
                            data: {
                            action: 'saab_update_form_entry_data', 
                            entry_id: entryId,
                            updated_data: updatedData,
                            security: nonce,
                            },
                            success: function(response) {
                            if (response.success) {
                                console.log('Form data updated successfully');
                            } else {
                                console.log('Failed to update form data');
                            }
                            },
                            error: function() {
                            console.log('Failed to update form data');
                            },
                            complete: function() {
                                submitButton.disabled = false;
                                submitButton.loading = false;
                                submitButton.updateValue();
                            }
                        });

                        return false;
                        });

                    });
                </script>

             <?php

            }
        }
        function saab_render_notes_meta_box($post) {
            $notes = get_post_meta($post->ID, 'saab_notes', true);
            wp_nonce_field('save_notes', 'notes_nonce');
            ?>
            <textarea name="notes" id="notes" rows="5" style="width: 100%;"><?php echo esc_textarea($notes); ?></textarea>
            <?php
        }

        function timezone_dropdown($post_id){
            $get_timezone_value = get_post_meta( $post_id,'saab_timezone',true);
            $timezones_Africa = array(
                "Africa/Abidjan" => "Abidjan",
                "Africa/Accra" => "Accra",
                "Africa/Addis_Ababa" => "Addis Ababa",
                "Africa/Algiers" => "Algiers",
                "Africa/Asmara" => "Asmara",
                "Africa/Bamako" => "Bamako",
                "Africa/Bangui" => "Bangui",
                "Africa/Banjul" => "Banjul",
                "Africa/Bissau" => "Bissau",
                "Africa/Blantyre" => "Blantyre",
                "Africa/Brazzaville" => "Brazzaville",
                "Africa/Bujumbura" => "Bujumbura",
                "Africa/Cairo" => "Cairo",
                "Africa/Casablanca" => "Casablanca",
                "Africa/Ceuta" => "Ceuta",
                "Africa/Conakry" => "Conakry",
                "Africa/Dakar" => "Dakar",
                "Africa/Dar_es_Salaam" => "Dar es Salaam",
                "Africa/Djibouti" => "Djibouti",
                "Africa/Douala" => "Douala",
                "Africa/El_Aaiun" => "El Aaiun",
                "Africa/Freetown" => "Freetown",
                "Africa/Gaborone" => "Gaborone",
                "Africa/Harare" => "Harare",
                "Africa/Johannesburg" => "Johannesburg",
                "Africa/Juba" => "Juba",
                "Africa/Kampala" => "Kampala",
                "Africa/Khartoum" => "Khartoum",
                "Africa/Kigali" => "Kigali",
                "Africa/Kinshasa" => "Kinshasa",
                "Africa/Lagos" => "Lagos",
                "Africa/Libreville" => "Libreville",
                "Africa/Lome" => "Lome",
                "Africa/Luanda" => "Luanda",
                "Africa/Lubumbashi" => "Lubumbashi",
                "Africa/Lusaka" => "Lusaka",
                "Africa/Malabo" => "Malabo",
                "Africa/Maputo" => "Maputo",
                "Africa/Maseru" => "Maseru",
                "Africa/Mbabane" => "Mbabane",
                "Africa/Mogadishu" => "Mogadishu",
                "Africa/Monrovia" => "Monrovia",
                "Africa/Nairobi" => "Nairobi",
                "Africa/Ndjamena" => "Ndjamena",
                "Africa/Niamey" => "Niamey",
                "Africa/Nouakchott" => "Nouakchott",
                "Africa/Ouagadougou" => "Ouagadougou",
                "Africa/Porto-Novo" => "Porto-Novo",
                "Africa/Sao_Tome" => "Sao Tome",
                "Africa/Tripoli" => "Tripoli",
                "Africa/Tunis" => "Tunis",
                "Africa/Windhoek" => "Windhoek"
            );
            $timezones_America = array(
                "America/Adak" => "Adak",
                "America/Anchorage" => "Anchorage",
                "America/Anguilla" => "Anguilla",
                "America/Antigua" => "Antigua",
                "America/Araguaina" => "Araguaina",
                "America/Argentina/Buenos_Aires" => "Argentina - Buenos Aires",
                "America/Argentina/Catamarca" => "Argentina - Catamarca",
                "America/Argentina/Cordoba" => "Argentina - Cordoba",
                "America/Argentina/Jujuy" => "Argentina - Jujuy",
                "America/Argentina/La_Rioja" => "Argentina - La Rioja",
                "America/Argentina/Mendoza" => "Argentina - Mendoza",
                "America/Argentina/Rio_Gallegos" => "Argentina - Rio Gallegos",
                "America/Argentina/Salta" => "Argentina - Salta",
                "America/Argentina/San_Juan" => "Argentina - San Juan",
                "America/Argentina/San_Luis" => "Argentina - San Luis",
                "America/Argentina/Tucuman" => "Argentina - Tucuman",
                "America/Argentina/Ushuaia" => "Argentina - Ushuaia",
                "America/Aruba" => "Aruba",
                "America/Asuncion" => "Asuncion",
                "America/Atikokan" => "Atikokan",
                "America/Bahia" => "Bahia",
                "America/Bahia_Banderas" => "Bahia Banderas",
                "America/Barbados" => "Barbados",
                "America/Belem" => "Belem",
                "America/Belize" => "Belize",
                "America/Blanc-Sablon" => "Blanc-Sablon",
                "America/Boa_Vista" => "Boa Vista",
                "America/Bogota" => "Bogota",
                "America/Boise" => "Boise",
                "America/Cambridge_Bay" => "Cambridge Bay",
                "America/Campo_Grande" => "Campo Grande",
                "America/Cancun" => "Cancun",
                "America/Caracas" => "Caracas",
                "America/Cayenne" => "Cayenne",
                "America/Cayman" => "Cayman",
                "America/Chicago" => "Chicago",
                "America/Chihuahua" => "Chihuahua",
                "America/Ciudad_Juarez" => "Ciudad Juarez",
                "America/Costa_Rica" => "Costa Rica",
                "America/Creston" => "Creston",
                "America/Cuiaba" => "Cuiaba",
                "America/Curacao" => "Curacao",
                "America/Danmarkshavn" => "Danmarkshavn",
                "America/Dawson" => "Dawson",
                "America/Dawson_Creek" => "Dawson Creek",
                "America/Denver" => "Denver",
                "America/Detroit" => "Detroit",
                "America/Dominica" => "Dominica",
                "America/Edmonton" => "Edmonton",
                "America/Eirunepe" => "Eirunepe",
                "America/El_Salvador" => "El Salvador",
                "America/Fortaleza" => "Fortaleza",
                "America/Fort_Nelson" => "Fort Nelson",
                "America/Glace_Bay" => "Glace Bay",
                "America/Goose_Bay" => "Goose Bay",
                "America/Grand_Turk" => "Grand Turk",
                "America/Grenada" => "Grenada",
                "America/Guadeloupe" => "Guadeloupe",
                "America/Guatemala" => "Guatemala",
                "America/Guayaquil" => "Guayaquil",
                "America/Guyana" => "Guyana",
                "America/Halifax" => "Halifax",
                "America/Havana" => "Havana",
                "America/Hermosillo" => "Hermosillo",
                "America/Indiana/Indianapolis" => "Indiana - Indianapolis",
                "America/Indiana/Knox" => "Indiana - Knox",
                "America/Indiana/Marengo" => "Indiana - Marengo",
                "America/Indiana/Petersburg" => "Indiana - Petersburg",
                "America/Indiana/Tell_City" => "Indiana - Tell City",
                "America/Indiana/Vevay" => "Indiana - Vevay",
                "America/Indiana/Vincennes" => "Indiana - Vincennes",
                "America/Indiana/Winamac" => "Indiana - Winamac",
                "America/Inuvik" => "Inuvik",
                "America/Iqaluit" => "Iqaluit",
                "America/Jamaica" => "Jamaica",
                "America/Juneau" => "Juneau",
                "America/Kentucky/Louisville" => "Kentucky - Louisville",
                "America/Kentucky/Monticello" => "Kentucky - Monticello",
                "America/Kralendijk" => "Kralendijk",
                "America/La_Paz" => "La Paz",
                "America/Lima" => "Lima",
                "America/Los_Angeles" => "Los Angeles",
                "America/Lower_Princes" => "Lower Princes",
                "America/Maceio" => "Maceio",
                "America/Managua" => "Managua",
                "America/Manaus" => "Manaus",
                "America/Marigot" => "Marigot",
                "America/Martinique" => "Martinique",
                "America/Matamoros" => "Matamoros",
                "America/Mazatlan" => "Mazatlan",
                "America/Menominee" => "Menominee",
                "America/Merida" => "Merida",
                "America/Metlakatla" => "Metlakatla",
                "America/Mexico_City" => "Mexico City",
                "America/Miquelon" => "Miquelon",
                "America/Moncton" => "Moncton",
                "America/Monterrey" => "Monterrey",
                "America/Montevideo" => "Montevideo",
                "America/Montserrat" => "Montserrat",
                "America/Nassau" => "Nassau",
                "America/New_York" => "New York",
                "America/Nome" => "Nome",
                "America/Noronha" => "Noronha",
                "America/North_Dakota/Beulah" => "North Dakota - Beulah",
                "America/North_Dakota/Center" => "North Dakota - Center",
                "America/North_Dakota/New_Salem" => "North Dakota - New Salem",
                "America/Nuuk" => "Nuuk",
                "America/Ojinaga" => "Ojinaga",
                "America/Panama" => "Panama",
                "America/Paramaribo" => "Paramaribo",
                "America/Phoenix" => "Phoenix",
                "America/Port-au-Prince" => "Port-au-Prince",
                "America/Port_of_Spain" => "Port of Spain",
                "America/Porto_Velho" => "Porto Velho",
                "America/Puerto_Rico" => "Puerto Rico",
                "America/Punta_Arenas" => "Punta Arenas",
                "America/Rankin_Inlet" => "Rankin Inlet",
                "America/Recife" => "Recife",
                "America/Regina" => "Regina",
                "America/Resolute" => "Resolute",
                "America/Rio_Branco" => "Rio Branco",
                "America/Santarem" => "Santarem",
                "America/Santiago" => "Santiago",
                "America/Santo_Domingo" => "Santo Domingo",
                "America/Sao_Paulo" => "Sao Paulo",
                "America/Scoresbysund" => "Scoresbysund",
                "America/Sitka" => "Sitka",
                "America/St_Barthelemy" => "St Barthelemy",
                "America/St_Johns" => "St Johns",
                "America/St_Kitts" => "St Kitts",
                "America/St_Lucia" => "St Lucia",
                "America/St_Thomas" => "St Thomas",
                "America/St_Vincent" => "St Vincent",
                "America/Swift_Current" => "Swift Current",
                "America/Tegucigalpa" => "Tegucigalpa",
                "America/Thule" => "Thule",
                "America/Tijuana" => "Tijuana",
                "America/Toronto" => "Toronto",
                "America/Tortola" => "Tortola",
                "America/Vancouver" => "Vancouver",
                "America/Whitehorse" => "Whitehorse",
                "America/Winnipeg" => "Winnipeg",
                "America/Yakutat" => "Yakutat"
            );
            
            $timezones_Antarctica = array(
                "Antarctica/Casey" => "Casey",
                "Antarctica/Davis" => "Davis",
                "Antarctica/DumontDUrville" => "DumontDUrville",
                "Antarctica/Macquarie" => "Macquarie",
                "Antarctica/Mawson" => "Mawson",
                "Antarctica/McMurdo" => "McMurdo",
                "Antarctica/Palmer" => "Palmer",
                "Antarctica/Rothera" => "Rothera",
                "Antarctica/Syowa" => "Syowa",
                "Antarctica/Troll" => "Troll",
                "Antarctica/Vostok" => "Vostok"
            );
            $timezones_Asia = array(
                "Asia/Aden" => "Aden",
                "Asia/Almaty" => "Almaty",
                "Asia/Amman" => "Amman",
                "Asia/Anadyr" => "Anadyr",
                "Asia/Aqtau" => "Aqtau",
                "Asia/Aqtobe" => "Aqtobe",
                "Asia/Ashgabat" => "Ashgabat",
                "Asia/Atyrau" => "Atyrau",
                "Asia/Baghdad" => "Baghdad",
                "Asia/Bahrain" => "Bahrain",
                "Asia/Baku" => "Baku",
                "Asia/Bangkok" => "Bangkok",
                "Asia/Barnaul" => "Barnaul",
                "Asia/Beirut" => "Beirut",
                "Asia/Bishkek" => "Bishkek",
                "Asia/Brunei" => "Brunei",
                "Asia/Chita" => "Chita",
                "Asia/Choibalsan" => "Choibalsan",
                "Asia/Colombo" => "Colombo",
                "Asia/Damascus" => "Damascus",
                "Asia/Dhaka" => "Dhaka",
                "Asia/Dili" => "Dili",
                "Asia/Dubai" => "Dubai",
                "Asia/Dushanbe" => "Dushanbe",
                "Asia/Famagusta" => "Famagusta",
                "Asia/Gaza" => "Gaza",
                "Asia/Hebron" => "Hebron",
                "Asia/Ho_Chi_Minh" => "Ho Chi Minh",
                "Asia/Hong_Kong" => "Hong Kong",
                "Asia/Hovd" => "Hovd",
                "Asia/Irkutsk" => "Irkutsk",
                "Asia/Jakarta" => "Jakarta",
                "Asia/Jayapura" => "Jayapura",
                "Asia/Jerusalem" => "Jerusalem",
                "Asia/Kabul" => "Kabul",
                "Asia/Kamchatka" => "Kamchatka",
                "Asia/Karachi" => "Karachi",
                "Asia/Kathmandu" => "Kathmandu",
                "Asia/Khandyga" => "Khandyga",
                "Asia/Kolkata" => "Kolkata",
                "Asia/Krasnoyarsk" => "Krasnoyarsk",
                "Asia/Kuala_Lumpur" => "Kuala Lumpur",
                "Asia/Kuching" => "Kuching",
                "Asia/Kuwait" => "Kuwait",
                "Asia/Macau" => "Macau",
                "Asia/Magadan" => "Magadan",
                "Asia/Makassar" => "Makassar",
                "Asia/Manila" => "Manila",
                "Asia/Muscat" => "Muscat",
                "Asia/Nicosia" => "Nicosia",
                "Asia/Novokuznetsk" => "Novokuznetsk",
                "Asia/Novosibirsk" => "Novosibirsk",
                "Asia/Omsk" => "Omsk",
                "Asia/Oral" => "Oral",
                "Asia/Phnom_Penh" => "Phnom Penh",
                "Asia/Pontianak" => "Pontianak",
                "Asia/Pyongyang" => "Pyongyang",
                "Asia/Qatar" => "Qatar",
                "Asia/Qostanay" => "Qostanay",
                "Asia/Qyzylorda" => "Qyzylorda",
                "Asia/Riyadh" => "Riyadh",
                "Asia/Sakhalin" => "Sakhalin",
                "Asia/Samarkand" => "Samarkand",
                "Asia/Seoul" => "Seoul",
                "Asia/Shanghai" => "Shanghai",
                "Asia/Singapore" => "Singapore",
                "Asia/Srednekolymsk" => "Srednekolymsk",
                "Asia/Taipei" => "Taipei",
                "Asia/Tashkent" => "Tashkent",
                "Asia/Tbilisi" => "Tbilisi",
                "Asia/Tehran" => "Tehran",
                "Asia/Thimphu" => "Thimphu",
                "Asia/Tokyo" => "Tokyo",
                "Asia/Tomsk" => "Tomsk",
                "Asia/Ulaanbaatar" => "Ulaanbaatar",
                "Asia/Urumqi" => "Urumqi",
                "Asia/Ust-Nera" => "Ust-Nera",
                "Asia/Vientiane" => "Vientiane",
                "Asia/Vladivostok" => "Vladivostok",
                "Asia/Yakutsk" => "Yakutsk",
                "Asia/Yangon" => "Yangon",
                "Asia/Yekaterinburg" => "Yekaterinburg",
                "Asia/Yerevan" => "Yerevan"
            );
            $timezones_Atlantic = array(
                "Atlantic/Azores" => "Azores",
                "Atlantic/Bermuda" => "Bermuda",
                "Atlantic/Canary" => "Canary",
                "Atlantic/Cape_Verde" => "Cape Verde",
                "Atlantic/Faroe" => "Faroe",
                "Atlantic/Madeira" => "Madeira",
                "Atlantic/Reykjavik" => "Reykjavik",
                "Atlantic/South_Georgia" => "South Georgia",
                "Atlantic/Stanley" => "Stanley",
                "Atlantic/St_Helena" => "St Helena"
            );
            $timezones_Australia = array(
                "Australia/Adelaide" => "Adelaide",
                "Australia/Brisbane" => "Brisbane",
                "Australia/Broken_Hill" => "Broken Hill",
                "Australia/Darwin" => "Darwin",
                "Australia/Eucla" => "Eucla",
                "Australia/Hobart" => "Hobart",
                "Australia/Lindeman" => "Lindeman",
                "Australia/Lord_Howe" => "Lord Howe",
                "Australia/Melbourne" => "Melbourne",
                "Australia/Perth" => "Perth",
                "Australia/Sydney" => "Sydney"
            );
            $timezones_Europe = array(
                "Europe/Amsterdam" => "Amsterdam",
                "Europe/Andorra" => "Andorra",
                "Europe/Astrakhan" => "Astrakhan",
                "Europe/Athens" => "Athens",
                "Europe/Belgrade" => "Belgrade",
                "Europe/Berlin" => "Berlin",
                "Europe/Bratislava" => "Bratislava",
                "Europe/Brussels" => "Brussels",
                "Europe/Bucharest" => "Bucharest",
                "Europe/Budapest" => "Budapest",
                "Europe/Busingen" => "Busingen",
                "Europe/Chisinau" => "Chisinau",
                "Europe/Copenhagen" => "Copenhagen",
                "Europe/Dublin" => "Dublin",
                "Europe/Gibraltar" => "Gibraltar",
                "Europe/Guernsey" => "Guernsey",
                "Europe/Helsinki" => "Helsinki",
                "Europe/Isle_of_Man" => "Isle of Man",
                "Europe/Istanbul" => "Istanbul",
                "Europe/Jersey" => "Jersey",
                "Europe/Kaliningrad" => "Kaliningrad",
                "Europe/Kirov" => "Kirov",
                "Europe/Kyiv" => "Kyiv",
                "Europe/Lisbon" => "Lisbon",
                "Europe/Ljubljana" => "Ljubljana",
                "Europe/London" => "London",
                "Europe/Luxembourg" => "Luxembourg",
                "Europe/Madrid" => "Madrid",
                "Europe/Malta" => "Malta",
                "Europe/Mariehamn" => "Mariehamn",
                "Europe/Minsk" => "Minsk",
                "Europe/Monaco" => "Monaco",
                "Europe/Moscow" => "Moscow",
                "Europe/Oslo" => "Oslo",
                "Europe/Paris" => "Paris",
                "Europe/Podgorica" => "Podgorica",
                "Europe/Prague" => "Prague",
                "Europe/Riga" => "Riga",
                "Europe/Rome" => "Rome",
                "Europe/Samara" => "Samara",
                "Europe/San_Marino" => "San Marino",
                "Europe/Sarajevo" => "Sarajevo",
                "Europe/Saratov" => "Saratov",
                "Europe/Simferopol" => "Simferopol",
                "Europe/Skopje" => "Skopje",
                "Europe/Sofia" => "Sofia",
                "Europe/Stockholm" => "Stockholm",
                "Europe/Tallinn" => "Tallinn",
                "Europe/Tirane" => "Tirane",
                "Europe/Ulyanovsk" => "Ulyanovsk",
                "Europe/Vaduz" => "Vaduz",
                "Europe/Vatican" => "Vatican",
                "Europe/Vienna" => "Vienna",
                "Europe/Vilnius" => "Vilnius",
                "Europe/Volgograd" => "Volgograd",
                "Europe/Warsaw" => "Warsaw",
                "Europe/Zagreb" => "Zagreb",
                "Europe/Zurich" => "Zurich"
            );
            
            $timezones_Indian = array(
                "Indian/Antananarivo" => "Antananarivo",
                "Indian/Chagos" => "Chagos",
                "Indian/Christmas" => "Christmas",
                "Indian/Cocos" => "Cocos",
                "Indian/Comoro" => "Comoro",
                "Indian/Kerguelen" => "Kerguelen",
                "Indian/Mahe" => "Mahe",
                "Indian/Maldives" => "Maldives",
                "Indian/Mauritius" => "Mauritius",
                "Indian/Mayotte" => "Mayotte",
                "Indian/Reunion" => "Reunion"
            );
            $timezones_Pacific = array(
                "Pacific/Apia" => "Apia",
                "Pacific/Auckland" => "Auckland",
                "Pacific/Bougainville" => "Bougainville",
                "Pacific/Chatham" => "Chatham",
                "Pacific/Chuuk" => "Chuuk",
                "Pacific/Easter" => "Easter",
                "Pacific/Efate" => "Efate",
                "Pacific/Fakaofo" => "Fakaofo",
                "Pacific/Fiji" => "Fiji",
                "Pacific/Funafuti" => "Funafuti",
                "Pacific/Galapagos" => "Galapagos",
                "Pacific/Gambier" => "Gambier",
                "Pacific/Guadalcanal" => "Guadalcanal",
                "Pacific/Guam" => "Guam",
                "Pacific/Honolulu" => "Honolulu",
                "Pacific/Kanton" => "Kanton",
                "Pacific/Kiritimati" => "Kiritimati",
                "Pacific/Kosrae" => "Kosrae",
                "Pacific/Kwajalein" => "Kwajalein",
                "Pacific/Majuro" => "Majuro",
                "Pacific/Marquesas" => "Marquesas",
                "Pacific/Midway" => "Midway",
                "Pacific/Nauru" => "Nauru",
                "Pacific/Niue" => "Niue",
                "Pacific/Norfolk" => "Norfolk",
                "Pacific/Noumea" => "Noumea",
                "Pacific/Pago_Pago" => "Pago Pago",
                "Pacific/Palau" => "Palau",
                "Pacific/Pitcairn" => "Pitcairn",
                "Pacific/Pohnpei" => "Pohnpei",
                "Pacific/Port_Moresby" => "Port Moresby",
                "Pacific/Rarotonga" => "Rarotonga",
                "Pacific/Saipan" => "Saipan",
                "Pacific/Tahiti" => "Tahiti",
                "Pacific/Tarawa" => "Tarawa",
                "Pacific/Tongatapu" => "Tongatapu",
                "Pacific/Wake" => "Wake",
                "Pacific/Wallis" => "Wallis"
            );
            $timezones_UTC = array(
                "UTC-12" => "UTC-12",
                "UTC-11.5" => "UTC-11:30",
                "UTC-11" => "UTC-11",
                "UTC-10.5" => "UTC-10:30",
                "UTC-10" => "UTC-10",
                "UTC-9.5" => "UTC-9:30",
                "UTC-9" => "UTC-9",
                "UTC-8.5" => "UTC-8:30",
                "UTC-8" => "UTC-8",
                "UTC-7.5" => "UTC-7:30",
                "UTC-7" => "UTC-7",
                "UTC-6.5" => "UTC-6:30",
                "UTC-6" => "UTC-6",
                "UTC-5.5" => "UTC-5:30",
                "UTC-5" => "UTC-5",
                "UTC-4.5" => "UTC-4:30",
                "UTC-4" => "UTC-4",
                "UTC-3.5" => "UTC-3:30",
                "UTC-3" => "UTC-3",
                "UTC-2.5" => "UTC-2:30",
                "UTC-2" => "UTC-2",
                "UTC-1.5" => "UTC-1:30",
                "UTC-1" => "UTC-1",
                "UTC-0.5" => "UTC-0:30",
                "UTC+0" => "UTC+0",
                "UTC+0.5" => "UTC+0:30",
                "UTC+1" => "UTC+1",
                "UTC+1.5" => "UTC+1:30",
                "UTC+2" => "UTC+2",
                "UTC+2.5" => "UTC+2:30",
                "UTC+3" => "UTC+3",
                "UTC+3.5" => "UTC+3:30",
                "UTC+4" => "UTC+4",
                "UTC+4.5" => "UTC+4:30",
                "UTC+5" => "UTC+5",
                "UTC+5.5" => "UTC+5:30",
                "UTC+5.75" => "UTC+5:45",
                "UTC+6" => "UTC+6",
                "UTC+6.5" => "UTC+6:30",
                "UTC+7" => "UTC+7",
                "UTC+7.5" => "UTC+7:30",
                "UTC+8" => "UTC+8",
                "UTC+8.5" => "UTC+8:30",
                "UTC+8.75" => "UTC+8:45",
                "UTC+9" => "UTC+9",
                "UTC+9.5" => "UTC+9:30",
                "UTC+10" => "UTC+10",
                "UTC+10.5" => "UTC+10:30",
                "UTC+11" => "UTC+11",
                "UTC+11.5" => "UTC+11:30",
                "UTC+12" => "UTC+12",
                "UTC+12.75" => "UTC+12:45",
                "UTC+13" => "UTC+13",
                "UTC+13.75" => "UTC+13:45",
                "UTC+14" => "UTC+14"
            );
            
            $dropdown_timezone = '<select name="timezone" id="saab-timezone" class="form-control">';
            $dropdown_timezone .= '<optgroup label="Africa">';
            foreach ($timezones_Africa as $value_Africa => $label_africa) {
                $selected = ($value_Africa == $get_timezone_value) ? 'selected="selected"' : '';
                $dropdown_timezone .= '<option value="' . esc_html( $value_Africa) . '" ' . esc_html( $selected ). '>' . esc_html($label_africa) . '</option>';

            }
            $dropdown_timezone .= '</optgroup>';
            
            $dropdown_timezone .= '<optgroup label="America">';
            foreach ($timezones_America as $value_America => $label_America) {
                $selected = ($value_America == $get_timezone_value) ? 'selected="selected"' : '';
                $dropdown_timezone .= '<option value="' . esc_html($value_America) . '" ' . esc_html($selected) . '>' . esc_html($label_America) . '</option>';
            }
            $dropdown_timezone .= '</optgroup>';

            $dropdown_timezone .= '<optgroup label="Antarctica">';
            foreach ($timezones_Antarctica as $value_Antarctica => $label_Antarctica) {
                $selected = ($value_Antarctica == $get_timezone_value) ? 'selected="selected"' : '';
                $dropdown_timezone .= '<option value="' . esc_html($value_Antarctica) . '" ' . esc_html($selected ). '>' . esc_html($label_Antarctica) . '</option>';
            }
            $dropdown_timezone .= '</optgroup>';

            $dropdown_timezone .= '<optgroup label="Arctic">';
            $selected = ("Arctic" == $get_timezone_value) ? 'selected="selected"' : '';
            $dropdown_timezone .= '<option value="Arctic/Longyearbyen"' . esc_html($selected ). '>Longyearbyen</option>';
            $dropdown_timezone .= '</optgroup>';

            $dropdown_timezone .= '<optgroup label="Asia">';
            foreach ($timezones_Asia as $value_Asia => $label_Asia) {
                $selected = ($value_Asia == $get_timezone_value) ? 'selected="selected"' : '';
                $dropdown_timezone .= '<option value="' . esc_html($value_Asia) . '" ' . esc_html($selected) . '>' . esc_html($label_Asia) . '</option>';
            }
            $dropdown_timezone .= '</optgroup>';

            $dropdown_timezone .= '<optgroup label="Atlantic">';
            foreach ($timezones_Atlantic as $value_Atlantic => $label_Atlantic) {
                $selected = ($value_Atlantic == $get_timezone_value) ? 'selected="selected"' : '';
                $dropdown_timezone .= '<option value="' . esc_html($value_Atlantic) . '" ' . esc_html($selected) . '>' . esc_html($label_Atlantic) . '</option>';
            }
            $dropdown_timezone .= '</optgroup>';

            $dropdown_timezone .= '<optgroup label="Australia">';
            foreach ($timezones_Australia as $value_Australia => $label_Australia) {
                $selected = ($value_Australia == $get_timezone_value) ? 'selected="selected"' : '';
                $dropdown_timezone .= '<option value="' . esc_html($value_Australia) . '" ' . esc_html($selected). '>' . $label_Australia . '</option>';
            }
            $dropdown_timezone .= '</optgroup>';

            $dropdown_timezone .= '<optgroup label="Europe">';
            foreach ($timezones_Europe as $value_Europe => $label_Europe) {
                $selected = ($value_Europe == $get_timezone_value) ? 'selected="selected"' : '';
                $dropdown_timezone .= '<option value="' . esc_html($value_Australia) . '" ' . $selected . '>' . esc_html($label_Australia) . '</option>';

            }
            $dropdown_timezone .= '</optgroup>';

            $dropdown_timezone .= '<optgroup label="Indian">';
            foreach ($timezones_Indian as $value_Indian => $label_Indian) {
                $selected = ($value_Indian == $get_timezone_value) ? 'selected="selected"' : '';
                $dropdown_timezone .= '<option value="' . esc_html($value_Europe) . '" ' . esc_html($selected) . '>' . esc_html($label_Europe) . '</option>';
            }
            $dropdown_timezone .= '</optgroup>';
            $dropdown_timezone .= '<optgroup label="Pacific">';
            foreach ($timezones_Pacific as $value_Pacific => $label_Pacific) {
                $selected = ($value_Pacific == $get_timezone_value) ? 'selected="selected"' : '';
                $dropdown_timezone .= '<option value="' . esc_html($value_Pacific) . '" ' . esc_html($selected) . '>' . esc_html($label_Pacific) . '</option>';
            }
            $dropdown_timezone .= '</optgroup>';

            $dropdown_timezone .= '<optgroup label="UTC">';
            $selected = ("UTC" == $get_timezone_value) ? 'selected="selected"' : '';
            $dropdown_timezone .= '<option value="' . esc_attr("UTC") . '" ' . selected($selected, "UTC", false) . '>UTC</option>';
            $dropdown_timezone .= '</optgroup>';

            $dropdown_timezone .= '<optgroup label="UTC">';
            foreach ($timezones_UTC as $value_UTC => $label_UTC) {
                $selected = ($value_UTC == $get_timezone_value) ? 'selected="selected"' : '';
                $dropdown_timezone .= '<option value="' . esc_html($value_UTC) . '" ' . esc_html($selected) . '>' . esc_html($label_UTC) . '</option>';
            }
            $dropdown_timezone .= '</optgroup>';
            $dropdown_timezone .= '</select>';
            
            
            return $dropdown_timezone;
        }
        
    }           
    add_action( 'plugins_loaded', function() {
        $SAAB_Admin_Fieldmeta = new SAAB_Admin_Fieldmeta();
    } );
 }
?>
