<?php
/**
 * PB_Admin_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @package Plugin name
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PB_Admin_Action' ) ) {

	/**
	 *  The PB_Admin_Action Class
	 */
	class PB_Admin_Action {

		function __construct()  {

			add_action( 'admin_init', array( $this, 'action__admin_init' ) );

			add_action( 'admin_enqueue_scripts',array( $this, 'enqueue_styles' ));
			add_action( 'admin_enqueue_scripts',array( $this, 'enqueue_scripts' ));
			add_action('admin_menu',array( $this, 'add_main_custom_post_type_menu' ));
			
			// add_action( 'add_meta_boxes', array( $this, 'bms_add_meta_box' ) );		
			add_action( 'wp_ajax_bms_save_form_data', array( $this, 'bms_save_form_data' ));
			add_action('manage_bms_forms_posts_custom_column', array( $this, 'populate_custom_column' ), 10, 2);
			add_action('manage_bms_entries_posts_custom_column', array( $this, 'populate_custom_column' ), 10, 2);

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
		function action__admin_init() {

			wp_register_script( PB_PREFIX . '_admin_js', PB_URL . 'assets/js/admin.min.js', array( 'jquery-core' ), PB_VERSION );
			wp_register_style( PB_PREFIX . '_admin_min_css', PB_URL . 'assets/css/admin.min.css', array(), PB_VERSION );
			wp_register_style( PB_PREFIX . '_admin_css', PB_URL . 'assets/css/admin.css', array(), PB_VERSION );
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

			
			if (is_singular('bms_forms') || is_singular('zeal_formbuilder') || (isset($post_type) && ($post_type == 'bms_forms' || $post_type == 'bms_entries')) || (isset($_GET['post_type']) && ($_GET['post_type'] === 'bms_forms' || $_GET['post_type'] === 'bms_entries'))) {
				wp_enqueue_style( '_admin_css',PB_URL.'assets/css/admin.css', array(), 1.1, 'all' );
				wp_enqueue_style( 'bms_boostrap_min',PB_URL.'assets/css/bootstrap.min.css', array(), 1.1, 'all' );
				wp_enqueue_style( 'bms_formio_full_min',PB_URL.'assets/css/formio.full.min.css', array(), 1.1, 'all' );
				wp_enqueue_style( 'bms_font-awesomev1','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', array(), 1.1, 'all' );
				
			 }
			 if (isset($_GET['page']) && $_GET['page'] === 'notification-settings') {
				wp_enqueue_style( 'dattable_admin_css',PB_URL.'assets/css/jquery.dataTables.min.css', array(), 1.1, 'all' );
			 }
			
		}

		/**
		* WP Enqueue Scripts
		*/
		function enqueue_scripts() {

			// wp_enqueue_script('custom-script', 'path/to/your/custom-script.js', array('jquery'));

		
			if(isset($_GET['post'])){
				$postid = $_GET['post'];
				$post_type = get_post_type($postid);
			}else{
				$post_type = '';
			}

			if (is_singular('bms_forms') || is_singular('zeal_formbuilder') || $post_type == 'bms_forms' || isset($_GET['post_type']) && $_GET['post_type'] === 'bms_forms' || isset($_GET['post_type']) && $_GET['post_type'] === 'bms_entries' ||  $post_type == 'bms_entries') {
				wp_enqueue_script( 'bms_bootstrapbundlemin', PB_URL.'assets/js/bootstrap.bundle.min.js', array( 'jquery' ), 1.1, false );
			 	wp_enqueue_script( 'bms_formio_full_min', PB_URL.'assets/js/formio.full.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'bms_popper.minjs', PB_URL.'assets/js/popper.min.js', array( 'jquery' ), 1.1, false );
			 	wp_enqueue_script( 'bms_bootstrap.min', PB_URL.'assets/js/bootstrap.min.js', array( 'jquery' ), 1.1, false );
			 	wp_enqueue_script( 'bms_jquery-3.7.0.slim.min', PB_URL.'assets/js/jquery-3.7.0.slim.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'bms_jquery-3.7.0.min',PB_URL.'assets/js/jquery-3.7.0.min.js', array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'booking-form', PB_URL.'assets/js/booking-form.js', array( 'jquery' ), 1.1, false );
				wp_localize_script('booking-form', 'ajax_object', array(
					'ajax_url' => admin_url('admin-ajax.php')
				));
	
				wp_enqueue_script( 'admin', PB_URL.'assets/js/admin.js', array( 'jquery' ), 1.1, false );
			 }
			 if (isset($_GET['page']) && $_GET['page'] === 'notification-settings') {
				wp_enqueue_script( 'datatble_admin',PB_URL.'assets/js/jquery.dataTables.min.js',array( 'jquery' ), 1.1, false );
				wp_enqueue_script( 'datatbleboostrap',PB_URL.'assets/js/dataTables.bootstrap5.min.js',array( 'jquery' ), 1.1, false );
			 }
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
		/*
		* Add the main custom post type as the admin menu item
		*/
		function add_main_custom_post_type_menu_old() {
			$labels_form = array(
				'name' => 'Booking Form',
				'singular_name' => 'Booking Form',
			);
		
			$args_form = array(
				'labels' => $labels_form,
				'description' => '',
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'delete_with_user' => false,
				'show_in_rest' => false,
				'rest_base' => '',
				'has_archive' => false,
				'show_in_menu' => 'bms_forms',
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
			register_post_type('bms_forms', $args_form);
		
			$labels = array(
				'name' => 'Booking Entries',
				'singular_name' => 'Booking Entry',
			);
		
			$args = array(
				'labels' => $labels,
				'description' => '',
				'public' => false,
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
		
			register_post_type('bms_entries', $args);
		
			$menu_hook_suffix = add_menu_page(
				'Booking Forms',
				'Booking Form',
				'manage_options',
				'edit.php?post_type=bms_forms',
				'',
				'',
				20
			);
		
			add_submenu_page(
				'edit.php?post_type=bms_forms', 
				'Add New Form', 
				'Add New Form', 
				'manage_options', 
				admin_url('post-new.php?post_type=bms_forms')
			);
		
			add_submenu_page(
				'edit.php?post_type=bms_forms',
				'Booking Entries',
				'Booking Entries',
				'manage_options',
				admin_url('edit.php?post_type=bms_entries')
			);
		
			add_submenu_page(
				'edit.php?post_type=bms_form',
				'Email Templates',
				'Email Templates',
				'manage_options',
				'bms_email_template',
				'bms_email_template_page_callback'
			);
		
			add_submenu_page(
				$menu_hook_suffix, // Use the menu hook suffix as the parent slug
				'Notification Settings', // Page title
				'Notification Settings', // Menu title
				'manage_options', // Required capability to access the page
				'notification-settings', // Page slug
				array($this, 'render_notification_settings_page')
			);
		}
		
		function add_main_custom_post_type_menu() {
			$labels_form = array(
				'name' => 'Booking Form',
				'singular_name' => 'Booking Form',
			);
		
			$args_form = array(
				'labels' => $labels_form,
				'description' => '',
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'delete_with_user' => false,
				'show_in_rest' => false,
				'rest_base' => '',
				'has_archive' => false,
				'show_in_menu' => true, // Set to true
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
			register_post_type('bms_forms', $args_form);
		
			$labels = array(
				'name' => 'Booking Entries',
				'singular_name' => 'Booking Entry',
			);
		
			$args = array(
				'labels' => $labels,
				'description' => '',
				'public' => false,
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
		
			register_post_type('bms_entries', $args);
		
			$menu_hook_suffix = add_menu_page(
				'Booking Forms',
				'Booking Form',
				'manage_options',
				'edit.php?post_type=bms_forms',
				'',
				'',
				20
			);
		
			add_submenu_page(
				'edit.php?post_type=bms_forms', 
				'Add New Form', 
				'Add New Form', 
				'manage_options', 
				admin_url('post-new.php?post_type=bms_forms')
			);
		
			add_submenu_page(
				'edit.php?post_type=bms_forms', // Set parent slug to null
				'Booking Entries',
				'Booking Entries',
				'manage_options',
				'edit.php?post_type=bms_entries'
			);
			add_submenu_page(
				$menu_hook_suffix, // Use the menu hook suffix as the parent slug
				'Notification Settings', // Page title
				'Notification Settings', // Menu title
				'manage_options', // Required capability to access the page
				'notification-settings', // Page slug
				array($this, 'render_notification_settings_page')
			);
			add_submenu_page(
				$menu_hook_suffix, // Use the menu hook suffix as the parent slug
				'View Booking Entry', // Page title
				'View Booking Entry', // Menu title
				'manage_options', // Required capability to access the page
				'view-booking-entry', // Page slug
				array($this, 'view_booking_entry')
			);
		}
		function view_booking_entry( $post ){
            $post_id = $_GET['post_id'] ;
            // $post_id = $post_id;
            $form_data = get_post_meta( $post_id, 'bms_submission_data', true );	
            $form_id = get_post_meta( $post_id, 'bms_form_id', true );	
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
            // $totalbookings = get_post_meta( $post_id, 'totalbookings', true );	
            $slotcapacity = get_post_meta( $post_id, 'slotcapacity', true );	

            // echo $checkseats = $this->get_available_seats_per_timeslot($timeslot,$booked_date);

            if(!empty($form_id)){ 
               $booking_form_title = get_the_title($form_id);               
            }
            $date_generated = get_the_date($post_id);
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
            }
            ?>
            <h3>Booking Details</h3>
			
            <ul>
                <li><?php echo __('Form Title', 'textdomain')." : ".$booking_form_title; ?></li>
                <li><?php echo __('Date Generated', 'textdomain')." : ".$date_generated; ?></li>
                <li><?php echo __('Status', 'textdomain')." : ".$status; ?></li>
                <li><?php echo __('Customer', 'textdomain'); ?> : <?php echo __('Guest', 'textdomain');; ?></li>
                <li><?php echo __('Booking Date', 'textdomain'); ?> : <?php echo __($booked_date, 'textdomain');; ?></li>
                <li><?php echo __('Timeslot', 'textdomain'); ?> : <?php echo __($timeslot, 'textdomain'); ?></li>
                <li><?php echo __('No of Slots Booked', 'textdomain'); ?> : <?php echo __($slotcapacity, 'textdomain'); ?></li>
            </ul>   
            <h3>Details</h3>
            <ul>
                <?php
                foreach($form_data['data'] as $form_key => $form_value){
                    if($form_key !== 'submit'){
                        echo "<li>".$form_key." : ".$form_value."</li>";
                    }
                }
                ?>
            </ul>
			<h3>Notes</h3>

            <?php
        }
		function edit_booking_entry(){
			echo "hello";
		}
		function admin_get_shortcodes_keylabel($post_id){
			$shortcode_list = array();
			$form_data = get_post_meta( $post_id, '_my_meta_value_key', true ); 
			$form_data=json_decode($form_data);
			foreach ($form_data as $obj) {     
				$shortcode_list[] = array(
					'fieldkey'=>esc_attr($obj->key),
					'fieldlabel'=>esc_html($obj->label),
				);
			   
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
						$form_data = get_post_meta( $post_id, '_my_meta_value_key', true ); 
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
								foreach ($form_data as $obj) {
									echo '<span>['.$obj.']</span>';
									echo ' ';
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
								<!-- Modal -->
								<?php
								$notification_metadata = get_post_meta($post_id, 'notification_data', true);
								// echo "<pre>";
								// print_r($notification_metadata);
								if (!empty($notification_metadata) && is_array($notification_metadata)) {
									$post_id = $_GET['post_id'];
									?>	
										<!-- <div class="dataTables_filter">
											<label>
												Search:
												<input type="search" class="form-control" id="notification-search" placeholder="Search by notification title">
											</label>
										</div> -->
										<!-- <input type="search" class="form-control" id="searchbox" placeholder="Search by notification title"> -->
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
														<!-- <td><span><?php //echo $state; ?></span> <span>(Enabled)</span></td> -->
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
									<?php
								}else{
									echo 'No notification data found for the post.';
								}
							echo '</div>';
						} else {
							echo 'Publish Post to Create Notification';
						}
						echo '</div>';
						?>
					</div>
				</div>
				<div class="tab-pane fade" id="content_documentation" role="tabpanel" aria-labelledby="tab_documentation">
					<div class="shortcodes_list col-md-6 m-4">
						<ul>
							<li class="h6">Create Email Alerts for admin and as well for user with on following Status: Booked, Pending, Approved, Cancelled.</li>
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
				<?php
			} else {
				echo "Error: Post type and/or post ID not found.";
			}
			echo '</div>';
			// Include your form or other content for extending the notification properties
		}
		function admin_get_shortcodes($post_id){
			$shortcode_list = array();
			$form_data1 = get_post_meta( $post_id, '_my_meta_value_key', true ); 
			$form_data1=json_decode($form_data1);

			foreach ($form_data1 as $obj) {  				
				$shortcode_list[] = $obj->key;
			}
			$tobe_merged = array('FormId','BookingId','Status','FormTitle','To','FirstName','LastName','Timeslot','BookedSeats','BookingDate','BookedDate','Service','prefixlabel','cost','StartTime','EndTime','CancelBooking');
			$shortcode_list = array_merge($tobe_merged,$shortcode_list);
			
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
												$available_types = array('any', 'booked', 'pending', 'cancelled', 'approved','submitted');
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
											<input type="text" id="email-replyto" name="email_replyto" class="form-control" value="<?php echo isset($email_replyto) ? $email_replyto : ''; ?>" required>
										</div>
										<div class="form-group">
											<label for="email-from">Bcc</label>
											<input type="text" id="email-bcc" name="email_bcc" class="form-control" value="<?php echo isset($email_bcc) ? $email_bcc : ''; ?>" required>
										</div>
										<div class="form-group">
											<label for="email-from">Cc</label>
											<input type="text" id="email-cc" name="email_cc" class="form-control" value="<?php echo isset($email_cc) ? $email_cc : ''; ?>" required>
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

		function bms_email_template_page_callback(){
			echo __('Email Template','bms');
		}
				
		function bms_save_form_data() {
			
			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

			$form_data = isset( $_POST['form_data'] ) ? $_POST['form_data'] : array();
			
			update_post_meta( $post_id, '_my_meta_value_key', $form_data );

			wp_send_json_success( 'Form data saved successfully.' );
			exit;

		}
		function populate_custom_column($column, $post_id) {
			if ($column === 'shortcode') {				
				echo "[booking_form form_id='".$post_id."']";
			}
			if ($column === 'form') {	
				$form_id = get_post_meta($post_id,'bms_form_id',true);	
				$form_title = get_the_title($form_id);	
				echo __($form_title,'textdomain');
			}
			if ($column === 'booking_status') {		
				$booking_status = get_post_meta($post_id,'entry_status',true);
				echo __($booking_status,'textdomain');
				
			}
			if ($column === 'event_status') {
				$form_id = get_post_meta($post_id,'bms_form_id',true);	
				$form_title = get_the_title($form_id);					
				echo __($form_title,'textdomain');
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
		
				$available_timeslots = $this->admin_generate_timeslots($start_time, $end_time, $duration_minutes, $gap_minutes, $break_times, $post_id);
				// Generate the output string
				$output = implode('<br>', $available_timeslots);
				// $output = $available_timeslots;
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
				$start_time_slot = date('h:i A', $current_timestamp);
				$end_time_slot = date('h:i A', $end_timeslot);
	
				// Add the timeslot to the available timeslots array
				$available_timeslots[] = $start_time_slot . ' - ' . $end_time_slot;
	
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
		
			if ($post_type === 'bms_entries') {
				?>
				<script>
					jQuery(document).ready(function($) {
						$('#titlediv').remove(); // Remove the title field
					});
				</script>
				<?php
			}
		}

		
	}

	add_action( 'plugins_loaded', function() {
		PB()->admin->action = new PB_Admin_Action;
	} );
}
