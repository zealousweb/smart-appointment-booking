<?php
/**
 * SAB_Admin_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @package Smart Appointment & Booking
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'SAB_Admin_Action' ) ) {

	/**
	 *  The SAB_Admin_Action Class
	 */
	class SAB_Admin_Action {

		function __construct()  {
			add_action( 'init',array( $this, 'action_init_sab' ));
			add_action( 'admin_enqueue_scripts',array( $this, 'enqueue_styles' ));
			add_action( 'admin_enqueue_scripts',array( $this, 'enqueue_scripts' ));
			add_action('admin_menu',array( $this, 'sab_add_post_type_menu' ));
				
			add_action( 'wp_ajax_sab_save_form_data', array( $this, 'sab_save_form_data' ));
			add_action('manage_sab_form_builder_posts_custom_column', array( $this, 'populate_custom_column' ), 10, 2);
			add_action('manage_manage_entries_posts_custom_column', array( $this, 'populate_custom_column' ), 10, 2);

			add_action('wp_ajax_zfb_preiveiw_timeslot', array( $this, 'zfb_preiveiw_timeslot' ) );
			add_action('wp_ajax_nopriv_zfb_preiveiw_timeslot', array( $this, 'zfb_preiveiw_timeslot' ) );

			add_action( 'wp_ajax_zfb_save_new_notification', array( $this, 'zfb_save_new_notification' ));
			add_action('wp_ajax_nopriv_zfb_save_new_notification', array( $this, 'zfb_save_new_notification' ) );

			add_action('init', array( $this, 'add_notification_capability' ) );
			add_action('admin_enqueue_scripts',  array( $this, 'enqueue_admin_scripts' ), 10, 2);
			
			add_action('wp_ajax_delete_notification_indexes', array( $this, 'delete_notification_indexes' ) );

			add_action( 'wp_ajax_zfb_update_notification_state', array( $this, 'zfb_update_notification_state' ));
			add_action('wp_ajax_nopriv_zfb_update_notification_state', array( $this, 'zfb_update_notification_state' ) );

			add_action('wp_ajax_zfb_save_user_mapping', array( $this, 'zfb_save_user_mapping' ) );
			add_action('wp_ajax_nopriv_zfb_save_user_mapping', array( $this, 'zfb_save_user_mapping' ) );

			add_action( 'wp_ajax_zfb_save_confirmation', array( $this, 'zfb_save_confirmation' ));
			add_action('wp_ajax_nopriv_zfb_save_confirmation', array( $this, 'zfb_save_confirmation' ) );

			add_action('edit_form_after_title', array( $this, 'disable_title_editing_for_custom_post_type' ) );

			add_action('wp_ajax_update_form_entry_data', array( $this, 'update_form_entry_data' ) );
			add_action('wp_ajax_nopriv_update_form_entry_data', array( $this, 'update_form_entry_data' ) );
			
			add_action( 'restrict_manage_posts', array( $this, 'add_custom_booking_status_filter' ) );
			add_action( 'pre_get_posts', array( $this, 'filter_custom_booking_status' ) );
			
			add_action('post_submitbox_misc_actions', array( $this, 'modify_submitdiv_content' ) );
			add_action('delete_post', array( $this, 'check_waiting_list_on_trashed_delete' ) );

			add_action('wp_ajax_get_paginated_items_for_waiting_list', array( $this, 'get_paginated_items_for_waiting_list' ) );
			add_action('wp_ajax_nopriv_get_paginated_items_for_waiting_list', array( $this, 'get_paginated_items_for_waiting_list' ) );

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
		* WP Enqueue Styles
		*/
		function action_init_sab(){
			wp_register_style( SAB_PREFIX . '_admin_min_css', SAB_URL . 'assets/css/admin.min.css', array(), SAB_VERSION );
			wp_register_style( SAB_PREFIX . '_admin_css', SAB_URL . 'assets/css/admin.css', array(), SAB_VERSION );
		}

	
		function enqueue_styles() {

			if(isset($_GET['post'])){
				$postid = $_GET['post'];
				$post_type = get_post_type($postid);
			}else{
				$post_type = '';
			}

			
			if (is_singular('sab_form_builder') || is_singular('zeal_formbuilder') || (isset($post_type) && ($post_type == 'sab_form_builder' || $post_type == 'manage_entries')) || (isset($_GET['post_type']) && ($_GET['post_type'] === 'sab_form_builder' || $_GET['post_type'] === 'manage_entries'))) {
				wp_enqueue_style( '_admin_css',SAB_URL.'assets/css/admin.css', array(), 1.1, 'all' );	
				wp_enqueue_style( 'sab_font-awesomev1','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', array(), 1.1, 'all' );
				//formio
				wp_enqueue_style( 'sab_formio_full_min',SAB_URL.'assets/css/formio/formio.full.min.css', array(), 1.1, 'all' );
				//boostrap
				wp_enqueue_style( 'sab_boostrap_min',SAB_URL.'assets/css/boostrap/boostrap.min.css', array(), 1.1, 'all' );	
			 }
			 if (isset($_GET['page']) && $_GET['page'] === 'notification-settings') {
				//boostrap
				wp_enqueue_style( 'datatable_admin_css',SAB_URL.'assets/css/boostrap/jquery.dataTables.min.css', array(), 1.1, 'all' );
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

			if (is_singular('sab_form_builder') || is_singular('zeal_formbuilder') || $post_type == 'sab_form_builder' || isset($_GET['post_type']) && $_GET['post_type'] === 'sab_form_builder' || isset($_GET['post_type']) && $_GET['post_type'] === 'manage_entries' ||  $post_type == 'manage_entries') {
				//boostrap folder
				wp_enqueue_script( 'sab_popper.minjs', SAB_URL.'assets/js/boostrap/popper.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'sab_jquery-3.7.0.slim.min', SAB_URL.'assets/js/boostrap/jquery-3.7.0.slim.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'sab_jquery-3.7.0.min',SAB_URL.'assets/js/boostrap/jquery-3.7.0.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'sab_boostrap.min', SAB_URL.'assets/js/boostrap/boostrap.min.js', array( 'jquery' ), 1.1, false );				
				wp_enqueue_script( 'sab_boostrap_bundlemin', SAB_URL.'assets/js/boostrap/boostrap.bundle.min.js', array( 'jquery' ), 1.1, false );

				//formio folder
			 	wp_enqueue_script( 'sab_formio_full_min', SAB_URL.'assets/js/formio/formio.full.min.js', array( 'jquery' ), 1.1, false );
				
				//booking folder
				wp_enqueue_script( 'booking-form', SAB_URL.'assets/js/booking/booking-form.js', array( 'jquery' ), 1.1, false );
				wp_localize_script('booking-form', 'ajax_object', array(
					'ajax_url' => admin_url('admin-ajax.php')
				));
	
				wp_enqueue_script( 'admin', SAB_URL.'assets/js/admin.js', array( 'jquery' ), 1.1, false );
			 }
			 if (isset($_GET['page']) && $_GET['page'] === 'notification-settings') {
				//boostrap folder
				wp_enqueue_script( 'datatble_admin',SAB_URL.'assets/js/boostrap/jquery.dataTables.min.js',array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'datatbleboostrap',SAB_URL.'assets/js/boostrap/dataTables.boostrap5.min.js',array( 'jquery' ), 1.1, false );
			 }

			 wp_register_script( SAB_PREFIX . '_admin_js', SAB_URL . 'assets/js/admin.min.js', array( 'jquery-core' ), SAB_VERSION );

		}

		function enqueue_admin_scripts() {
			wp_enqueue_script('jquery-ui-tabs');
		}
		// Add capability to user role
		function add_notification_capability() {
			$role = get_role('administrator'); 
			$role->add_cap('edit_notifications'); 
		}
		/**
		 * save new notification
		*/
		function zfb_save_new_notification() {
			$response = array(
				'success' => false,
				'message' => 'Invalid request.',
			);
			$get_notification_array = array();
			if (isset($_POST['notification_data'])) {
			
				parse_str($_POST['notification_data'], $form_data);
				$post_id = $form_data['form_id'];
               	$index = $form_data['editnotify'];
				$mail_body='mail_body' . $index;
				
				$notification_data = array(
					'form_id' => sanitize_text_field($form_data['form_id']),
					'notification_name' => sanitize_text_field($form_data['notification_name']),
					'state' => sanitize_text_field($form_data['state']),
					'type' => sanitize_text_field($form_data['type']),
					'to' => sanitize_text_field($form_data['email_to']),
					'from' => sanitize_text_field($form_data['email_from']),
					'replyto' => sanitize_text_field($form_data['email_replyto']),
					'bcc' => sanitize_text_field($form_data['email_bcc']),
					'cc' => sanitize_text_field($form_data['email_cc']),
					'subject' => sanitize_text_field($form_data['email_subject']),
					'additional_headers' => sanitize_textarea_field($form_data['additional_headers']),
					'mail_body' => wp_kses_post($form_data[$mail_body]),
					'use_html' => sanitize_text_field($form_data['use_html']),
				);
			
				if ($post_id) {
					$get_notification_array = get_post_meta($post_id, 'notification_data', true);
		
					// Verify if the meta key exists and contains data
					if (!empty($get_notification_array)) {
						if (isset($get_notification_array[$index])) {
							// Update existing array element
							$get_notification_array[$index] = $notification_data;
						} else {
							if(is_string($get_notification_array)){
								update_post_meta($post_id, 'notification_data','');
								$get_notification_array[] = $notification_data;
								update_post_meta($post_id, 'notification_data', $get_notification_array);
							}else{
								$get_notification_array[] = $notification_data;
							}
							
							
						}		
						update_post_meta($post_id, 'notification_data', $get_notification_array);
					} else {
						$get_notification_array = array($notification_data);
						update_post_meta($post_id, 'notification_data', $get_notification_array);
					}
		
					$response = array(
						'success' => true,
						'message' => 'Notification saved successfully.',
					);
				} else {
					$response = array(
						'success' => false,
						'message' => 'Invalid post ID',
					);
				}
			} else {
				// Return the failure response if the request is invalid
				$response = array(
					'success' => false,
					'message' => 'Invalid request.',
				);
			}
			wp_send_json_success( $response);
			exit;
			
		}

		/**
		 * Add custom post types and admin menu items for WP Smart Appointment & Booking plugin.
		 *
		 * This function registers two custom post types: 'sab_form_builder' for generating appointment and booking forms,
		 * and 'manage_entries' for managing entries submitted through the forms.
		 *
		 * It also adds menu items to the admin dashboard for easy access to the plugin's functionality.
		 */
		function sab_add_post_type_menu() {
			
			$labels_form = array(
				'name' => 'Generate Appointment and Booking Forms',
				'singular_name' => 'Generate Appointment and Booking Form',
			);
		
			$args_form = array(
				'labels' => $labels_form,
				'description' => '',
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'delete_with_user' => false,
				'show_in_rest' => false,
				'rest_base' => '',
				'has_archive' => false,
				'show_in_menu' => true, 
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
			register_post_type('sab_form_builder', $args_form);

			$labels = array(
				'name' => 'Manage Entries',
				'singular_name' => 'Manage Entry',
			);
		
			$args = array(
				'labels' => $labels,
				'description' => '',
				'public' => true,
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
		
			register_post_type('manage_entries', $args);
		
			$menu_hook_suffix = add_menu_page(
				'WP Smart A&B',
				'WP Smart A&B',
				'manage_options',
				'edit.php?post_type=sab_form_builder',
				'',
				'data:image/svg+xml;base64,' . base64_encode( '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
						viewBox="0 0 1024 1024" style="enable-background:new 0 0 1024 1024;" xml:space="preserve">
					<style type="text/css">
						.st0{fill:#A7AAAD;}
						.st1{fill-rule:evenodd;clip-rule:evenodd;fill:#A7AAAD;}
					</style>
					<g>
						<path class="st0" d="M762.9,819.7h-54v9.9c0,18.7,15.2,33.9,33.9,33.9h54v-9.9C796.9,834.9,781.7,819.7,762.9,819.7z"/>
						<path class="st0" d="M660.4,819.7h-54v9.9c0,18.7,15.2,33.9,33.9,33.9h54v-9.9C694.3,834.9,679.1,819.7,660.4,819.7z"/>
						<path class="st0" d="M557.8,819.7h-54v9.9c0,18.7,15.2,33.9,33.9,33.9h54v-9.9C591.7,834.9,576.5,819.7,557.8,819.7z"/>
						<path class="st1" d="M263.4,443.9c-53.1,63.5-80,128.5-77.8,187.8v0c-0.1,1.8-0.3,3.5-0.3,5.2c-1-3.1-2.1-6.9-3.4-11.3
							c-3-10.4-4.7-20.4-5.6-25.5c-0.1-0.7-0.2-1.2-0.3-1.7c-0.5-2.8-2.1-15.7-3.9-29.3c-1-8-2.1-16.2-3-22.7c-2.3-17.5-6.8-36.3-10.1-49
							c-3.3-12.7-8.9-26.1-14.4-38.5c-5.5-12.4-13.9-27.4-20.3-38.3c-6.4-10.8-15.2-21.2-15.2-21.2c-26.6-30.9-49.7-32.8-49.7-32.8
							c-22.6-5.5-42-1.4-42-1.4c3.7-22.6,10.7-35.9,17.1-47.9c0.3-0.7,0.7-1.3,1-1.9c6.7-12.5,17.9-24,24.2-29.3c0.2-0.2,0.5-0.4,0.8-0.7
							c7.9-6.7,39.6-33.6,95.2-33.1c57.8,0.5,85.6,23.6,100.5,38.4c14.9,14.7,32.7,40.1,37.3,46.8c4.6,6.6,13.3,20.2,13.3,20.2
							s8.1,13,15.8,25.2C300.2,403.1,280.4,423.4,263.4,443.9z M118,315.4c0,6.1-4.8,11-10.7,11s-10.7-4.9-10.7-11c0-6.1,4.8-11,10.7-11
							C113.2,304.4,118,309.3,118,315.4z"/>
						<path class="st0" d="M722.1,164.1c-53.8,24.3-102.2,48-146.1,71.3c-179.1,95.2-279.1,185.4-329,263c11.9-13.7,25.5-27.1,40.7-40
							C373,386.1,505.3,330.7,681,293.6l10.7-2.2c19.7-59.2,32.3-105.3,36.9-123C729.7,164.8,725.7,162.7,722.1,164.1z"/>
						<path class="st0" d="M652.7,400c5.2-12.5,11-29.4,17.1-45.5c0.9-2.7,1.9-5.7,3.1-8.1c4.7-12.7,8.8-24.5,11.9-33.6
							c-294.7,62.1-420.8,167.6-462.9,258C290.4,481.7,385.6,420.9,652.7,400z"/>
						<path class="st0" d="M643.4,417.6c-151.8,14.7-267.6,47-344.5,96.2c-39.2,25.1-63.5,51.5-78.3,76.4c-28.4,47.7-20.7,109.4,21,146
							c8.1,7.1,16.5,12.4,24.4,16.1c22.1,10.2,44.3,15.3,66,15.3c61.1,0,123.1-40.6,184.2-120.7C560.1,589.2,602.9,512.2,643.4,417.6z"/>
						<path class="st0" d="M720.2,413.3c-19.5,0.5-38.3,1.3-56.4,2.3l-12.3,27.7C610,537.3,566,614.3,520.8,672
							c-17.9,22.8-35.9,42.5-54.1,59.2c116.5-77.3,232.6-223.3,301-318.7C751.7,412.5,735.6,412.8,720.2,413.3L720.2,413.3z"/>
						<path class="st0" d="M782.5,394.5c24.1-37.4,48.7-80.4,61.1-101.1c2.4-4-0.3-9.4-4.4-8.7c-50.1,7.7-116,20.4-135.3,24.2
							c-3.3,10-7.2,21.4-11.5,33.7l-0.4,1c-0.8,1.8-1.7,4.2-2.4,6.6l-0.7,2c-3.2,8.8-12.9,32.3-18.3,46.3
							C692.3,396.7,732.6,394.3,782.5,394.5L782.5,394.5z"/>
						<path class="st0" d="M220.2,702c-0.3-0.3,0.1,0.6,1.3,2.3C221.1,703.5,220.6,702.8,220.2,702z"/>
						<path class="st0" d="M594.3,644.1c24.4-16.6,49.7-35.1,75.9-55.5c80.8-62.9,150-128.2,189.1-166.7c2.1-2,0.7-5.7-2.2-5.7
							c-26.4-0.5-54.7-0.7-71.3-0.9C739.7,480,670.7,569.4,594.3,644.1z"/>
						<path class="st0" d="M335.3,776.8c-1.8,0.1-3.5,0.1-5.2,0.1c1,0,2,0,3,0C333.9,776.9,334.6,776.8,335.3,776.8L335.3,776.8z"/>
						<path class="st0" d="M325.6,776.7c1,0,2.1,0.1,3.2,0.1c0.1,0,0.2,0,0.4,0C327.9,776.9,326.7,776.8,325.6,776.7L325.6,776.7z"/>
						<path class="st0" d="M307.3,775.2h-0.5c11.3,4.9,23.1,8.8,35,11.7c27.9,7.8,103.9,29.4,124.2,37.5c0,0-43.9-23-55.3-31.3h-0.2
							c7.6-0.5,22.4-1.9,30-3.2c30.2,6.4,31.7,9.5,49.8,16.7c0,0-29.3-21.8-29.8-21.8c35.5-10,69.4-28.4,97.7-55.1
							c1.4-1.5,2.8-2.7,4.3-4.2c27.2-25.7,53.1-39.7,63.2-44.8c17.6-9.1,71.3-36,153.5-43.1c-47.2-2.2-88.8-6.1-122.3-10.8
							C519.8,733.5,413.4,786.5,307.3,775.2L307.3,775.2z"/>
						<path class="st0" d="M935.6,665.2c-82.8-35.5-217.4-24.2-311,41c9.3-1.9,19.4-3,30.2-3c9.1,0,18.6,0.8,28.5,2.7
							c27.4,5.2,55.4,21.9,85,39.5c33.7,20,68.6,40.8,109.1,50.1l0.3,0.1c4.1,1.1,7.9,2.1,11.6,2.9c38.6,7.5,83.3-3.8,104-26.2
							c7.9-8.5,11.4-17.9,10.6-27.8C1002.2,721,988.9,688,935.6,665.2z"/>
					</g>
					</svg>' ),
				20
			);
			

			add_submenu_page(
				'edit.php?post_type=sab_form_builder', 
				'Add New Form', 
				'Add New Form', 
				'manage_options', 
				admin_url('post-new.php?post_type=sab_form_builder')
			);
		
			add_submenu_page(
				'edit.php?post_type=sab_form_builder',
				'Manage Entries',
				'Manage Entries',
				'manage_options',
				'edit.php?post_type=manage_entries'
			);
			
			add_submenu_page(
				$menu_hook_suffix, 
				'Notification Settings',
				'Notification Settings', 
				'manage_options', 
				'notification-settings', 
				array($this, 'render_notification_settings_page')
			);
			
		}
		/**
		 * 
		 * view booking entry
		 */
		function view_booking_entry( $post ){
            $post_id = $_GET['post_id'] ;
            $form_data = get_post_meta( $post_id, 'sab_submission_data', true );	
            $form_id = get_post_meta( $post_id, 'sab_form_id', true );	
            $timeslot = get_post_meta( $post_id, 'timeslot', true );
            $booking_date = get_post_meta( $post_id, 'booking_date', true );
            $array_of_date = explode('_',$booking_date);
            $bookedmonth = $array_of_date[2];
            $bookedday =$array_of_date[3];
            $bookedyear =$array_of_date[4];
            $booked_date = $bookedday."-".$bookedmonth."-".$bookedyear;
			$booked_date = date('F j, Y', strtotime($booked_date));	
            $slotcapacity = get_post_meta( $post_id, 'slotcapacity', true );	

            if(!empty($form_id)){ 
               $booking_form_title = get_the_title($form_id);               
            }
            $date_generated = get_the_date('d/m/Y',$post_id);
            $status = get_post_meta( $post_id, 'entry_status', true );
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
            }elseif($status == 'waiting'){
                $status = "Waiting";
            }
            ?>
			
			<div class="entry_title">
				<div class="entries_title_main">
					<?php
					if (isset($_GET['post_id'])) {
						$post_id = absint($_GET['post_id']); 
						$title = get_the_title($post_id);
						echo '<p class="entry-title h5">' . esc_html($title) . '</p>';

						if (current_user_can('edit_post', $post_id)) {
							$edit_post_link = get_edit_post_link($post_id);
							if ($edit_post_link) {
								?>
								<a href="<?php echo esc_url($edit_post_link); ?>" class="edit-link">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
										<path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
										<path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
									</svg>
									Edit
								</a>
								<?php
							}
						} else {
							error_log('You do not have permission to edit this post.');
						}
						
						$published_date = get_the_date( 'F j, Y @ h:i a', $post_id );
						echo '<p class="published_on">Published on ' . $published_date. '</p>';
					} else {
						error_log('Invalid post ID.');
					}
					?>
				</div>
			</div>

			<div class="main-entries-section" id="main_entries_section1">
				<table id="main_entries_table1">
					<tr>
						<th class="h6"><?php echo __('Form Title', 'smart-appointment-booking'); ?></th>
						<td><?php echo $booking_form_title; ?></td>
					</tr>
					
					<tr>
						<th class="h6"><?php echo __('Status', 'smart-appointment-booking'); ?></th>
						<td><?php echo $status; ?></td>
					</tr>
					<tr>
						<th class="h6"><?php echo __('Customer', 'smart-appointment-booking'); ?></th>
						<td><?php echo __('Guest', 'smart-appointment-booking'); ?></td>
					</tr>
					<tr>
						<th class="h6"><?php echo __('Booked Date', 'smart-appointment-booking'); ?></th>
						<td><?php echo $booked_date; ?></td>
					</tr>
					<tr>
						<th class="h6"><?php echo __('Timeslot', 'smart-appointment-booking'); ?></th>
						<td><?php echo $timeslot; ?></td>
					</tr>
					<tr>
						<th class="h6"><?php echo __('No of Slots Booked', 'smart-appointment-booking'); ?></th>
						<td><?php echo $slotcapacity; ?></td>
					</tr>
				</table>
			</div>

			<div class="main-entries-section" id="main_entries_section2">
				
				<table id="main_entries_table2">
					<?php
					foreach($form_data['data'] as $form_key => $form_value){
						if($form_key !== 'submit'){
							echo "<tr>"
							. "<th class='h6'>" . ucfirst($form_key) . "</th>"
							. "<td>" . htmlspecialchars($form_value) . "</td>"
							. "</tr>";
						
						}
					}
					?>
				</table>
			</div>

			<div class="main-entries-section" id="main_entries_section3">
				<h6>Notes</h6>
				<?php 
				$notes = get_post_meta($post_id, 'notes', true);
				echo esc_textarea($notes);
				?>
			</div>
            <?php
        }
		/**
		 * Update booking form entries in backend
		 */
		function update_form_entry_data(){

			if (isset($_POST['entry_id']) && isset($_POST['updated_data']) ) {
				$entry_id = $_POST['entry_id'];
				$updated_data = $_POST['updated_data'];
				$get_submitted_data = get_post_meta($entry_id, 'sab_submission_data', true);
				$updated_data = $_POST['updated_data'];
				echo "<pre>";
				print_r($get_submitted_data);
				foreach ($updated_data as $key => $value) {
					if (isset($get_submitted_data['data'][$key])) {
						$get_submitted_data['data'][$key] = $value;
					}
				}
				update_post_meta($entry_id, 'sab_submission_data', $get_submitted_data);
			}
            wp_die();
        }
		/**
		 * Admin : get key value pair of shortcodes to send in manual notification or post update
		 */
		function admin_get_shortcodes_keylabel($post_id){
			$shortcode_list = array();
			$form_data = get_post_meta( $post_id, '_formschema', true ); 
			if(isset($form_data) && !empty($form_data)){
				$form_data=json_decode($form_data);
				foreach ($form_data as $obj) {     
					$shortcode_list[] = array(
						'fieldkey'=>esc_attr($obj->key),
						'fieldlabel'=>esc_html($obj->label),
					);
				   
				}
			}
			
			return $shortcode_list;
		}
		/**
		 * Render notification setting page
		 */
		function render_notification_settings_page() {
			// Add your page content here
			echo "<div class='notification-page-main m-4 p-1 ' >";
			
			if (isset($_GET['post_type']) && isset($_GET['post_id'])) {
				$post_type = $_GET['post_type'];
				$post_id = $_GET['post_id'];
			
				?>
				<ul class="nav nav-tabs" id="myTabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="tab_fieldmapping" data-bs-toggle="tab" href="#content_fieldmapping" role="tab" aria-controls="content_fieldmapping" aria-selected="true">Field Mapping</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tab_notification" data-bs-toggle="tab" href="#content_notification" role="tab" aria-controls="content_notification" aria-selected="false">Notification</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tab_confirmation" data-bs-toggle="tab" href="#content_confirmation" role="tab" aria-controls="content_confirmation" aria-selected="false">Confirmation</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tab_documentation" data-bs-toggle="tab" href="#content_documentation" role="tab" aria-controls="content_documentation" aria-selected="false">Documentation</a>
					</li>
					
				</ul>

				<div class="tab-content p-4 border" id="myTabContent">
					<div class="tab-pane fade show active " id="content_fieldmapping" role="tabpanel" aria-labelledby="tab_fieldmapping">
						<?php
						$form_data = get_post_meta( $post_id, '_formschema', true ); 
						$form_data=json_decode($form_data);
						$shortcodes = $this->admin_get_shortcodes_keylabel($post_id);
						?>
						<div class="row">
							<div class="col col-md-3">	

								<?php
								$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
								$user_mapping = get_post_meta($post_id, 'user_mapping', true);
								if ($user_mapping) {
									$first_name = isset($user_mapping['first_name']) ? sanitize_text_field($user_mapping['first_name']) : '';
									$last_name = isset($user_mapping['last_name']) ? sanitize_text_field($user_mapping['last_name']) : '';
									$email = isset($user_mapping['email']) ? sanitize_text_field($user_mapping['email']) : '';
									$service = isset($user_mapping['service']) ? sanitize_text_field($user_mapping['service']) : '';
									$cancel_bookingpage = isset($user_mapping['cancel_bookingpage']) ? sanitize_text_field($user_mapping['cancel_bookingpage']) : '';
								} else {
									$first_name = '';
									$last_name = '';
									$email = '';
									$service = '';
									$cancel_bookingpage = '';
								}
								?>
								<form id="usermap_form" method="post" data-pid="">
									<input type="hidden" name="post_id" id="post_id" value="<?php echo esc_attr($post_id); ?>">
									<div class="form-row">
										<div class="form-group col-md-6">
											<label class="h6" for="first-name">First Name:</label>
											<select class="form-control" id="first-name" name="first_name">
												<option value="any" disabled>Any</option>
												<?php 													
													$fieldFirstName = $this->admin_get_shortcodes_keylabel($post_id);
													foreach ($fieldFirstName as $option) {
														$fieldKey = $option['fieldkey'];
														$fieldLabel = $option['fieldlabel'];
														$selected = ($fieldKey == $first_name) ? 'selected' : '';
														echo '<option value="' . esc_attr($fieldKey) . '" ' . $selected . '>' . esc_html($fieldLabel) . '</option>';
													}
												?>
											</select>
										</div>
										<div class="form-group col-md-6">
											<label class="h6" for="last-name">Last Name:</label>
											<select class="form-control" id="last-name" name="last_name">
												<option value="any" disabled>Any</option>
												<?php 													
													$fieldLastName = $this->admin_get_shortcodes_keylabel($post_id);
													foreach ($fieldLastName as $option) {
														$fieldKey = $option['fieldkey'];
														$fieldLabel = $option['fieldlabel'];
														$selected = ($fieldKey == $last_name) ? 'selected' : '';
														echo '<option value="' . esc_attr($fieldKey) . '" ' . $selected . '>' . esc_html($fieldLabel) . '</option>';
													}
												?>
											</select>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col">
											<label class="h6" for="email">Email:</label>
											<select class="form-control" id="email" name="email">
											<option value="any" disabled>Any</option>
											<?php 													
												$fieldEmail = $this->admin_get_shortcodes_keylabel($post_id);
												foreach ($fieldEmail as $option) {
													$fieldKey = $option['fieldkey'];
													$fieldLabel = $option['fieldlabel'];
													$selected = ($fieldKey == $email) ? 'selected' : '';
													echo '<option value="' . esc_attr($fieldKey) . '" ' . $selected . '>' . esc_html($fieldLabel) . '</option>';
												}
											?>
											</select>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col">
											<label class="h6" for="service">Service:</label>
											<select class="form-control" id="service" name="service">
											<option value="any" disabled>Any</option>
											<?php 													
												$fieldService = $this->admin_get_shortcodes_keylabel($post_id);
												foreach ($fieldService as $option) {
													$fieldKey = $option['fieldkey'];
													$fieldLabel = $option['fieldlabel'];
													$selected = ($fieldKey == $service) ? 'selected' : '';
													echo '<option value="' . esc_attr($fieldKey) . '" ' . $selected . '>' . esc_html($fieldLabel) . '</option>';
												}
											?>
											</select>
										</div>
									</div>

									<div class="form-row">
										<div class="form-group col">
											<label class="h6" for="email">Cancel Booking Page:</label>
											<select name="cancel_bookingpage"  class="form-control" id="selected_page">
												<option value="">Select a page</option>
												<?php
												$pages = get_pages();
											

												foreach ($pages as $page) {
													$selected = $cancel_bookingpage == $page->ID ? 'selected' : '';
													echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
												}
												?>
											</select>
										</div>
									</div>
									<input type="submit" value="submit" class="btn btn-primary" name="Save">
									
								</form>
								<div>
									<p id="map_success" class="h6 m-2"></p>
								</div>
							</div>

							<div class="shortcodes_list col-md-6 m-4">
							
								<p class="h5 head-shortcode">Shortcodes for Notification</p>
								<p class="smal head-shortcode">Here is a list of available shortcodes to use in email notification mail body</p>
																
								<?php
								$form_data = $this->admin_get_shortcodes($post_id);
								echo '<div class=""><label style="font-weight: bold;">' . __('Form shortcodes', 'smart-appointment-booking') . '</label></div>';
								foreach ($form_data['form'] as $objform) {
									echo '<span class="copy-text" style="margin-right: 5px; font-family: Arial; font-size: 14px;">[' . $objform . ']</span>';
								}
								$enable_booking = get_post_meta($post_id, 'enable_booking', true);
								if( $enable_booking ){
									echo '<div class=""><label style="font-weight: bold;">' . __('Booking shortcodes', 'smart-appointment-booking') . '</label></div>';
									foreach ($form_data['booking'] as $objbooking) {
										echo '<span class="copy-text" style="margin-right: 5px;margin-bottom: 5px; font-family: Arial; font-size: 14px;">[' . $objbooking . ']</span>';
									}
								}
								echo '<div class=""><label style="font-weight: bold;">' . __('Post shortcodes', 'smart-appointment-booking') . '</label></div>';
								foreach ($form_data['post'] as $objpost) {
									echo '<span class="copy-text" style="margin-right: 5px; font-family: Arial; font-size: 14px;">[' . $objpost . ']</span>';
								}
								
								?>
							</div>
						</div>

					</div>
					<div class="tab-pane fade" id="content_notification" role="tabpanel" aria-labelledby="tab_notification">
						<div id="notify-main-content" class="notify-main-container">
							<!-- <h3>Notifcation</h3> -->
							<?php
							// Get the post status
							$status = get_post_status($post_id);
							
							// Check if the post is published
							if ($status === 'publish') {

								$get_no_of_notification = get_post_meta($post_id,'no_of_notification',true);
								$index='add';
								?>
								<div class="main-container-notification border border-light" >
									
									<div class="form-group">
										<!-- Button to trigger the modal -->
										<button type="button" class="btn btn-secondary" id="add_notify_btn" data-toggle="modal" data-target="#notifyModal<?php echo $index; ?>">Add New notification </button>
									</div>
									<!-- Modal -->
									<?php  $this->generateModal($index,$post_id); ?>
									
									<?php
									$notification_metadata = get_post_meta($post_id, 'notification_data', true);
									
									if (!empty($notification_metadata) && is_array($notification_metadata)) {
										$post_id = $_GET['post_id'];
										?>
										
										<div id="tab5" class="tab-content">
											<input type="hidden" name="post_id" id="post_id" value="<?php echo $_REQUEST['post_id']; ?>" >
											<table class="table notificationtable datatable table-striped" id="notifytable" >
												<thead>
													<tr>
														<th scope="col" ><input type="checkbox" id="main-check-all" class="maincheckall" value="1" ></th>
														
														<th scope="col">
														<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-alarm" viewBox="0 0 16 16">
																	<path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9V5.5z"/>
																	<path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1h-3zm1.038 3.018a6.093 6.093 0 0 1 .924 0 6 6 0 1 1-.924 0zM0 3.5c0 .753.333 1.429.86 1.887A8.035 8.035 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5zM13.5 1c-.753 0-1.429.333-1.887.86a8.035 8.035 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1z"/>
																</svg>	
														Notification</th>
														<th scope="col">Status</th>
														<th scope="col">State</th>
														<th scope="col">Actions</th>
														
													</tr>
												</thead>
												<tbody>
													<?php
													$ni=1;
													foreach ($notification_metadata as $index => $notification) {
														$notification_name = $notification['notification_name'];
														$state = $notification['state'];
														$notification_id = 'notify_' . $index;
														?>
														<tr>
															<td><input type="checkbox" id="zfb-check-all<?php echo $index; ?>" class="child-checkall" value="<?php echo $index; ?>"></td>
															<td>
																<?php 
																echo $ni."."; 
																$ni++;
																echo " ".$notification_name; 
																?>
															</td>
															<td>
																<span>
																	<?php 
																		
																		$booking_status = isset($notification['type']) ? $notification['type'] : '';
																	
																		echo $booking_status;
																	?>
																</span>
															</td>
															<td>
															<button type="button" class="btn btn-outline-dark enable-btn" data-notification-id="<?php echo $notification_id; ?>" data-notification-state="<?php echo $state; ?>">
															<?php echo ($state === 'enabled') ? 'Enabled' : 'Disabled'; ?> </button></td>
															<td> 
																<button type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#notifyModal<?php echo $index; ?>">
																<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
																	<path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
																	<path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
																</svg>
																	Edit
																</button>
																<!-- Modal -->
																<?php  $this->generateModal($index,$post_id); ?>
															</td>
															</tr>
														<?php
													}
													?>
												</tbody>
											</table>
											<button type="button" class="btn btn-danger" id="deletenotify">Delete</button>
										</div>
										<?php
									}else{
										echo 'No notification data found for the post.';
									}
									?>
								</div>
								<?php
							}else {
								echo 'Publish Post to Create Notification';
							}
								?>
						</div>
					</div>
				
					<div class="tab-pane fade" id="content_documentation" role="tabpanel" aria-labelledby="tab_documentation">
						<div class="shortcodes_list col-md-6 m-4">
							<ul>
								<li class="h6">Create Email Alerts for admin and as well for user with on following Status: Booked, Pending, Approved, Cancelled, Waiting.</li>
								<li>Map this required Fields with Form's field : Email Field helps to send email alert to user who submitted form</li>
								<li class="h6">New Booking Created: (When auto approve mode is enabled)</li>
								<li>When any user or admin makes a new booking, create an email notification containing the relevant details of the booking. This notification will indicate that the booking is booked and confirmed
								</li>
								<li class="h6">Booking Pending: (When auto approve mode is disabled)</li>
								<li>Create an email notification for user when a booking is approved by admin. This notification will indicate that the booking has been "Approved" and is confirmed.
								</li>
								<li class="h6">Booking Approved: (When auto approve mode is disabled)</li>
								<li>Create an email notification for user when a booking is approved by admin. This notification will indicate that the booking has been "Approved" and is confirmed.
								</li>
								<li class="h6">Booking Cancelled:</li>
								<li>In case a booking is cancelled either by an admin or a user, create an email alert providing you with the pertinent information. This notification will indicate that the booking has been "Cancelled" and will no longer be valid.
								</li>
							</ul>

							<ul>
								<li class="h6">Submitted : (Send Email on submitting Form) </li>
								<li>If the booking feature is not set up or disabled, you have the option to configure email notifications specifically for the "submitted" status. You can generate admin and user email alert.</li>
							</ul>
						</div>
					</div>
					<div class="tab-pane fade" id="content_confirmation" role="tabpanel" aria-labelledby="tab_confirmation">
						<?php
						$confirmation = get_post_meta($post_id, 'confirmation', true);  
						$redirect_text = get_post_meta($post_id, 'redirect_text', true);
						$redirect_to= get_post_meta($post_id, 'redirect_to', true);
						$hiddenredirect_text = '';
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
						<form id="confirm_form" method="post" >
						
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
							<div class="form-group redirectto_main redirect_to redirect_zfb <?php echo $hiddenredirect_to; ?> ">
								<label class="h6"><?php echo __('Enter Url: ', 'smart-appointment-booking'); ?></label>
								<input type="text" name="redirect_to" id="redirect-url" class="form-control" value="<?php echo esc_attr($redirect_to); ?>" pattern="https?://.+" style="width: 500px !important;" placeholder="Enter url with http or https">
								<small class="redirecturl-error" style="display:none;">Please enter a valid URL starting with http:// or https://</small>
							</div> 
							<input type="hidden" name="post_id" value="<?php echo $post_id;?>">
							<input type="submit" value="Save" class="btn btn-primary" name="Save">
						</form>
						<p id="confirm_msg" class="h6 m-2"></p>
					</div>
				</div>
				<?php
				$back_link = get_edit_post_link($post_id);
				?>
				<a href="<?php echo $back_link; ?>"><button type="button" class="btn btn-secondary mt-2" id="deletenotify">Back To Form Configuration</button></a>
				<?php
			} else {
				echo "Error: Post type and/or post ID not found.";
			}
			echo '</div>';
		}
		/**
		 * get shortcodes to display in page
		 */
		function admin_get_shortcodes($post_id){
			$form_list = array();
			$shortcode_list = array();
			$form_data1 = get_post_meta( $post_id, '_formschema', true ); 
			if(isset($form_data1) && !empty($form_data1)){
				$form_data1=json_decode($form_data1);
				foreach ($form_data1 as $obj) {  
					 if($obj->key !== 'submit'){				
					$form_list[] = $obj->key;
					 }
				}
			}
			$booking_shortcodes = array('BookingId','Status','To','FirstName','LastName','Timeslot','BookedSeats','BookingDate','BookedDate','Service','prefixlabel','cost','StartTime','EndTime','CancelBooking');
			$post_shortcodes = array('FormId','FormTitle');
			$shortcode_list = array(
					'form' => $form_list,
					'booking' => $booking_shortcodes,
					'post' => $post_shortcodes,
			);
			
			return $shortcode_list;
		}
		/**
		 * Update notification status: enable and disable
		 */
		function zfb_update_notification_state() {
			$response = array(
				'success' => false,
				'message' => 'Invalid request.',
			);
		
			if (isset($_POST['post_id'], $_POST['notification_id'], $_POST['new_state'])) {
				$post_id = $_POST['post_id'];
				$notification_id = $_POST['notification_id'];
				$index = ltrim($notification_id, "notify_");
				$new_state = $_POST['new_state'];
		
				// Get the existing notification metadata
				$notification_data = get_post_meta($post_id, 'notification_data', true);
				if ($notification_data) {
					if (isset($notification_data[$index])) {
						$notification_data[$index]['state'] = $new_state;
						update_post_meta($post_id, 'notification_data', $notification_data);
		
						$response['success'] = true;
						$response['message'] = __('Notification saved successfully', 'smart-appointment-booking');
						$response['state'] = $new_state;
					} else {
						$response['message'] = __('Something went wrong', 'smart-appointment-booking');
					}
				} else {
					$response['message'] = __('Something went wrong', 'smart-appointment-booking');
				}
			}
		
			wp_send_json($response);
		}
		/**
		 * delete Notification entry from backend
		 */
		function delete_notification_indexes() {
			if (isset($_POST['indexes'])) {
				$post_id = $_POST['post_id']; 
				$indexesToDelete = $_POST['indexes'];

				$notification_metadata = get_post_meta($post_id, 'notification_data', true);

				foreach ($indexesToDelete as $index) {
					if (isset($notification_metadata[$index])) {
						unset($notification_metadata[$index]);
					}
				}

				update_post_meta($post_id, 'notification_data', $notification_metadata);
				wp_send_json_success('Indexes deleted successfully.');
			} else {
				wp_send_json_error('Invalid request.');
			}
		}
		/**
		 * modal : add and edit new notification
		 */
		function generateModal($index,$post_id) {
			
			$mode = ($index === 'add') ? 'add' : '';
			$checkedit = ($index === 'add') ? 'add' : 'edit';
			$title = ($index === 'add') ? 'Add New Notification' : 'Edit Notification';
			$notificationName = '';
			$state = '';
			$type = '';
			$email_to = '';
			$email_from = '';
			$email_replyto = '';
			$email_bcc = '';
			$email_cc = '';
			$email_subject = '';
			$mail_body = '';
			$use_html = '';

			$data = get_post_meta($post_id, 'notification_data', true);
			
			if ($checkedit === 'edit' && isset($data[$index])) {
				$title = 'Edit Notification';
				$item = $data[$index];
				$notificationName = isset($item['notification_name']) ? $item['notification_name'] : '';
				$state = isset($item['state']) ? $item['state'] : '';
				$type = isset($item['type']) ? $item['type'] : '';
				$email_to = isset($item['to']) ? $item['to'] : '';
				$email_from = isset($item['from']) ? $item['from'] : '';
				$email_replyto = isset($item['replyto']) ? $item['replyto'] : '';
				$email_bcc = isset($item['bcc']) ? $item['bcc'] : '';
				$email_cc = isset($item['cc']) ? $item['cc'] : '';
				$email_subject = isset($item['subject']) ? $item['subject'] : '';
				$mail_body = isset($item['mail_body']) ? $item['mail_body'] : '';
				$use_html =  isset($item['use_html']) ? $item['use_html'] : '';
			}
			
			?>
			<div class="modal fade notification-modal" id="notifyModal<?php echo $index; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $index; ?>" aria-hidden="true">
				<div class="modal-dialog modal-lg notification-mdialog modal-dialog-scrollable">
					<div class="modal-content notification-mcontent">
						
							
							<input type="hidden" value="<?php echo $post_id; ?>" name="form_id">
							<!-- Modal header -->
							<div class="modal-header">
								<h4 class="modal-title" id="myModalLabel"><?php echo $title; ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							</div>
							
							<!-- Modal body -->
							<div class="modal-body notification-mdialog" style="max-height: 100%;overflow-y: auto;">
								<form class="notifyform" data-id ="<?php echo $index; ?>" method="post">
									<div class="border p-4 m-1">
										<h5>General Notification Setting</h5>
										<input type="hidden" value="<?php echo $index; ?>" name="editnotify" >
										<div class="form-group">
											<input type="hidden" name="form_id" value="<?php echo $post_id; ?>">
										</div>
										<div class="form-group">
											<label for="notification-name">Notification Name</label>
											<input type="text" value="<?php echo $notificationName; ?>" id="notification-name" name="notification_name" class="form-control" placeholder="Enter Notification Title" required>
										</div>

										<div class="form-group">
											<label for="state">State</label>
											<div class="form-check">
												<input class="form-check" type="radio" name="state" id="disable" value="disabled"<?php echo ($state === 'disabled' || $state === 'disable') ? 'checked' : ''; ?>>
												<label class="form-check-label" for="disable">Disable</label>
												</div>
											<div class="form-check">
												<input class="form-check" type="radio" name="state" id="enable" value="enabled"<?php echo ($state === 'enabled' || $state === 'enable') ? 'checked' : ''; ?>>
												<label class="form-check-label" for="enabled">Enable</label>
											</div>
										</div>

										<div class="form-group">
											<label for="type-dropdown">Type</label>
											<select class="form-select form-control" id="type-dropdown" name="type">
												<?php
												$available_types = array('any', 'booked', 'pending', 'cancelled', 'approved','waiting','submitted');
												foreach ($available_types as $avail_type) {
													$selected = ($avail_type === $type) ? 'selected' : '';
													echo '<option value="' . $avail_type . '" ' . $selected . '>' . ucfirst($avail_type) . '</option>';
												}
												?>
											</select>

										</div>

									</div>
									<div class="border p-4 m-1">
										<h5>Email</h5>
										
										<div class="form-group">
											<label for="email-to">To</label>
											<input type="text" id="email-to" name="email_to" class="form-control" value="<?php echo isset($email_to) ? $email_to : ''; ?>" required>
										</div>

										<div class="form-group">
											<label for="email-from">From</label>
											<input type="text" id="email-from" name="email_from" class="form-control" value="<?php echo isset($email_from) ? $email_from : ''; ?>" required>
										</div>
										<div class="form-group">
											<label for="email-from">Reply To</label>
											<input type="text" id="email-replyto" name="email_replyto" class="form-control" value="<?php echo isset($email_replyto) ? $email_replyto : ''; ?>" >
										</div>
										<div class="form-group">
											<label for="email-from">Bcc</label>
											<input type="text" id="email-bcc" name="email_bcc" class="form-control" value="<?php echo isset($email_bcc) ? $email_bcc : ''; ?>" >
										</div>
										<div class="form-group">
											<label for="email-from">Cc</label>
											<input type="text" id="email-cc" name="email_cc" class="form-control" value="<?php echo isset($email_cc) ? $email_cc : ''; ?>" >
										</div>
										<div class="form-group">
											<label for="email-subject">Subject</label>
											<input type="text" id="email-subject" name="email_subject" class="form-control" value="<?php echo isset($email_subject) ? $email_subject : ''; ?>" required>
										</div>
										

										<div class="form-group">
											<label for="mail-body">Mail Body</label>
											<?php
											wp_editor(isset($mail_body) ? $mail_body : '', 'mail_body' . $index, array(
												'textarea_name' =>  'mail_body' . $index,
											));
											?>
										</div>
										
										<div class="form-check">
											
											<?php 
												$checked = '';
												if($use_html && !empty($use_html)){
													$checked = 'checked';
												}
											?>
											<input class="form-check-input" type="checkbox" id="use_html" name="use_html" value="1" <?php echo $checked;?>>
											<label class="form-check-label" for="flexCheckDefault">
												Use HTML content type
											</label>
										</div>
									
									</div>
									<p id="suc_loc" ></p>
									<input type="submit" id="submit_notification" name="submit_notification" class="btn btn-primary">
									<button type="button" class="btn btn-secondary" id="closemodal" data-dismiss="modal">Close</button>
								</form>
								<p id="suc_loc"></p>
							</div>
					</div>
				</div> 
			</div>
			<?php
			
		}
		/**
		 * save form data
		 * */	
		function sab_save_form_data() {
			
			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

			$form_data = isset( $_POST['form_data'] ) ? $_POST['form_data'] : array();
			
			update_post_meta( $post_id, '_formschema', $form_data );

			wp_send_json_success( 'Form data saved successfully.' );
			exit;

		}
		/**
		 * populate columns of post type
		 */
		function populate_custom_column($column, $post_id) {
			if ($column === 'shortcode') {				
				echo "[booking_form form_id='".$post_id."']";
			}
			if ($column === 'form') {	
				$form_id = get_post_meta($post_id,'sab_form_id',true);	
				$form_title = get_the_title($form_id);	
				
				if (isset($form_title)) {	
					echo __($form_title,'smart-appointment-booking');
				}else{
					echo '-';
				}
			}
			if ($column === 'booking_status') {		
				$booking_status = get_post_meta($post_id,'entry_status',true);
			
				if (isset($booking_status) && !empty($booking_status)) {	
					echo ucfirst(__($booking_status,'smart-appointment-booking'));
				}else{
					echo '-';
				}
				
			}
			if ($column === 'booking_date') {
				$booking_date = get_post_meta($post_id,'booking_date',true);
				if (isset($booking_date) && !empty($booking_date)) {
				
				$array_of_date = explode('_',$booking_date);
			
				$bookedmonth = $array_of_date[2];
				$bookedday =$array_of_date[3];
				$bookedyear =$array_of_date[4];
				$booked_date = $bookedday."-".$bookedmonth."-".$bookedyear;
				$booked_date = date('d F, Y', strtotime($booked_date));	
					if (isset($booking_date) && !empty($booking_date)) {	
						echo __($booked_date,'smart-appointment-booking');
					}
				}else{
					echo '-';
				}	
			}
			if ($column === 'timeslot') {
				$timeslot = get_post_meta( $post_id, 'timeslot', true );						
			
				if (isset($timeslot) && !empty($timeslot)) {	
					echo __($timeslot,'smart-appointment-booking');
				}else{
					echo '-';
				}
			}
			

		}
		/**
		 * html to preview timeslots or generate new timeslots
		 */
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
			
					$available_timeslots_list = $this->admin_generate_timeslots($start_time, $end_time, $duration_minutes, $gap_minutes, $break_times, $post_id);
				
					foreach ($available_timeslots_list as $index => $timeslot) {
						$start_time = isset($timeslot['start_time_slot']) ? $timeslot['start_time_slot'] : '';
						$end_time = isset($timeslot['end_time_slot']) ? $timeslot['end_time_slot'] : '';
						$output .= '<div class="form-row timeslot-row generatetimeslot">';
						$output .= '<div class="form-group col-md-3">';
						$output .= '<label>Start Time:</label>';
						$output .= '<input type="time" class="form-control" name="generatetimeslot[' . $index . '][start_time]" value="' . esc_attr($start_time) . '">';
						$output .= '</div>';
						$output .= '<div class="form-group col-md-3">';
						$output .= '<label>End Time:</label>';
						$output .= '<input type="time" class="form-control" name="generatetimeslot[' . $index . '][end_time]" value="' . esc_attr($end_time) . '">';
						$output .= '</div>';
						$output .= '<div class="form-group col-2 remove-generatetimeslot">';
						$output .= '<svg class="remove-generatetimeslot" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">';
						$output .= '<path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>';
						$output .= '</svg>';
						$output .= '</div>';
						$output .= '</div>';
					}
			
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
		/** 
		 * generate timeslots
		 */
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
				
				$end_timeslot = $current_timestamp + ($duration * 60);
	
				// Check if the timeslot extends beyond the end time
				if ($end_timeslot > $end_timestamp) {
					break;
				}
	
				// Check if the timeslot extends into any break time
				foreach ($break_timestamps as $break_timestamp) {
					if ($end_timeslot > $break_timestamp[0] && $current_timestamp < $break_timestamp[0]) {
						$end_timeslot = $break_timestamp[0]; 
						break;
					}
				}
				$start_time_slot = date('H:i', $current_timestamp);
				$end_time_slot = date('H:i', $end_timeslot);
				$available_timeslots[] = array(
					'start_time_slot' => $start_time_slot,
					'end_time_slot' => $end_time_slot,
				);
	
				$current_timestamp = $end_timeslot + ($gap * 60);
			}
			return $available_timeslots;
		}
		/**
		 * User Mapping fields
		 */
		function zfb_save_user_mapping() {
			if (!is_admin()) {
				wp_send_json_error('Invalid request.');
			}
		
			$user_mapping = isset($_POST['zfbuser_mapping']) ? stripslashes($_POST['zfbuser_mapping']) : '';
		
			parse_str($user_mapping, $user_mapping_array);
		
			$post_id = isset($user_mapping_array['post_id']) ? sanitize_text_field($user_mapping_array['post_id']) : '';
			if (!empty($post_id)) {
				update_post_meta($post_id, 'user_mapping', $user_mapping_array);

				$response['message'] = __('User mapping saved successfully.', 'smart-appointment-booking');
			} else {
				$response['message'] = __('Post ID is missing.', 'smart-appointment-booking');
			
			}
			wp_send_json($response);
			exit;
		}
		/** 
		 * Confirmation settings 
		 * */
		function zfb_save_confirmation() {

			if (isset($_POST['confirmation_data'])) {

				parse_str($_POST['confirmation_data'], $formdata);
				
				$post_id = $formdata['post_id'];
				if (isset($formdata['confirmation'])) {
					$redirect_url = sanitize_text_field($formdata['confirmation']);
					update_post_meta($post_id, 'confirmation', $redirect_url);
				}
				if (isset($formdata['redirect_text'])) {
					$wp_editor_value = wp_kses_post($formdata['redirect_text']);
					update_post_meta($post_id, 'redirect_text', $wp_editor_value);
				}
				if (isset($formdata['redirect_page'])) {
					$redirect_page = sanitize_text_field($formdata['redirect_page']);
					update_post_meta($post_id, 'redirect_page', $redirect_page);
				}
				if (isset($formdata['redirect_url'])) {
					$redirect_url = sanitize_text_field($formdata['redirect_url']);
					update_post_meta($post_id, 'redirect_url', $redirect_url);
				}
				$response['message'] = __('Saved Successfully', 'smart-appointment-booking');

			}else{
				$response['message'] = __('Something went wrong', 'smart-appointment-booking');
			}

			wp_send_json($response);
			exit;
		}	
		/**
		 * Disable Title FOR META BOX 
		 */
		function disable_title_editing_for_custom_post_type() {
			global $post_type;
		
			if ($post_type === 'manage_entries') {
				?>
				<script>
					jQuery(document).ready(function($) {
						$('#titlediv').remove(); 
					});
				</script>
				<?php
			}
		}
		/**
		 * ADD FILTER DROPDOWN for status
		 */
		function add_custom_booking_status_filter($post_type) {
			$args = array(
				'post_type' => 'manage_entries',
				'posts_per_page' => 1, // Fetch only one post to check if any exists.
			);
		
			$has_entries = new WP_Query($args);
		
			if ($has_entries->have_posts()) {
				$status = isset($_GET['booking_status']) ? $_GET['booking_status'] : '';
		
				$options = array(
					'any' => 'Status',
					'booked' => 'Booked',
					'approved' => 'Approved',
					'cancelled' => 'Cancelled',
					'pending' => 'Pending',
					'waiting' => 'Waiting',
					'submitted' => 'Submitted',
				);
		
				$args = array(
					'post_type' => 'sab_form_builder',
					'posts_per_page' => -1,
				);
		
				echo '<select name="booking_status" class="form-control">';
				foreach ($options as $value => $label) {
					$selected = selected($status, $value, false);
					echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
				}
				echo '</select>';
		
				$selected_form_id = isset($_GET['form_filter']) ? $_GET['form_filter'] : '';
		
				$forms_query = new WP_Query($args);
		
				echo '<select name="form_filter">';
				echo '<option value="">All Forms</option>';
				while ($forms_query->have_posts()) {
					$forms_query->the_post();
					$selected = selected($selected_form_id, get_the_ID(), false);
					echo '<option value="' . esc_attr(get_the_ID()) . '" ' . $selected . '>' . esc_html(get_the_title()) . '</option>';
				}
				echo '</select>';
			}
		}
		/**
		 * Filter as per selected Status
		 */
		function filter_custom_booking_status($query) {
			global $pagenow, $typenow;
			if (!is_admin() || !in_array($query->get('post_type'), array('manage_entries'))) {
				return;
			}

			if ('edit.php' === $pagenow && 'manage_entries' === $typenow) {
				$booking_status = isset($_GET['booking_status']) ? sanitize_text_field($_GET['booking_status']) : '';
				$form_filter = isset($_GET['form_filter']) ? intval($_GET['form_filter']) : 0;

				if (!empty($booking_status) || !empty($form_filter)) {
					$meta_query = array('relation' => 'and');

					if (!empty($booking_status) && in_array($booking_status, array('booked', 'approved', 'cancelled', 'pending', 'waiting', 'submitted'))) {
						$meta_query[] = array(
							'key' => 'entry_status',
							'value' => $booking_status,
							'compare' => '='
						);
					}

					if (!empty($form_filter)) {
						$meta_query[] = array(
							'key' => 'sab_form_id',
							'value' => $form_filter,
							'compare' => '='
						);
					}

					$query->set('meta_query', $meta_query);
				}
			}
		}
		/**
		 * add configuration email link
		 */
		function modify_submitdiv_content() {
			global $post;
			$post_id = $post->ID;
			$post_type = get_post_type( $post_id );
			if($post_type === 'sab_form_builder'){
				$form_id = get_post_meta($post_id,'sab_form_id',true);
				$page_slug = 'notification-settings';
				$post_type = 'sab_form_builder';
				
				$admin_url = admin_url('admin.php');
				$view_entry_url = add_query_arg(
					array(
						'page' => $page_slug,
						'post_type' => $post_type,
						'post_id' => $post_id
					),
					$admin_url
				);

				echo '<div class="misc-pub-section misc-pub-post-status" id="misc-notification"> <a href="' . esc_url($view_entry_url) . '" style="color:black;" target="_blank"><b>Configure Email Notifications</a> </b></div>';
				?>
			
				<?php
			}
          
			
		}
		/**
		 * Get pagination entry list: get_paginated_items_for_waiting_list
		 */
		function get_paginated_items_for_waiting_list(){
			// Define the current page number
			$current_page = isset($_POST['page']) ? absint($_POST['page']) : 1;
			$timeslot = isset($_POST['timeslot']) ? ($_POST['timeslot']) : '';
			$booking_date = isset($_POST['booking_date']) ? ($_POST['booking_date']) : '';
			$args = array(
				'post_type' => 'manage_entries',
				'posts_per_page' => 5, // Show 5 entries per page
				'paged' => $current_page, // Use the current page number for pagination
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
			// echo '<pre>';
			// echo $query->request;
			// echo '</pre>';
			// echo "<pre>";print_r($query);
			// Output the paginated items as a response
			ob_start();
			if ($query->have_posts()) {
				echo '<div class="border-top border-dark mb-2"></div>';
				echo '<p>Waiting List</p>';
				// echo '<div id="waitingtable">';
				echo '<table class="table table-bordered waitingtable " style="width:60%">';
				echo '<tr>';
				echo '<th style="width:10%">Post ID</th>';
				echo '<th style="width:50%">Post Title</th>';
				echo '<th style="width:20%">Status</th>';
				echo '<th style="width:20%"><svg><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
				<path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
				</svg></th>';
				echo '</tr>';
				$i = ($current_page - 1) * 5 + 1;
				while ($query->have_posts()) {
					$query->the_post();
					$post_id = get_the_ID();
					$post_title = get_the_title();
					$booking_status = get_post_meta($post_id, 'entry_status', true);
		
					if ($booking_status === 'waiting') {
						echo '<tr>';
						echo '<td>'.$i.'-'. $post_id . '</td>';
						echo '<td>' . $post_title . '</td>';
						echo '<td>' . $booking_status . '</td>';
						echo '<td><a href="' . get_edit_post_link($post_id) . '"><svg><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
							<path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
							</svg></a></td>';
						echo '</tr>';
					}
					$i++;
				}
		
				echo '</table>';
				echo '</div>';
				wp_reset_postdata();
		
				// Calculate the total number of pages
				$total_pages = $query->max_num_pages;
				echo $query->found_posts.' Items';
				if ($total_pages > 1) {
					echo '<div id="pagination-links">';
					echo '<select id="sabpage-number" data-timeslot="' . $timeslot . '" data-booking_date="' . $booking_date . '">';
						for ($page = 1; $page <= $total_pages; $page++) {
							echo '<option value="' . $page . '"';
							if ($page == $current_page) {
								echo ' selected';
							}
							echo '>' . $page . '</option>';
						}
					echo '</select>';
					echo __('of Page ','textdomain');
					echo $total_pages;
					echo '</div>';
				}
			}
		
			// Send the response back to the Ajax request
			echo ob_get_clean();
			wp_die(); // End the script
		}
	}
	add_action( 'plugins_loaded', function() {
		$SAB_Admin_Action = new SAB_Admin_Action();
	} );

}
