<?php
/**
 * SAAB_Admin_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @package Smart Appointment & Booking
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'SAAB_Admin_Action' ) ) {

	/**
	 *  The SAAB_Admin_Action Class
	 */
	class SAAB_Admin_Action {

		function __construct()  {
			add_action( 'init',array( $this, 'saab_action_init' ));
			add_action( 'admin_enqueue_scripts',array( $this, 'saab_enqueue_styles' ));
			add_action( 'admin_enqueue_scripts',array( $this, 'saab_enqueue_scripts' ));
			add_action('admin_menu',array( $this, 'saab_add_post_type_menu' ));
				
			add_action( 'wp_ajax_saab_save_form_data', array( $this, 'saab_save_form_data' ));
			add_action('manage_saab_form_builder_posts_custom_column', array( $this, 'saab_populate_custom_column' ), 10, 2);
			add_action('manage_manage_entries_posts_custom_column', array( $this, 'saab_populate_custom_column' ), 10, 2);

			add_action('wp_ajax_saab_preiveiw_timeslot', array( $this, 'saab_preiveiw_timeslot' ) );
			add_action('wp_ajax_nopriv_saab_preiveiw_timeslot', array( $this, 'saab_preiveiw_timeslot' ) );

			add_action( 'wp_ajax_saab_save_new_notification', array( $this, 'saab_save_new_notification' ));
			add_action('wp_ajax_nopriv_saab_save_new_notification', array( $this, 'saab_save_new_notification' ) );

			add_action('init', array( $this, 'saab_add_notification_capability' ) );
			add_action('admin_enqueue_scripts',  array( $this, 'saab_enqueue_admin_scripts' ), 10, 2);
			
			add_action('wp_ajax_delete_notification_indexes', array( $this, 'saab_delete_notification_indexes' ) );

			add_action( 'wp_ajax_saab_update_notification_state', array( $this, 'saab_update_notification_state' ));
			add_action('wp_ajax_nopriv_saab_update_notification_state', array( $this, 'saab_update_notification_state' ) );

			add_action('wp_ajax_saab_save_user_mapping', array( $this, 'saab_save_user_mapping' ) );
			add_action('wp_ajax_nopriv_saab_save_user_mapping', array( $this, 'saab_save_user_mapping' ) );

			add_action( 'wp_ajax_saab_save_confirmation', array( $this, 'saab_save_confirmation' ));
			add_action('wp_ajax_nopriv_saab_save_confirmation', array( $this, 'saab_save_confirmation' ) );

			add_action('edit_form_after_title', array( $this, 'saab_disaable_title_editing_for__post_type' ) );

			add_action('wp_ajax_saab_update_form_entry_data', array( $this, 'saab_update_form_entry_data' ) );
			add_action('wp_ajax_nopriv_saab_update_form_entry_data', array( $this, 'saab_update_form_entry_data' ) );
			
			add_action( 'restrict_manage_posts', array( $this, 'saab_add_custom_booking_status_filter' ) );
			add_action( 'pre_get_posts', array( $this, 'saab_filter_custom_booking_status' ) );
			
			add_action('post_submitbox_misc_actions', array( $this, 'saab_modify_submitdiv_content' ) );			
			add_action('wp_ajax_saab_get_paginated_items_for_waiting_list', array( $this, 'saab_get_paginated_items_for_waiting_list' ) );
			add_action('wp_ajax_nopriv_saab_get_paginated_items_for_waiting_list', array( $this, 'saab_get_paginated_items_for_waiting_list' ) );

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
		* WP Enqueue Styles
		*/	
		function saab_action_init(){
			wp_register_style( SAAB_PREFIX . '_admin_min_css', SAAB_URL . 'assets/css/admin.min.css', array(), SAAB_VERSION );
			wp_register_style( SAAB_PREFIX . '_admin_css', SAAB_URL . 'assets/css/admin.css', array(), SAAB_VERSION );
		}

		function saab_enqueue_styles() {
			global $post;
			if ($post) {
				$post_type = $post->post_type;
			} else {
				$post_type = '';
			}

			if (
				is_singular('saab_form_builder') || 
				is_singular('zeal_formbuilder') || 
				(isset($post_type) && ($post_type == 'saab_form_builder' || $post_type == 'manage_entries')) 
			) {
				wp_enqueue_style( '_admin_css',SAAB_URL.'assets/css/admin.css', array(), 1.1, 'all' );	
				wp_enqueue_style( 'saab_font-awesomev1',SAAB_URL.'assets/css/font-awesome.css', array(), 1.1, 'all' );
				//formio
				wp_enqueue_style( 'saab_formio_full_min',SAAB_URL.'assets/css/formio/formio.full.min.css', array(), 1.1, 'all' );
				//boostrap
				wp_enqueue_style( 'saab_boostrap_min',SAAB_URL.'assets/css/boostrap/boostrap.min.css', array(), 1.1, 'all' );	
			 }
			
			 if ( is_page( ['notification-settings'] ) ) {
				//boostrap
				wp_enqueue_style( 'datatable_admin_css',SAAB_URL.'assets/css/boostrap/jquery.dataTables.min.css', array(), 1.1, 'all' );
			 }	
			 if (  isset( $_GET['nonce'] ) ||  check_ajax_referer( 'other_setting', 'nonce', false ) ) {		 
				 wp_enqueue_style( 'saab_boostrap_min',SAAB_URL.'assets/css/boostrap/boostrap.min.css', array(), 1.1, 'all' );
			 }	
		}
	
		/**
		* WP Enqueue Scripts
		*/
		function saab_enqueue_scripts() {
		
			global $post;
			if ($post) {
				$post_type = $post->post_type;
			} else {
				$post_type = '';
			}		
			if ( is_page( ['notification-settings'] ) ) {
				//boostrap folder
				wp_enqueue_script( 'datatble_admin',SAAB_URL.'assets/js/boostrap/jquery.dataTables.min.js',array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'datatbleboostrap',SAAB_URL.'assets/js/boostrap/dataTables.boostrap5.min.js',array( 'jquery' ), 1.1, false );
			 }
			if (				
				is_singular('saab_form_builder') || 
				is_singular('zeal_formbuilder') || 
				$post_type == 'saab_form_builder' || 
				$post_type == 'manage_entries'
			) {
				wp_enqueue_script( 'saab_popper.minjs', SAAB_URL.'assets/js/boostrap/popper.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'saab_boostrap.min', SAAB_URL.'assets/js/boostrap/boostrap.min.js', array( 'jquery' ), 1.1, false );				
				wp_enqueue_script( 'saab_boostrap_bundlemin', SAAB_URL.'assets/js/boostrap/boostrap.bundle.min.js', array( 'jquery' ), 1.1, false );
   
				//formio folder
			 	wp_enqueue_script( 'saab_formio_full_min', SAAB_URL.'assets/js/formio/formio.full.min.js', array( 'jquery' ), 1.1, false );
				$ajax_nonce = wp_create_nonce('saab_ajax_nonce');
				//booking folder
				wp_enqueue_script( 'booking-form', SAAB_URL.'assets/js/booking/booking-form.js', array( 'jquery-core' ), 1.1, false );
				wp_localize_script('booking-form', 'ajax_object', array(
					'ajax_url' => esc_url(admin_url('admin-ajax.php')),
					'nonce'   => $ajax_nonce,
				));
	
				wp_enqueue_script( 'admin', SAAB_URL.'assets/js/admin.js', array( 'jquery' ), 1.1, false );
			 }
			 if (  isset( $_GET['nonce'] ) ||  wp_verify_nonce( 'other_setting', 'nonce', false ) ) {
				wp_enqueue_script( 'saab_popper.minjs', SAAB_URL.'assets/js/boostrap/popper.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'saab_boostrap.min', SAAB_URL.'assets/js/boostrap/boostrap.min.js', array( 'jquery' ), 1.1, false );				
				wp_enqueue_script( 'saab_boostrap_bundlemin', SAAB_URL.'assets/js/boostrap/boostrap.bundle.min.js', array( 'jquery' ), 1.1, false );
   
				//formio folder
			 	wp_enqueue_script( 'saab_formio_full_min', SAAB_URL.'assets/js/formio/formio.full.min.js', array( 'jquery' ), 1.1, false );
				$ajax_nonce = wp_create_nonce('saab_ajax_nonce');
				//booking folder
				wp_enqueue_script( 'booking-form', SAAB_URL.'assets/js/booking/booking-form.js',array( 'jquery-core' ), 1.1, false );
				wp_localize_script('booking-form', 'ajax_object', array(
					'ajax_url' => esc_url(admin_url('admin-ajax.php')),
					'nonce'   => $ajax_nonce,
				));
	
				wp_enqueue_script( 'admin', SAAB_URL.'assets/js/admin.js', array( 'jquery' ), 1.1, false );  
			}
			 
			 //boostrap folder
			 wp_register_script( SAAB_PREFIX . '_admin_js', SAAB_URL . 'assets/js/admin.min.js', array( 'jquery-core' ), SAAB_VERSION );

		}

		function saab_enqueue_admin_scripts() {
			wp_enqueue_script('jquery-ui-tabs');
		}
		// Add capability to user role
		function saab_add_notification_capability() {
			$role = get_role('administrator'); 
			$role->add_cap('edit_notifications'); 
		}
		/**
		 * save new notification
		*/
		function saab_save_new_notification() {
			
			if ( !isset( $_POST['security'] ) &&! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['security'] ) ) , 'saab_ajax_nonce' )) { 
				$response = array(
					'success' => false,
					'message' => 'Invalid Nonce request.',
				);
				wp_send_json_success( $response);
				exit;  
			}
			$response = array(
				'success' => false,
				'message' => 'Invalid request.',
			);
			$get_notification_array = array();
			if (isset($_POST['notification_data'])) {
			
				parse_str(wp_unslash(sanitize_text_field($_POST['notification_data']), $form_data));
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
					$get_notification_array = get_post_meta($post_id, 'saab_notification_data', true);
		
					// Verify if the meta key exists and contains data
					if (!empty($get_notification_array)) {
						if (isset($get_notification_array[$index])) {
							// Update existing array element
							$get_notification_array[$index] = $notification_data;
						} else {
							if(is_string($get_notification_array)){
								update_post_meta($post_id, 'saab_notification_data','');
								$get_notification_array[] = $notification_data;
								update_post_meta($post_id, 'saab_notification_data', $get_notification_array);
							}else{
								$get_notification_array[] = $notification_data;
							}
							
							
						}		
						update_post_meta($post_id, 'saab_notification_data', $get_notification_array);
					} else {
						$get_notification_array = array($notification_data);
						update_post_meta($post_id, 'saab_notification_data', $get_notification_array);
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
		 * This function registers two custom post types: 'saab_form_builder' for generating appointment and booking forms,
		 * and 'manage_entries' for managing entries submitted through the forms.
		 *
		 * It also adds menu items to the admin dashboard for easy access to the plugin's functionality.
		 */
		function saab_add_post_type_menu() {
			
			$labels_form = array(
				'name' => esc_html__('Generate Appointment and Booking Forms','smart-appointment-booking'),
				'singular_name' => esc_html__('Generate Appointment and Booking Form','smart-appointment-booking'),
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
			register_post_type('saab_form_builder', $args_form);

			$labels = array(				
				'name' => esc_html__('Manage Entries','smart-appointment-booking'),
				'singular_name' => esc_html__('Manage Entry','smart-appointment-booking'),
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
				'edit.php?post_type=saab_form_builder',
				'',
				'dashicons-twitter',
				20
			);
			
			add_submenu_page(
				'edit.php?post_type=saab_form_builder', 
				'Add New Form', 
				'Add New Form', 
				'manage_options', 
				admin_url('post-new.php?post_type=saab_form_builder')
			);
		
			add_submenu_page(
				'edit.php?post_type=saab_form_builder',
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
				array($this, 'saab_render_notification_settings_page')
			);
			
		}
		/**
		 * 
		 * view booking entry
		 */
	
		/**
		 * Update booking form entries in backend
		 */
		function saab_update_form_entry_data(){
			
			if ( !isset( $_POST['security'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['security'] ) ) , 'saab_ajax_nonce' ) ) {   
				return;
			}

			if (isset($_POST['entry_id']) && isset($_POST['updated_data']) ) {

				$entry_id = isset($_POST['entry_id']) ? absint($_POST['entry_id']) : '';
				$get_submitted_data = get_post_meta($entry_id, 'saab_submission_data', true);
				$updated_data = sanitize_text_field($_POST['updated_data']);	
				foreach ($updated_data as $key => $value) {
					$sanitized_value = sanitize_text_field($value);
					if (isset($get_submitted_data['data'][$key])) {
						$get_submitted_data['data'][$key] = $sanitized_value;
					}
				}
				
				update_post_meta($entry_id, 'saab_submission_data', $get_submitted_data);
			}
            wp_die();
        }
		/**
		 * Admin: Get key-value pairs of shortcodes to send in manual notification or post update.
		 *
		 * @param int $post_id The post ID.
		 * @return array An array of shortcode key-value pairs.
		 */
		function saab_admin_get_shortcodes_keylabel($post_id){
			$shortcode_list = array();
			$form_data = get_post_meta( $post_id, 'saab_formschema', true ); 
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
		function saab_render_notification_settings_page() {
			// Add your page content here
			echo "<div class='notification-page-main m-4 p-1 ' >";
		
			if (isset($_GET['post_type']) && isset($_GET['post_id']) && isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )) {
			
				$post_type = sanitize_text_field($_GET['post_type']);
				$post_id = absint( $_GET['post_id']);				
			
				?>
				<ul class="nav nav-tabs" id="myTabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="tab_fieldmapping" data-bs-toggle="tab" href="#content_fieldmapping" role="tab" aria-controls="content_fieldmapping" aria-selected="true"><?php echo esc_html__('Field Mapping','smart-appointment-booking'); ?></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tab_notification" data-bs-toggle="tab" href="#content_notification" role="tab" aria-controls="content_notification" aria-selected="false"><?php echo esc_html__('Notification','smart-appointment-booking'); ?></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="tab_confirmation" data-bs-toggle="tab" href="#content_confirmation" role="tab" aria-controls="content_confirmation" aria-selected="false"><?php echo esc_html__('Confirmation','smart-appointment-booking'); ?></a>
					</li>
				</ul>

				<div class="tab-content p-4 border" id="myTabContent">
					<div class="tab-pane fade show active " id="content_fieldmapping" role="tabpanel" aria-labelledby="tab_fieldmapping">
						<?php
						$form_data = get_post_meta($post_id, 'saab_formschema', true ); 
						$form_data=json_decode($form_data);
						$shortcodes = $this->saab_admin_get_shortcodes_keylabel($post_id);
						?>
						<div class="row">
							<div class="col col-md-3">	
								<?php
								$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
								$user_mapping = get_post_meta($post_id, 'saab_user_mapping', true);
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
											<label class="h6" for="lfirstname"  id="lfirstname">First Name:</label>
											<select class="form-control" id="first-name" name="first_name">
												<option value="any" disabled>Any</option>
												<?php 													
													$fieldFirstName = $this->saab_admin_get_shortcodes_keylabel($post_id);
													foreach ($fieldFirstName as $option) {
														$fieldKey = $option['fieldkey'];
														$fieldLabel = $option['fieldlabel'];
														$selected = ($fieldKey == $first_name) ? 'selected' : '';
														echo '<option value="' . esc_attr($fieldKey) . '" ' . esc_html($selected) . '>' . esc_html($fieldLabel) . '</option>';
													}
												?>
											</select>
										</div>
										<div class="form-group col-md-6">
											<label class="h6" for="llastname" id="llastname">Last Name:</label>
											<select class="form-control" id="last-name" name="last_name">
												<option value="any" disabled>Any</option>
												<?php 													
													$fieldLastName = $this->saab_admin_get_shortcodes_keylabel($post_id);
													foreach ($fieldLastName as $option) {
														$fieldKey = $option['fieldkey'];
														$fieldLabel = $option['fieldlabel'];
														$selected = ($fieldKey == $last_name) ? 'selected' : '';
														echo '<option value="' . esc_attr($fieldKey) . '" ' . esc_html($selected) . '>' . esc_html($fieldLabel) . '</option>';
													}
												?>
											</select>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col">
											<label class="h6" >Email:</label>
											<select class="form-control" id="email" name="email">
											<option value="any" disabled>Any</option>
											<?php 													
												$fieldEmail = $this->saab_admin_get_shortcodes_keylabel($post_id);
												foreach ($fieldEmail as $option) {
													$fieldKey = $option['fieldkey'];
													$fieldLabel = $option['fieldlabel'];
													$selected = ($fieldKey == $email) ? 'selected' : '';
													echo '<option value="' . esc_attr($fieldKey) . '" ' . esc_html($selected) . '>' . esc_html($fieldLabel) . '</option>';
												}
											?>
											</select>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col">
											<label class="h6" >Service:</label>
											<select class="form-control" id="service" name="service">
											<option value="any" disabled>Any</option>
											<?php 													
												$fieldService = $this->saab_admin_get_shortcodes_keylabel($post_id);
												foreach ($fieldService as $option) {
													$fieldKey = $option['fieldkey'];
													$fieldLabel = $option['fieldlabel'];
													$selected = ($fieldKey == $service) ? 'selected' : '';
													echo '<option value="' . esc_attr($fieldKey) . '" ' . esc_html($selected) . '>' . esc_html($fieldLabel) . '</option>';
												}
											?>
											</select>
										</div>
									</div>

									<div class="form-row">
										<div class="form-group col">
											<label class="h6" >Cancel Booking Page:</label>
											<select name="cancel_bookingpage"  class="form-control" id="selected_page">
												<option value="">Select a page</option>
												<?php
												$pages = get_pages();
												foreach ($pages as $page) {
													$selected = $cancel_bookingpage == $page->ID ? 'selected' : '';
													echo '<option value="' .  esc_attr($page->ID). '" ' . esc_html($selected). '>' . esc_html($page->post_title) . '</option>';
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
								$form_data = $this->saab_admin_get_shortcodes($post_id);
								echo '<div class=""><label style="font-weight: bold;">' . esc_html__('Form shortcodes', 'smart-appointment-booking') . '</label></div>';
								foreach ($form_data['form'] as $objform) {
									echo '<span class="copy-text" style="margin-right: 5px; font-family: Arial; font-size: 14px;">[' .  esc_attr($objform) . ']</span>';
								}
								$enable_booking = get_post_meta($post_id, 'saab_enable_booking', true);
								if( $enable_booking ){
									echo '<div class=""><label style="font-weight: bold;">' . esc_html__('Booking shortcodes', 'smart-appointment-booking') . '</label></div>';
									foreach ($form_data['booking'] as $objbooking) {
										echo '<span class="copy-text" style="margin-right: 5px;margin-bottom: 5px; font-family: Arial; font-size: 14px;">[' .  esc_attr($objbooking) . ']</span>';
									}
								}
								echo '<div class=""><label style="font-weight: bold;">' . esc_html__('Post shortcodes', 'smart-appointment-booking') . '</label></div>';
								foreach ($form_data['post'] as $objpost) {
									echo '<span class="copy-text" style="margin-right: 5px; font-family: Arial; font-size: 14px;">[' . esc_attr($objpost) . ']</span>';
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
								$index='add';
								?>
								<div class="main-container-notification border border-light" >
									
									<div class="form-group">
										<!-- Button to trigger the modal -->
										<button type="button" class="btn btn-secondary" id="add_notify_btn" data-toggle="modal" data-target="#notifyModal<?php echo esc_attr( $index ); ?>">Add New notification </button>
									</div>
									<!-- Modal -->
									<?php  $this->saab_generateModal($index,$post_id); ?>
									
									<?php
									$notification_metadata = get_post_meta($post_id, 'saab_notification_data', true);
									
									if (!empty($notification_metadata) && is_array($notification_metadata)) {
										$post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : '';
										?>
										
										<div id="tab5" class="tab-content">
											<input type="hidden" name="post_id" id="post_id" value="<?php echo esc_attr($post_id); ?>" >
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
															<td><input type="checkbox" id="saab-check-all<?php echo esc_attr($index); ?>" class="child-checkall" value="<?php echo esc_attr($index); ?>"></td>
															<td>
																<?php 
																echo esc_attr($ni)."."; 
																$ni++;
																echo " ". esc_html( $notification_name ); 
																?>
															</td>
															<td>
																<span>
																	<?php 
																		
																		$booking_status = isset($notification['type']) ? $notification['type'] : '';
																	
																		echo esc_html($booking_status);
																	?>
																</span>
															</td>
															<td>
															<button type="button" class="btn btn-outline-dark enable-btn" data-notification-id="<?php echo esc_attr( $notification_id ); ?>" data-notification-state="<?php echo esc_html( $state ); ?>">
															<?php echo ($state === 'enabled') ? 'Enabled' : 'Disabled'; ?> </button></td>
															<td> 
																<button type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#notifyModal<?php echo esc_attr($index); ?>">
																<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
																	<path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
																	<path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
																</svg>
																	Edit
																</button>
																<!-- Modal -->
																<?php  $this->saab_generateModal($index,$post_id); ?>
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
			
					<div class="tab-pane fade" id="content_confirmation" role="tabpanel" aria-labelledby="tab_confirmation">
						<?php
						$confirmation = get_post_meta($post_id, 'saab_confirmation', true);  
						$redirect_text = get_post_meta($post_id, 'saab_redirect_text', true);
						$redirect_to= get_post_meta($post_id, 'saab_redirect_to', true);
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
									<?php echo esc_html__('Texts','smart-appointment-booking'); ?>
								</label>
							</div>
							<div class="form-check form-check-inline">
								<input  type="radio" name="confirmation" id="radioPage" value="redirect_page" <?php if ($confirmation == 'redirect_page') echo 'checked="checked"'; ?>>
								<label class="form-check-label" for="radioPage">
								<?php echo esc_html__('Page','smart-appointment-booking'); ?>
								</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" name="confirmation" id="radioRedirect" value="redirect_to" <?php if ($confirmation == 'redirect_to') echo 'checked="checked"'; ?>>
								<label class="form-check-label" for="radioRedirect">
								<?php echo esc_html__('Redirect to','smart-appointment-booking'); ?>
								</label>
							</div>
							<!-- Class is used for on change event display div: redirectto_main redirect_page , redirectto_main redirect_text, redirectto_main redirect_to -->
							<div class="form-group redirectto_main redirect_text text_saab <?php echo esc_html( $hiddenredirect_text ); ?> ">
								<?php
									wp_editor($redirect_text, 'redirect_text', array(
										'textarea_name' => 'redirect_text',
									));
								?>
							</div>
							<div class="form-group redirectto_main redirect_page page_saab <?php echo esc_html( $hiddenredirect_page ); ?>  ">
								<label  class="h6"><?php echo esc_html__('Select a page:','smart-appointment-booking'); ?></label>
								<!-- <input type="text" id="redirectpage-search" placeholder="Search..."> -->
								<select name="redirect_page" id="redirectpage-dropdown">
									<option value=""><?php echo esc_html__('Select a page','smart-appointment-booking'); ?></option>
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
										echo '<option value="' . esc_attr($page->ID) . '" ' . esc_html($selected) . '>' . esc_attr($page->post_title) . '</option>';
									}
									?>
								</select>
							</div>
							<div class="form-group redirectto_main redirect_to redirect_saab <?php echo esc_html( $hiddenredirect_to ); ?> ">
								<label class="h6"><?php echo esc_html__('Enter Url: ', 'smart-appointment-booking'); ?></label>
								<input type="text" name="redirect_to" id="redirect-url" class="form-control" value="<?php echo esc_attr($redirect_to); ?>" pattern="https?://.+" style="width: 500px !important;" placeholder="Enter url with http or https">
								<small class="redirecturl-error" style="display:none;"><?php echo esc_html__('Please enter a valid URL starting with http:// or https://','smart-appointment-booking'); ?></small>
							</div> 
							<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id );?>">
							<input type="submit" value="Save" class="btn btn-primary" name="Save">
						</form>
						<p id="confirm_msg" class="h6 m-2"></p>
					</div>
				</div>
				<?php
				$back_link = get_edit_post_link($post_id);
				?>
				<a href="<?php echo esc_url($back_link); ?>">
					<button type="button" class="btn btn-secondary mt-2" id="deletenotify">
						<?php echo esc_html__('Back To Form Configuration','smart-appointment-booking'); ?>
					</button>
				</a>
				<?php
			} else {
				echo "Error: Post type and/or post ID not found.";
			}
			echo '</div>';
		}
		/**
		 * get shortcodes to display in page
		 */
		function saab_admin_get_shortcodes($post_id){
			$form_list = array();
			$shortcode_list = array();
			$form_data1 = get_post_meta($post_id, 'saab_formschema', true ); 
			if(isset($form_data1) && !empty($form_data1)){
				$form_data1=json_decode($form_data1);
				foreach ($form_data1 as $obj) {  
					 if($obj->key !== 'submit'){				
					$form_list[] = $obj->key;
					 }
				}
				 // Additional values to be added
				 $additional_values = ['To', 'FirstName', 'LastName','Service'];

				 // Merge arrays and remove duplicates
				 $form_list = array_unique(array_merge($form_list, $additional_values));
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
		function saab_update_notification_state() {
			$response = array(
				'success' => false,
				'message' => 'Invalid request.',
			);
			
			if (!isset($_POST['security']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['security'] ) ) , 'saab_ajax_nonce' )) {
				$response['message'] = esc_html__('Something went wrong', 'smart-appointment-booking');
				wp_send_json($response);
			}else{
				if (null !== ($_POST['post_id'] ?? null) && null !== absint($_POST['notification_id'] ?? null) && null !== sanitize_text_field($_POST['new_state'] ?? null)) {
					$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
					$notification_id = isset($_POST['notification_id']) ? absint($_POST['notification_id']) : 0;

					$index = ltrim($notification_id, "notify_");
					$new_state = isset($_POST['new_state']) ? sanitize_text_field($_POST['new_state']) : '';
					
					// Get the existing notification metadata
					$notification_data = get_post_meta($post_id, 'saab_notification_data', true);
					if ($notification_data) {
						if (isset($notification_data[$index])) {
							$notification_data[$index]['state'] = $new_state;
							update_post_meta($post_id, 'saab_notification_data', $notification_data);
			
							$response['success'] = true;
							$response['message'] = esc_html__('Notification saved successfully', 'smart-appointment-booking');
							$response['state'] = $new_state;
						} else {
							$response['message'] = esc_html__('Something went wrong', 'smart-appointment-booking');
						}
					} else {
						$response['message'] = esc_html__('Something went wrong', 'smart-appointment-booking');
					}
				}
			
				wp_send_json($response);
				
			}
			wp_die();
		}
		/**
		 * delete Notification entry from backend
		 */
		function saab_delete_notification_indexes() {
			
			if (!isset($_POST['security']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['security'] ) ) , 'saab_ajax_nonce' )) {
				wp_send_json_error('Invalid request.');
				wp_die();
			}
			if (isset($_POST['indexes'])) {
				$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
				$indexesToDelete = isset($_POST['indexes']) ? sanitize_text_field($_POST['indexes']) : '';	
				$notification_metadata = get_post_meta($post_id, 'saab_notification_data', true);
				foreach ($indexesToDelete as $index) {
					if (isset($notification_metadata[$index])) {
						unset($notification_metadata[$index]);
					}
				}

				update_post_meta($post_id, 'saab_notification_data', $notification_metadata);
				wp_send_json_success('Indexes deleted successfully.');
			} else {
				wp_send_json_error('Invalid request.');
			}
		}
		/**
		 * modal : add and edit new notification
		 */
		function saab_generateModal($index,$post_id) {
			
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

			$data = get_post_meta($post_id, 'saab_notification_data', true);
			
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
			<div class="modal fade notification-modal" id="notifyModal<?php echo esc_attr($index); ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo esc_attr($index); ?>" aria-hidden="true">
				<div class="modal-dialog modal-lg notification-mdialog modal-dialog-scrollable">
					<div class="modal-content notification-mcontent">
						<input type="hidden" value="<?php echo esc_attr($post_id); ?>" name="form_id">
						<!-- Modal header -->
						<div class="modal-header">
							<h4 class="modal-title" id="myModalLabel"><?php echo esc_html($title); ?></h4>
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						</div>
							
						<!-- Modal body -->
						<div class="modal-body notification-mdialog" style="max-height: 100%;overflow-y: auto;">
							<form class="notifyform" data-id ="<?php echo esc_attr($index); ?>" method="post">
								<div class="border p-4 m-1">
									<h5>General Notification Setting</h5>
									<input type="hidden" value="<?php echo esc_attr($index); ?>" name="editnotify" >
									<div class="form-group">
										<input type="hidden" name="form_id" value="<?php echo esc_attr($post_id); ?>">
									</div>
									<div class="form-group">
										<label >Notification Name</label>
										<input type="text" value="<?php echo esc_html($notificationName); ?>" id="notification-name" name="notification_name" class="form-control" placeholder="Enter Notification Title" required>
									</div>
									<div class="form-group">
										<label >State</label>
										<div class="form-check">
											<input class="form-check" type="radio" name="state" id="disable" value="disabled"<?php echo ($state === 'disabled' || $state === 'disable') ? 'checked' : ''; ?>>
											<label class="form-check-label" >Disable</label>
											</div>
										<div class="form-check">
											<input class="form-check" type="radio" name="state" id="enable" value="enabled"<?php echo ($state === 'enabled' || $state === 'enable') ? 'checked' : ''; ?>>
											<label class="form-check-label" >Enable</label>
										</div>
									</div>
									<div class="form-group">
										<label >Type</label>
										<select class="form-select form-control" id="type-dropdown" name="type">
											<?php
											$available_types = array('any', 'booked', 'pending', 'cancelled', 'approved','waiting','submitted');
											foreach ($available_types as $avail_type) {
												$selected = ($avail_type === $type) ? 'selected' : '';
												echo '<option value="' . esc_html($avail_type) . '" ' . esc_attr($selected) . '>' . ucfirst(esc_html($avail_type)) . '</option>';

											}
											?>
										</select>
									</div>
								</div>
								<div class="border p-4 m-1">
									<h5><?php echo esc_html__('Email','smart-appointment-booking'); ?></h5>
									<div class="form-group">
										<label ><?php echo esc_html__('To','smart-appointment-booking'); ?></label>
										<input type="text" id="email-to" name="email_to" class="form-control" value="<?php echo isset($email_to) ? esc_attr($email_to) : ''; ?>" required>
									</div>
									<div class="form-group">
										<label ><?php echo esc_html__('From','smart-appointment-booking'); ?></label>
										<input type="text" id="email-from" name="email_from" class="form-control" value="<?php echo isset($email_from) ? esc_attr($email_from) : ''; ?>" required>
									</div>
									<div class="form-group">
										<label ><?php echo esc_html__('Reply To','smart-appointment-booking'); ?></label>
										<input type="text" id="email-replyto" name="email_replyto" class="form-control" value="<?php echo isset($email_replyto) ? esc_attr($email_replyto) : ''; ?>" >
									</div>
									<div class="form-group">
										<label ><?php echo esc_html__('Bcc','smart-appointment-booking'); ?></label>
										<input type="text" id="email-bcc" name="email_bcc" class="form-control" value="<?php echo isset($email_bcc) ? esc_attr($email_bcc) : ''; ?>" >
									</div>
									<div class="form-group">
										<label ><?php echo esc_html__('Cc','smart-appointment-booking'); ?></label>
										<input type="text" id="email-cc" name="email_cc" class="form-control" value="<?php echo isset($email_cc) ? esc_attr($email_cc) : ''; ?>" >
									</div>
									<div class="form-group">
										<label><?php echo esc_html__('Subject','smart-appointment-booking'); ?></label>
										<input type="text" id="email-subject" name="email_subject" class="form-control" value="<?php echo isset($email_subject) ? esc_attr($email_subject) : ''; ?>" required>
									</div>
									<div class="form-group">
										<label><?php echo esc_html__('Mail Body','smart-appointment-booking'); ?></label>
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
										<input class="form-check-input" type="checkbox" id="use_html" name="use_html" value="1" <?php echo esc_html($checked);?>>
										<label class="form-check-label" >
										<?php echo esc_html__('Use HTML content type','smart-appointment-booking'); ?>
										</label>
									</div>
								</div>
								<p id="suc_loc" ></p>
								<input type="submit" id="submit_notification" name="submit_notification" class="btn btn-primary">
								<button type="button" class="btn btn-secondary" id="closemodal" data-dismiss="modal"><?php echo esc_html__('Close','smart-appointment-booking'); ?></button>
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
		function saab_save_form_data() {

			if (isset($_POST['security']) || wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'saab_ajax_nonce')) {
			
				$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

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
				
				update_post_meta($post_id, 'saab_formschema', $form_data );

				wp_send_json_success( 'Form data saved successfully.' );
				
			}else{
				wp_send_json_error('Nonce verification failed');
			}
			exit;

		}
		/**
		 * populate columns of post type
		 */
		function saab_populate_custom_column($column, $post_id) {
			if ($column === 'shortcode') {				
				echo "[saab_booking_form form_id='".esc_attr($post_id)."']";
			}
			if ($column === 'form') {	
				$form_id = get_post_meta($post_id,'saab_form_id',true);	
				$form_title = get_the_title($form_id);	
				
				if (isset($form_title)) {
					echo sprintf(
						esc_html__('%s', 'smart-appointment-booking'),
						esc_html($form_title)
					);
				
				}else{
					echo '-';
				}
			}
			if ($column === 'booking_status') {		
				$booking_status = get_post_meta($post_id,'saab_entry_status',true);
			
				if (isset($booking_status) && !empty($booking_status)) {
					echo sprintf(
						esc_html__('%s', 'smart-appointment-booking'),
						esc_html(ucfirst($booking_status))
					);	
				}else{
					echo '-';
				}
				
			}
			if ($column === 'booking_date') {
				$booking_date = get_post_meta($post_id,'saab_booking_date',true);
				if (isset($booking_date) && !empty($booking_date)) {
				
				$array_of_date = explode('_',$booking_date);
			
				$bookedmonth = $array_of_date[2];
				$bookedday =$array_of_date[3];
				$bookedyear =$array_of_date[4];
				$booked_date = $bookedday."-".$bookedmonth."-".$bookedyear;
				$booked_date = gmdate('d F, Y', strtotime($booked_date));	
					if (isset($booking_date) && !empty($booking_date)) {	
						echo sprintf(
							esc_html__('%s', 'smart-appointment-booking'),
							esc_html($booked_date)
						);
						
					}
				}else{
					echo '-';
				}	
			}
			if ($column === 'timeslot') {
				$timeslot = get_post_meta($post_id, 'saab_timeslot', true );						
			
				if (isset($timeslot) && !empty($timeslot)) {
					echo sprintf(
						esc_html__('%s', 'smart-appointment-booking'),
						esc_html($timeslot)
					);	
				}else{
					echo '-';
				}
			}
			

		}
		/**
		 * Generate HTML for previewing or creating time slots.
		 *
		 * This function is responsible for generating HTML that allows users to preview existing time slots
		 * or create new ones. It serves as a part of a larger system for managing time slots in a web application.
		 * 
		 */
		function saab_preiveiw_timeslot() {
			$error = 0;
			$output = '';		
		
			if (!isset($_POST['security']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['security'] ) ) , 'saab_ajax_nonce' )) {
				$error = 1;
				$error_mess = "Nonce verification failed";
				// Send error response
				wp_send_json_error(array(
					'error_mess' => $error_mess
				));				
				wp_die();
			}
			if (isset($_POST['post_id'])) {
					
					$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
					$start_time = get_post_meta($post_id, 'saab_start_time', true);
					$end_time = get_post_meta($post_id, 'saab_end_time', true);
					$break_times = get_post_meta($post_id, 'saab_breaktimeslots', true);
					$duration_minutes = get_post_meta($post_id, 'saab_timeslot_duration', true);
					$gap_minutes = get_post_meta($post_id, 'saab_steps_duration', true);
			
					$available_timeslots_list = $this->saab_admin_generate_timeslots($start_time, $end_time, $duration_minutes, $gap_minutes, $break_times, $post_id);
				
					foreach ($available_timeslots_list as $index => $timeslot) {
						$start_time = isset($timeslot['start_time_slot']) ? $timeslot['start_time_slot'] : '';
						$end_time = isset($timeslot['end_time_slot']) ? $timeslot['end_time_slot'] : '';
						$output .= '<div class="form-row timeslot-row generatetimeslot">';
						$output .= '<div class="form-group col-md-3">';
						$output .= '<label>Start Time:</label>';
						$output .= '<input type="time" class="form-control" name="generatetimeslot[' . esc_attr($index) . '][start_time]" value="' . esc_attr($start_time) . '">';
						$output .= '</div>';
						$output .= '<div class="form-group col-md-3">';
						$output .= '<label>End Time:</label>';
						$output .= '<input type="time" class="form-control" name="generatetimeslot[' . esc_attr($index) . '][end_time]" value="' . esc_attr($end_time) . '">';
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
		 * Generate time slots based on specified parameters.
		 *
		 * This function calculates and generates time slots within a given time range, considering
		 * the desired duration, gaps between slots, and breaks. It is used in the administration panel
		 * to create and manage time slots for a specific post.
		 *
		 * @param string $start_time      The starting time for generating time slots (e.g., "09:00 AM").
		 * @param string $end_time        The ending time for generating time slots (e.g., "05:00 PM").
		 * @param array $duration_minutes  The duration of each time slot in minutes.
		 * @param array $gap_minutes      The gap between time slots in minutes.
		 * @param array $break_times     An array of break times during which no slots will be generated.
		 * @param int $post_id           The ID of the associated post.
		 *
		 */
		function saab_admin_generate_timeslots($start_time, $end_time, $duration_minutes, $gap_minutes, $break_times, $post_id){
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
				$start_time_slot = gmdate('H:i', $current_timestamp);
				$end_time_slot = gmdate('H:i', $end_timeslot);
				$available_timeslots[] = array(
					'start_time_slot' => $start_time_slot,
					'end_time_slot' => $end_time_slot,
				);
	
				$current_timestamp = $end_timeslot + ($gap * 60);
			}
			return $available_timeslots;
		}
		/**
		 * Save user mapping fields.
		 *
		 * This function is responsible for saving user mapping fields.
		 */
		function saab_save_user_mapping() {
			if (!is_admin()) {
				wp_send_json_error('Invalid request.');
			}
			if (!isset($_POST['security']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['security'] ) ) , 'saab_ajax_nonce' )) {
				wp_send_json_error(array('message' => 'Nonce verification failed'));
				wp_die();
			}
			$user_mapping = isset($_POST['saabuser_mapping']) ? sanitize_text_field($_POST['saabuser_mapping']) : '';
		
			parse_str($user_mapping, $user_mapping_array);
		
			$post_id = isset($user_mapping_array['post_id']) ? sanitize_text_field($user_mapping_array['post_id']) : '';
			if (!empty($post_id)) {
				update_post_meta($post_id, 'saab_user_mapping', $user_mapping_array);

				$response['message'] = esc_html__('User mapping saved successfully.', 'smart-appointment-booking');
			} else {
				$response['message'] = esc_html__('Post ID is missing.', 'smart-appointment-booking');
			
			}
			wp_send_json($response);
			exit;
		}
		/**
		 * Save confirmation settings.
		 *
		 * This function is responsible for processing and saving confirmation settings associated with a specific post.
		 * It validates the security token and processes the form data sent via AJAX to update the post's confirmation settings.
		 *
		 * @return void
		 */
		function saab_save_confirmation() {
			if (!isset($_POST['security']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['security'] ) ) , 'saab_ajax_nonce' )) {
				$response['message'] = esc_html__('Something went wrong', 'smart-appointment-booking');
				wp_send_json($response);
				exit;
			}
			if (isset($_POST['confirmation_data'])) {

				parse_str(wp_unslash(sanitize_text_field($_POST['confirmation_data'])), $formdata);
				
				$post_id = $formdata['post_id'];
				if (isset($formdata['confirmation'])) {
					$redirect_url = sanitize_text_field($formdata['confirmation']);
					update_post_meta($post_id, 'saab_confirmation', $redirect_url);
				}
				if (isset($formdata['redirect_text'])) {
					$wp_editor_value = wp_kses_post($formdata['redirect_text']);
					update_post_meta($post_id, 'saab_redirect_text', $wp_editor_value);
				}
				if (isset($formdata['redirect_page'])) {
					$redirect_page = sanitize_text_field($formdata['redirect_page']);
					update_post_meta($post_id, 'saab_redirect_page', $redirect_page);
				}
				if (isset($formdata['redirect_url'])) {
					$redirect_url = sanitize_text_field($formdata['redirect_url']);
					update_post_meta($post_id, 'saab_redirect_url', $redirect_url);
				}
				$response['message'] = esc_html__('Saved Successfully', 'smart-appointment-booking');

			}else{
				$response['message'] = esc_html__('Something went wrong', 'smart-appointment-booking');
			}

			wp_send_json($response);
			exit;
		}	
		/**
		 * Disable title editing for a specific post type's metabox.
		 *
		 * This function is used to prevent the editing of the post title in the WordPress editor for a
		 * specific post type named 'manage_entries'. It removes the title metabox using JavaScript
		 * when editing posts of this post type.
		 *
		 * @return void
		 */
		function saab_disable_title_editing_for__post_type() {
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
		 * Add custom booking status and form filters to the 'manage_entries' custom post type.
		 *
		 * This function adds custom filters for booking status and associated forms in the WordPress admin
		 * when viewing the 'manage_entries' custom post type. It provides options to filter entries by booking status
		 * and associate them with specific forms.
		 *
		 * @param string $post_type The post type to which filters are being added.
		 *
		 * @return void
		 */
		function saab_add_custom_booking_status_filter($post_type) {

			if('manage_entries' !== $post_type){
				return; 
			}
			
			$args = array(
				'post_type' => 'manage_entries',
				'posts_per_page' => 1, // Fetch only one post to check if any exists.
			);
			
			$has_entries = new WP_Query($args);
			
			if ($has_entries->have_posts()) {
			
				if (isset($_GET['booking_status_filter_nonce']) && 	! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_GET['booking_status_filter_nonce'] ) ) , 'booking_status_filter_nonce' )) {
					$status = isset($_GET['booking_status']) ? sanitize_text_field( wp_unslash($_GET['booking_status'] )) : '';
				}else{
					$status = '';
				}
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
					'post_type' => 'saab_form_builder',
					'posts_per_page' => -1,
				);
				wp_nonce_field('booking_status_filter_nonce', 'booking_status_filter_nonce');
				echo '<select name="booking_status" class="form-control">';
				
				foreach ($options as $value => $label) {
					$selected = selected($status, $value, false);
					echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
				}
				echo '</select>';

				$selected_form_id = isset($_GET['form_filter']) ? sanitize_text_field($_GET['form_filter']) : '';
		
				$forms_query = new WP_Query($args);
		
				echo '<select name="form_filter">';
				echo '<option value="">All Forms</option>';
				while ($forms_query->have_posts()) {
					$forms_query->the_post();
					$selected = selected($selected_form_id, get_the_ID(), false);
					echo '<option value="' . esc_attr(get_the_ID()) . '" ' . esc_attr($selected) . '>' . esc_html(get_the_title()) . '</option>';
				}
				echo '</select>';
			}
		}
		/**
		 * Filter 'manage_entries' by selected booking status and form criteria.
		 *
		 * This function modifies the main query for the 'manage_entries' custom post type in the WordPress admin,
		 * allowing filtering by booking status and associated form. It ensures that entries are displayed according
		 * to the selected criteria when filtering is applied.
		 *
		 * @param WP_Query $query The main WordPress query object.
		 *
		 * @return void
		 */
		function saab_filter_custom_booking_status($query) {
			global $pagenow, $typenow;
			if (!is_admin() || !in_array($query->get('post_type'), array('manage_entries'))) {
				return;
			}
			
			if (!isset($_GET['booking_status_filter_nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_GET['booking_status_filter_nonce'] ) ) , 'booking_status_filter_nonce' )) {
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
							'key' => 'saab_form_id',
							'value' => $form_filter,
							'compare' => '='
						);
					}

					$query->set('meta_query', $meta_query);
				}
			}
		}
		/**
		 * Add a link to configure email notifications in the 'Submit' meta box.
		 *
		 * This function enhances the WordPress admin interface for the 'saab_form_builder' custom post type.
		 * It adds a link that allows users to configure email notifications associated with the form.
		 *
		 * @return void
		 */
		function saab_modify_submitdiv_content() {
			global $post;
			$post_id = $post->ID;
			$post_type = get_post_type( $post_id );
			if($post_type === 'saab_form_builder'){
				$form_id = get_post_meta($post_id,'saab_form_id',true);
				$page_slug = 'notification-settings';
				$post_type = 'saab_form_builder';
				
				$admin_url = esc_url(admin_url('admin.php'));
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
		 * Retrieve paginated items for the waiting list.
		 *
		 * This function handles an AJAX request to fetch and display paginated items on the waiting list for a specific timeslot and booking date.
		 *
		 * @return void
		 */
		function saab_get_paginated_items_for_waiting_list(){
			// Verify the nonce
			if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['security'] ) ) , 'get_paginated_items_nonce' )) {
				// Send the response back to the Ajax request
				echo esc_html(ob_get_clean());
				wp_die(); // End the script
			}
			// Define the current page number
			
			$current_page = isset($_POST['page']) ? absint($_POST['page']) : 1;
			$timeslot = isset($_POST['timeslot']) ? sanitize_text_field($_POST['timeslot']) : '';
			$booking_date = isset($_POST['booking_date']) ? sanitize_text_field($_POST['booking_date']) : '';

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
				echo '<th style="width:20%">No of Bookings</th>';
				echo '<th style="width:25%">Published Date</th>';
				echo '<th style="width:5%">Edit</th>';
				echo '</tr>';
				$i = ($current_page - 1) * 5 + 1;
				while ($query->have_posts()) {
					$query->the_post();
					$post_id = get_the_ID();
					$post_title = get_the_title();
					$booking_status = get_post_meta($post_id, 'saab_entry_status', true);
					$no_of_bookings = get_post_meta($post_id, 'saab_slotcapacity', true);
					
					if ($booking_status === 'waiting') {
						echo '<tr>';
						echo '<td>' . esc_html($i) . '-' . esc_html($post_id) . '</td>';
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
				echo '</div>';
				wp_reset_postdata();
		
				// Calculate the total number of pages
				$total_pages = $query->max_num_pages;
				echo '<div id="pagination-links" style="font-size: 15px;font-weight: 600;">';
				echo '<span class="item-count" style="margin-right: 5px;">' . esc_html($query->found_posts) . ' Items</span>';
				if ($total_pages > 1) {
				
					echo '<select id="saabpage-number" data-timeslot="' . esc_attr($timeslot) . '" data-booking_date="' . esc_attr($booking_date) . '" data-nonce="'.esc_attr(wp_create_nonce('get_paginated_items_nonce')).'">';
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
					echo esc_html($total_pages);
				
				}
				echo '</div>';	
			}		
			echo esc_html(ob_get_clean());
			
			wp_die(); 
		}
	}
	add_action( 'plugins_loaded', function() {
		$SAAB_Admin_Action = new SAAB_Admin_Action();
	} );

}
