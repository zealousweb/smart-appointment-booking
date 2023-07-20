<?php
/**
 * WP_SAB_Admin_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @package WP Smart Appointment & Booking
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_SAB_Admin_Action' ) ) {

	/**
	 *  The WP_SAB_Admin_Action Class
	 */
	class WP_SAB_Admin_Action {

		function __construct()  {

			add_action( 'admin_enqueue_scripts',array( $this, 'enqueue_styles' ));
			add_action( 'admin_enqueue_scripts',array( $this, 'enqueue_scripts' ));
			add_action('admin_menu',array( $this, 'wp_sab_add_post_type_menu' ));
				
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

			add_action('wp_ajax_send_manual_notification_handler', array( $this, 'send_manual_notification_handler' ) );
			add_action('wp_ajax_nopriv_send_manual_notification_handler', array( $this, 'send_manual_notification_handler' ) );

			add_action( 'restrict_manage_posts', array( $this, 'add_custom_booking_status_filter' ) );
			add_action( 'pre_get_posts', array( $this, 'filter_custom_booking_status' ) );
			
			add_action('post_submitbox_misc_actions', array( $this, 'modify_submitdiv_content' ) );
			add_action('delete_post', array( $this, 'check_waiting_list_on_trashed_delete' ) );

			
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
		 * Action: admin_init
		 *
		 * - Register admin min js and admin min css
		 *
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
		function enqueue_styles() {

			if(isset($_GET['post'])){
				$postid = $_GET['post'];
				$post_type = get_post_type($postid);
			}else{
				$post_type = '';
			}

			
			if (is_singular('sab_form_builder') || is_singular('zeal_formbuilder') || (isset($post_type) && ($post_type == 'sab_form_builder' || $post_type == 'manage_entries')) || (isset($_GET['post_type']) && ($_GET['post_type'] === 'sab_form_builder' || $_GET['post_type'] === 'manage_entries'))) {
				wp_enqueue_style( '_admin_css',WP_SAB_URL.'assets/css/admin.css', array(), 1.1, 'all' );	
				wp_enqueue_style( 'sab_font-awesomev1','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', array(), 1.1, 'all' );
				//formio
				wp_enqueue_style( 'sab_formio_full_min',WP_SAB_URL.'assets/css/formio/formio.full.min.css', array(), 1.1, 'all' );
				//boostrap
				wp_enqueue_style( 'sab_boostrap_min',WP_SAB_URL.'assets/css/boostrap/boostrap.min.css', array(), 1.1, 'all' );	
			 }
			 if (isset($_GET['page']) && $_GET['page'] === 'notification-settings') {
				//boostrap
				wp_enqueue_style( 'datatable_admin_css',WP_SAB_URL.'assets/css/boostrap/jquery.dataTables.min.css', array(), 1.1, 'all' );
			 }

			wp_register_style( WP_SAB_PREFIX . '_admin_min_css', WP_SAB_URL . 'assets/css/admin.min.css', array(), WP_SAB_VERSION );
			wp_register_style( WP_SAB_PREFIX . '_admin_css', WP_SAB_URL . 'assets/css/admin.css', array(), WP_SAB_VERSION );
	
			
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
				wp_enqueue_script( 'sab_popper.minjs', WP_SAB_URL.'assets/js/boostrap/popper.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'sab_jquery-3.7.0.slim.min', WP_SAB_URL.'assets/js/boostrap/jquery-3.7.0.slim.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'sab_jquery-3.7.0.min',WP_SAB_URL.'assets/js/boostrap/jquery-3.7.0.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'sab_boostrap.min', WP_SAB_URL.'assets/js/boostrap/boostrap.min.js', array( 'jquery' ), 1.1, false );				
				wp_enqueue_script( 'sab_boostrap_bundlemin', WP_SAB_URL.'assets/js/boostrap/boostrap.bundle.min.js', array( 'jquery' ), 1.1, false );

				//formio folder
			 	wp_enqueue_script( 'sab_formio_full_min', WP_SAB_URL.'assets/js/formio/formio.full.min.js', array( 'jquery' ), 1.1, false );
				
				//booking folder
				wp_enqueue_script( 'booking-form', WP_SAB_URL.'assets/js/booking/booking-form.js', array( 'jquery' ), 1.1, false );
				wp_localize_script('booking-form', 'ajax_object', array(
					'ajax_url' => admin_url('admin-ajax.php')
				));
	
				wp_enqueue_script( 'admin', WP_SAB_URL.'assets/js/admin.js', array( 'jquery' ), 1.1, false );
			 }
			 if (isset($_GET['page']) && $_GET['page'] === 'notification-settings') {
				//boostrap folder
				wp_enqueue_script( 'datatble_admin',WP_SAB_URL.'assets/js/boostrap/jquery.dataTables.min.js',array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'datatbleboostrap',WP_SAB_URL.'assets/js/boostrap/dataTables.boostrap5.min.js',array( 'jquery' ), 1.1, false );
			 }

			 wp_register_script( WP_SAB_PREFIX . '_admin_js', WP_SAB_URL . 'assets/js/admin.min.js', array( 'jquery-core' ), WP_SAB_VERSION );

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
		
			// Send the JSON response
			echo json_encode($response);
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
		function wp_sab_add_post_type_menu() {

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
				'Booking FormBuilder',
				'Booking FormBuilder',
				'manage_options',
				'edit.php?post_type=sab_form_builder',
				'',
				 'dashicons-calendar-alt',
				// 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" width="1024" height="1024" viewBox="0 0 1024 1024"><path fill="currentColor" d="m960 95.888l-256.224.001V32.113c0-17.68-14.32-32-32-32s-32 14.32-32 32v63.76h-256v-63.76c0-17.68-14.32-32-32-32s-32 14.32-32 32v63.76H64c-35.344 0-64 28.656-64 64v800c0 35.343 28.656 64 64 64h896c35.344 0 64-28.657 64-64v-800c0-35.329-28.656-63.985-64-63.985zm0 863.985H64v-800h255.776v32.24c0 17.679 14.32 32 32 32s32-14.321 32-32v-32.224h256v32.24c0 17.68 14.32 32 32 32s32-14.32 32-32v-32.24H960v799.984zM736 511.888h64c17.664 0 32-14.336 32-32v-64c0-17.664-14.336-32-32-32h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32zm0 255.984h64c17.664 0 32-14.32 32-32v-64c0-17.664-14.336-32-32-32h-64c-17.664 0-32 14.336-32 32v64c0 17.696 14.336 32 32 32zm-192-128h-64c-17.664 0-32 14.336-32 32v64c0 17.68 14.336 32 32 32h64c17.664 0 32-14.32 32-32v-64c0-17.648-14.336-32-32-32zm0-255.984h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32h64c17.664 0 32-14.336 32-32v-64c0-17.68-14.336-32-32-32zm-256 0h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32h64c17.664 0 32-14.336 32-32v-64c0-17.68-14.336-32-32-32zm0 255.984h-64c-17.664 0-32 14.336-32 32v64c0 17.68 14.336 32 32 32h64c17.664 0 32-14.32 32-32v-64c0-17.648-14.336-32-32-32z"/></svg>' ), // Replace this with your actual SVG code
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
		function view_booking_entry( $post ){
            $post_id = $_GET['post_id'] ;
            // $post_id = $post_id;
            $form_data = get_post_meta( $post_id, 'sab_submission_data', true );	
            $form_id = get_post_meta( $post_id, 'sab_form_id', true );	
            $timeslot = get_post_meta( $post_id, 'timeslot', true );
            // echo "<br>".	
            $booking_date = get_post_meta( $post_id, 'booking_date', true );
            $array_of_date = explode('_',$booking_date);
            // echo "<pre>";
            // print_r($array_of_date);
            $bookedmonth = $array_of_date[2];
            $bookedday =$array_of_date[3];
            $bookedyear =$array_of_date[4];
            $booked_date = $bookedday."-".$bookedmonth."-".$bookedyear;
			$booked_date = date('F j, Y', strtotime($booked_date));
            // $totalbookings = get_post_meta( $post_id, 'totalbookings', true );	
            $slotcapacity = get_post_meta( $post_id, 'slotcapacity', true );	

            // echo $checkseats = $this->get_available_seats_per_timeslot($timeslot,$booked_date);

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
						<th class="h6"><?php echo __('Form Title', 'textdomain'); ?></th>
						<td><?php echo $booking_form_title; ?></td>
					</tr>
					
					<tr>
						<th class="h6"><?php echo __('Status', 'textdomain'); ?></th>
						<td><?php echo $status; ?></td>
					</tr>
					<tr>
						<th class="h6"><?php echo __('Customer', 'textdomain'); ?></th>
						<td><?php echo __('Guest', 'textdomain'); ?></td>
					</tr>
					<tr>
						<th class="h6"><?php echo __('Booked Date', 'textdomain'); ?></th>
						<td><?php echo $booked_date; ?></td>
					</tr>
					<tr>
						<th class="h6"><?php echo __('Timeslot', 'textdomain'); ?></th>
						<td><?php echo $timeslot; ?></td>
					</tr>
					<tr>
						<th class="h6"><?php echo __('No of Slots Booked', 'textdomain'); ?></th>
						<td><?php echo $slotcapacity; ?></td>
					</tr>
				</table>
			</div>

			<div class="main-entries-section" id="main_entries_section2">
				<!-- <h3>Details</h3> -->
				<table id="main_entries_table2">
					<?php
					foreach($form_data['data'] as $form_key => $form_value){
						if($form_key !== 'submit'){
							echo "<tr><th class='h6'>".ucfirst($form_key)."</th><td>".$form_value."</td></tr>";
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
		function send_manual_notification_handler() {
			$response = array(					
				'message' => '',
				'mail_message' => '',
				
			);
			if (isset($_POST['status']) && isset($_POST['form_id']) && isset($_POST['post_id'])) {

				$get_bookingId = sanitize_text_field($_POST['bookingId']);
				$bookingId = $_POST['post_id'];
				$status = $_POST['status'];
				$formdata = get_post_meta($bookingId,'sab_submission_data',true);
				$form_id = get_post_meta($bookingId,'sab_form_id',true);
				update_post_meta($bookingId, 'entry_status', $status);
			
				$listform_label_val = $this->create_key_value_formshortcodes($bookingId,$formdata);
				$listform_label_val['Status'] = $status;
				
				$message = do_action('notification_send', $status, $form_id, $bookingId, $listform_label_val);
			
				$response = array(					
					'message' => __('Your booking has been cancelled succesfully','textdomain'),
					'mail_message' => $message,
				);
			}
			
			wp_send_json($response);
			wp_die(); // Always include this line to end the AJAX request
		}
		
		
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
		function render_notification_settings_page() {
			// Add your page content here
			echo "<div class='notification-page-main m-4 p-1 ' >";
			
			if (isset($_GET['post_type']) && isset($_GET['post_id'])) {
				$post_type = $_GET['post_type'];
				$post_id = $_GET['post_id'];
				// echo "Post Type: $post_type<br>";
				// echo "Post ID: $post_id";
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
						// echo "<pre>";
						// print_r($shortcodes);
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
								echo '<div class=""><label style="font-weight: bold;">' . __('Form shortcodes', 'textdomain') . '</label></div>';
								foreach ($form_data['form'] as $objform) {
									echo '<span class="copy-text" style="margin-right: 5px; font-family: Arial; font-size: 14px;">[' . $objform . ']</span>';
								}
								$enable_booking = get_post_meta($post_id, 'enable_booking', true);
								if( $enable_booking ){
									echo '<div class=""><label style="font-weight: bold;">' . __('Booking shortcodes', 'textdomain') . '</label></div>';
									foreach ($form_data['booking'] as $objbooking) {
										echo '<span class="copy-text" style="margin-right: 5px;margin-bottom: 5px; font-family: Arial; font-size: 14px;">[' . $objbooking . ']</span>';
									}
								}
								echo '<div class=""><label style="font-weight: bold;">' . __('Post shortcodes', 'textdomain') . '</label></div>';
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
									// echo "<pre>";
									// print_r($notification_metadata);
									if (!empty($notification_metadata) && is_array($notification_metadata)) {
										$post_id = $_GET['post_id'];
										?>
										
										<div id="tab5" class="tab-content">
											<input type="hidden" name="post_id" id="post_id" value="<?php echo $_REQUEST['post_id']; ?>" >
											<table class="table notificationtable datatable table-striped" id="notifytable" >
												<thead>
													<tr>
														<th scope="col" ><input type="checkbox" id="main-check-all" class="maincheckall" value="1" ></th>
														<!-- <th scope="col"></th> -->
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
						<!-- <form method="post" class="confirmation_form" id="confirm_form"> -->
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
							<input type="hidden" name="post_id" value="<?php echo $post_id;?>">
							<input type="submit" value="Save" class="btn btn-primary" name="Save">
						</form>
						<p id="confirm_msg" class="h6 m-2"></p>
					</div>
				</div>
				<?php
			} else {
				echo "Error: Post type and/or post ID not found.";
			}
			echo '</div>';
		}
		function admin_get_shortcodes($post_id){
			$form_list = array();
			$shortcode_list = array();
			$form_data1 = get_post_meta( $post_id, '_formschema', true ); 
			if(isset($form_data1) && !empty($form_data1)){
				$form_data1=json_decode($form_data1);
				foreach ($form_data1 as $obj) {  
					// if($obj !== 'submit'){				
					$form_list[] = $obj->key;
					// }
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
						$response['message'] = __('Notification saved successfully', 'textdomain');
						$response['state'] = $new_state;
					} else {
						$response['message'] = __('Something went wrong', 'textdomain');
					}
				} else {
					$response['message'] = __('Something went wrong', 'textdomain');
				}
			}
		
			wp_send_json($response);
		}
		
		function delete_notification_indexes() {
			if (isset($_POST['indexes'])) {
				$post_id = $_POST['post_id']; // Replace with your actual post ID
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
			// print_r($data);
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
										<!-- <div class="form-group">
											<label>Email Setting</label>
										</div> -->
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
										<!-- <div class="form-group">
											<label for="additional-header">Additional Headers</label>
											<textarea id="additional-header" name="additional_headers" class="form-control" rows="4" required><?php echo isset($additional_headers) ? $additional_headers : ''; ?></textarea>
										</div> -->

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
										<!-- <div class="form-group">
											<label for="attachments">Attachments</label>
											<input type="file" id="attachments" name="attachments[]" multiple>
										</div> -->
										<?php
											// echo "<pre>";
											// print_r($attachments);
										?>
									</div>
									<p id="suc_loc" ></p>
									<input type="submit" id="submit_notification" name="submit_notification" class="btn btn-primary">
									<button type="button" class="btn btn-secondary" id="closemodal" data-dismiss="modal">Close</button>
								</form>
								<!-- <p id="suc_loc"></p> -->
							</div>
					</div>
				</div> 
			</div>
			<?php
			//  $modalContent = ob_get_clean();
			//  return $modalContent;
		}

		function sab_email_template_page_callback(){
			echo __('Email Template','sab');
		}
				
		function sab_save_form_data() {
			
			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

			$form_data = isset( $_POST['form_data'] ) ? $_POST['form_data'] : array();
			
			update_post_meta( $post_id, '_formschema', $form_data );

			wp_send_json_success( 'Form data saved successfully.' );
			exit;

		}
		function populate_custom_column($column, $post_id) {
			if ($column === 'shortcode') {				
				echo "[booking_form form_id='".$post_id."']";
			}
			if ($column === 'form') {	
				$form_id = get_post_meta($post_id,'sab_form_id',true);	
				$form_title = get_the_title($form_id);	
				
				if (isset($form_title)) {	
					echo __($form_title,'textdomain');
				}else{
					echo '-';
				}
			}
			if ($column === 'booking_status') {		
				$booking_status = get_post_meta($post_id,'entry_status',true);
			
				if (isset($booking_status) && !empty($booking_status)) {	
					echo ucfirst(__($booking_status,'textdomain'));
				}else{
					echo '-';
				}
				
			}
			if ($column === 'booking_date') {
				$booking_date = get_post_meta($post_id,'booking_date',true);
				if (isset($booking_date) && !empty($booking_date)) {
				
				$array_of_date = explode('_',$booking_date);
				// echo "<pre>";
				// print_r($array_of_date);
				$bookedmonth = $array_of_date[2];
				$bookedday =$array_of_date[3];
				$bookedyear =$array_of_date[4];
				$booked_date = $bookedday."-".$bookedmonth."-".$bookedyear;
				$booked_date = date('d F, Y', strtotime($booked_date));	
					if (isset($booking_date) && !empty($booking_date)) {	
						echo __($booked_date,'textdomain');
					}
				}else{
					echo '-';
				}	
			}
			if ($column === 'timeslot') {
				$timeslot = get_post_meta( $post_id, 'timeslot', true );						
			
				if (isset($timeslot) && !empty($timeslot)) {	
					echo __($timeslot,'textdomain');
				}else{
					echo '-';
				}
			}
			

		}
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
				$start_time_slot = date('H:i', $current_timestamp);
				$end_time_slot = date('H:i', $end_timeslot);
	
				// Add the timeslot to the available timeslots array
				//  $available_timeslots[] = $start_time_slot . ' - ' . $end_time_slot;
				$available_timeslots[] = array(
					'start_time_slot' => $start_time_slot,
					'end_time_slot' => $end_time_slot,
				);
	
				// Move to the next available timeslot (including the gap)
				$current_timestamp = $end_timeslot + ($gap * 60);
			}
			return $available_timeslots;
		}

		function zfb_save_user_mapping() {
			// Check if the request came from the admin side (optional)
			if (!is_admin()) {
				wp_send_json_error('Invalid request.');
			}
		
			$user_mapping = isset($_POST['zfbuser_mapping']) ? stripslashes($_POST['zfbuser_mapping']) : '';
		
			parse_str($user_mapping, $user_mapping_array);
		
			$post_id = isset($user_mapping_array['post_id']) ? sanitize_text_field($user_mapping_array['post_id']) : '';
			if (!empty($post_id)) {
				update_post_meta($post_id, 'user_mapping', $user_mapping_array);

				$response['message'] = __('User mapping saved successfully.', 'textdomain');
			} else {
				$response['message'] = __('Post ID is missing.', 'textdomain');
			
			}
			wp_send_json($response);
			exit;
		}
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
				$response['message'] = __('Saved Successfully', 'textdomain');

			}else{
				$response['message'] = __('Something went wrong', 'textdomain');
			}

			wp_send_json($response);
			exit;
		}	
		function disable_title_editing_for_custom_post_type() {
			global $post_type;
		
			if ($post_type === 'manage_entries') {
				?>
				<script>
					jQuery(document).ready(function($) {
						$('#titlediv').remove(); // Remove the title field
					});
				</script>
				<?php
			}
		}
		function add_custom_filters() {
			global $typenow;
		
			if ( 'manage_entries' === $typenow ) {
				add_action( 'restrict_manage_posts', array( $this, 'add_custom_booking_status_filter' ) );
				// add_action( 'restrict_manage_posts', array( $this, 'add_custom_form_filter_dropdown' ) );
				add_action( 'pre_get_posts', array( $this, 'filter_custom_booking_status' ) );
				// add_action( 'pre_get_posts', array( $this, 'filter_custom_form' ) );
			}
		}
		
		function add_custom_booking_status_filter($post_type) {
			
			if ( 'manage_entries' != $post_type ) {
				return;
			}
			$status = isset( $_GET['booking_status'] ) ? $_GET['booking_status'] : '';
		
			$options = array(
				'any' => 'Status',
				'booked' => 'Booked',
				'approved' => 'Approved',
				'cancelled' => 'Cancelled',
				'pending' => 'Pending',
				'waiting' => 'Waiting',
				'submitted' => 'Submitted'
			);
		
			echo '<select name="booking_status" class="form-control">';
			foreach ( $options as $value => $label ) {
				$selected = selected( $status, $value, false );
				echo '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select>';

				$selected_form_id = isset( $_GET['form_filter'] ) ? $_GET['form_filter'] : '';
		
				$args = array(
					'post_type' => 'sab_form_builder',
					'posts_per_page' => -1
				);
				$forms_query = new WP_Query( $args );
			
				echo '<select name="form_filter">';
				echo '<option value="">All Forms</option>';
				while ( $forms_query->have_posts() ) {
					$forms_query->the_post();
					$selected = selected( $selected_form_id, get_the_ID(), false );
					echo '<option value="' . esc_attr( get_the_ID() ) . '" ' . $selected . '>' . esc_html( get_the_title() ) . '</option>';
				}
				echo '</select>';
		}
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
		
		function modify_submitdiv_meta_box() {
			remove_meta_box('submitdiv', 'post', 'side');
			add_meta_box('submitdiv', 'Modified Publish', array($this,'custom_submitdiv_content'), 'post', 'side', 'core');
		}
		
		// Custom meta box content
		function custom_submitdiv_content($post) {
			// Output the default submitdiv content
			submit_button(__('Publish'), 'primary', 'publish', false, array('id' => 'publish'));

			// Output custom dropdown
			echo '<div class="misc-pub-section">';
			echo '<label for="custom_dropdown">Custom Dropdown:</label>';
			echo '<select name="custom_dropdown" id="custom_dropdown">';
			echo '<option value="option1">Option 1</option>';
			echo '<option value="option2">Option 2</option>';
			echo '<option value="option3">Option 3</option>';
			echo '</select>';
			echo '</div>';

			// Output the "Move to Trash" link
			echo '<div class="misc-pub-section">';
			echo '<a href="' . esc_url(get_delete_post_link($post->ID)) . '">Move to Trash</a>';
			echo '</div>';
		}

		function modify_submitdiv_content() {
			global $post;
			$post_id = $post->ID;
			$post_type = get_post_type( $post_id );
			if($post_type === 'sab_form_builder'){
				$form_id = get_post_meta($post_id,'sab_form_id',true);
				$page_slug = 'notification-settings';
				$post_type = 'sab_form_builder';
				// $post_id = 5508;

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
		
		function check_waiting_list_on_trashed_delete($post_id) {
			if (!wp_is_post_revision($post_id) && !wp_is_post_autosave($post_id)) {
				// Your custom logic here for when a post is permanently deleted
				// This will not run when a post is just moved to the trash
			} else {
				// Your custom logic here for when a post is moved to the trash
			}
		}
		
	}

	add_action( 'plugins_loaded', function() {
		WP_SAB()->admin->action = new WP_SAB_Admin_Action;
	} );
}
