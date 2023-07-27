<?php
/**
 * ZURCF7_Google_Signup Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @subpackage User Registration using Contact Form 7 PRO
 * @since 1.4
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Google Login PHP library

require_once( ZURCF7_DIR . '/inc/lib/google-library/src/Google_Client.php' );
require_once( ZURCF7_DIR . '/inc/lib/google-library/src/contrib/Google_Oauth2Service.php' );

//echo ZURCF7_DIR . '/inc/google-library/src/Google_Client.php';
if ( !class_exists( 'ZURCF7_Google_Signup' ) ) {

	/**
	 * The ZURCF7_Google_Signup class
	 */

	class ZURCF7_Google_Signup {


		function __construct() {
			
			// Google Signup Login Backend Tag
			add_action( 'wpcf7_admin_init', array( $this, 'action__wpcf7_admin_init' ), 15, 0 );

			// Google signup Login Frontend Show
			add_action( 'wpcf7_init', array( $this, 'action__wpcf7_init' ), 10, 0 );

			add_action( 'init', array($this,'save_googlesignup_data' ));
		}

		/**
		 * password generate
		 *
		 * @param [type] Google Signup Login Backend Tag
		 * @return void
		 */

		function password_generate($chars) {
			$data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
			return substr(str_shuffle($data), 0, $chars);
		}
		/**
		 * Google Signup Login Backend Tag
		 *
		 * @param [type] Google Signup Login Backend Tag
		 * @return void
		 */
		function action__wpcf7_admin_init() {
			$tag_generator = WPCF7_TagGenerator::get_instance();
			$tag_generator->add(
				'googlesignup',
				__( 'Google Signup', 'google-sign-form' ),
				array( $this, 'wpcf7_tag_generator_google_sign' )
			);
		}
		/**
		 * Google Signup Login Backend Tag callback funcation
		 *
		 * @param [type] 
		 * @return void
		 */

		function wpcf7_tag_generator_google_sign( $contact_form, $args = '' ) {

			$args = wp_parse_args( $args, array() );
			$type = $args['id'];
			$description = __( "Generate a form-tag for to display Google Signup form", 'google-sign-form' ); ?>
			
			<div class="control-box">
				<fieldset>
					<legend><?php echo esc_html( $description ); ?></legend>
					<table class="form-table">
						<tbody>
							<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'google-sign-form' ) ); ?></label></th>
							<td>
								<legend class="screen-reader-text"><input type="checkbox" name="required" value="on" checked="checked" /></legend>
								<input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</div>
			<div class="insert-box">
				<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />
					<div class="submitbox">
						<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'google-sign-form' ) ); ?>" />
					</div>
				<br class="clear" />
			</div>
			<?php

		}

		/**
		 * Google Signup Login Backend Tag
		 *
		 * @param [type] Google Signup Login Backend Tag
		 * @return void
		 */
		function action__wpcf7_init() {
			wpcf7_add_form_tag(
				array( 'googlesignup', 'googlesignup*' ),
				array( $this, 'wpcf7_add_form_tag_google_signup' ),
				array( 'name-attr' => true )
			);
		}

		/**
		 * Google signup Login Frontend callback funcation
		 *
		 * @param [type] google singup form.
		 * @return void
		 */

		function wpcf7_add_form_tag_google_signup( $tag ) {
			$current_form_id = $form_id = $output = '' ;
			$current_form_id = WPCF7_ContactForm::get_current();
			$form_id = $current_form_id->id;
			$google_signup_app_id = get_option('google_signup_app_id');
			$google_app_secret = get_option('google_app_secret');
			$callback_google = '?socialsignup=google';
            $site_url_callback_google = get_site_url().$callback_google;
			$google_object = new Google_Client();
			$google_object->setClientId($google_signup_app_id);
			$google_object->setClientSecret($google_app_secret);
			$google_object->setRedirectUri($site_url_callback_google);
			$google_oauthV2 = new Google_Oauth2Service($google_object);
			$google_object->getAccessToken(); 
			$params = $form_id;
			$google_object->setState($params);
			$authUrl = $google_object->createAuthUrl();
			
			$google_signup = wp_get_attachment_image_url( get_option('google_signup_logo'),'medium' ); 
			
			//.'&current_form_id='.$form_id
			
			if (isset($_SERVER['HTTPS'])) {
				if(!empty($google_signup)) {
					$output = '<a href="'.filter_var($authUrl, FILTER_SANITIZE_URL).'"><img src="'.$google_signup.'" alt=""/> </a>';
				}else{
					$output = '<a href="'.filter_var($authUrl, FILTER_SANITIZE_URL).'"> <img src="'.ZURCF7_URL.'/assets/images/google.png" alt=""/> </a>';
				}
			}
			
			return $output; 
		}

		

		/**
		 * Save Data Google Drive
		 *
		 * @param [type] Save Data Google Drive.
		 * @return void
		 */

		function save_googlesignup_data() {

			if ( ! isset( $_REQUEST['socialsignup'] ) || 'google' == $_REQUEST['socialsignup'] ){
				global $wpdb;
				$current_form_id = '';
				if(isset($_GET['state'])){
					$current_form_id = $_GET['state'];
				}
				$site_url = get_site_url();
				$zurcf7_successurl_field = (get_post_meta($current_form_id,'zurcf7_successurl_field',true)) ? get_post_meta($current_form_id,'zurcf7_successurl_field',true) : get_post_meta($current_form_id,'zurcf7_successurl_field',"");
				$booking_url = get_permalink($zurcf7_successurl_field);
				$form_title = get_the_title($current_form_id);

				$zurcf7_userrole_field = (get_post_meta($current_form_id,'zurcf7_userrole_field',true)) ? get_post_meta($current_form_id,'zurcf7_userrole_field',true) : get_post_meta($current_form_id,'zurcf7_userrole_field',"");
				$zurcf7_auto_login = (get_post_meta($current_form_id,'zurcf7_auto_login',true)) ? get_post_meta($current_form_id,'zurcf7_auto_login',true) : get_post_meta($current_form_id,'zurcf7_auto_login',"");
				$zurcf7_biographic_field = (get_post_meta($current_form_id, 'zurcf7_biographic_field',true)) ? get_post_meta($current_form_id,'zurcf7_biographic_field',true) : get_post_meta($current_form_id,'zurcf7_biographic_field',"");
				$zurcf7_user_approval = (get_post_meta($current_form_id,'zurcf7_user_approval',true)) ? get_post_meta($current_form_id,'zurcf7_user_approval',true) : get_post_meta($current_form_id,'zurcf7_user_approval',"");
				$zurcf7_form_title = (get_post_meta($current_form_id,'zurcf7_form_title',true)) ? get_post_meta($current_form_id,'zurcf7_form_title',true) : get_post_meta($current_form_id,'zurcf7_form_title',"");
				$zurcf7_enable_password_field = (get_post_meta($current_form_id,'zurcf7_enable_password_field',true)) ? get_post_meta($current_form_id,'zurcf7_enable_password_field',true) : get_post_meta($current_form_id,'zurcf7_enable_password_field',"");
				$zurcf7_skipcf7_email = get_post_meta($current_form_id,'zurcf7_skipcf7_email',true);
				$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
				$login_url = get_site_url();
				
				$google_signup_app_id = get_option('google_signup_app_id');
				$google_app_secret = get_option('google_app_secret');
				$callback_google = '?socialsignup=google';
				$site_url_callback_google = $site_url.$callback_google;
				$google_object = new Google_Client();
				$google_object->setClientId($google_signup_app_id);
				$google_object->setClientSecret($google_app_secret);
				$google_object->setRedirectUri($site_url_callback_google);

				$google_oauthV2 = new Google_Oauth2Service($google_object);
				if(isset($_GET['code'])){ 
					$google_object->authenticate($_GET['code']); 
					$_SESSION['token'] = $google_object->getAccessToken();
					$booking_base_url = (!empty($booking_url)) ? $booking_url : $site_url;
					wp_redirect($booking_base_url);
				}
				if(isset($_SESSION['token'])){ 
					$google_object->setAccessToken($_SESSION['token']); 
				}

				if($google_object->getAccessToken()) { 

					$generate_password = $this->password_generate(7);
					$google_profile_data = $google_oauthV2->userinfo->get(); 

					$google_id = $google_profile_data['id'];
					$google_name = $google_profile_data['given_name'];
					$google_family_name = $google_profile_data['family_name'];
					$google_email = $google_profile_data['email'];
					$google_gender = $google_profile_data['gender'];
					$google_locale = $google_profile_data['locale'];
					$google_profile = $google_profile_data['picture'];

					$userdata = array(
						'user_login'	=>  wp_slash($google_email),
						'user_pass'		=>  $generate_password,
						'user_email'	=>  wp_slash($google_email),
						'first_name' => $google_name,
						'last_name' => $google_family_name,
						'role' => $zurcf7_userrole_field,
					);
					$user_id = wp_insert_user( $userdata );
					
					// Check if post already exist
					$query = $wpdb->prepare(
						'SELECT ID FROM ' . $wpdb->posts . '
						WHERE post_title = %s
						AND post_type = \''.ZURCF7_POST_TYPE.'\'',
						$google_email
					);
					$wpdb->query( $query );
					if ( $wpdb->num_rows == 0 ) {
						$zurcf_post_id_post = wp_insert_post( array (
							'post_type'      => ZURCF7_POST_TYPE,
							'post_title'     => $google_email, // email
							'post_status'    => 'publish',
							'comment_status' => 'closed',
							'ping_status'    => 'closed',
							'post_author'    => $user_id,
						) );
						
						//$user_pwd = 'test';
						add_post_meta( $zurcf_post_id_post, ZURCF7_META_PREFIX.'user_id', $user_id, true);
						add_post_meta( $zurcf_post_id_post, ZURCF7_META_PREFIX.'form_id', $zurcf7_formid, true);
						add_post_meta( $zurcf_post_id_post, ZURCF7_META_PREFIX.'form_title', $form_title, true);
						add_post_meta( $zurcf_post_id_post, ZURCF7_META_PREFIX.'user_login', wp_slash($google_email), true);
						add_post_meta( $zurcf_post_id_post, ZURCF7_META_PREFIX.'user_pass', wp_hash_password($generate_password), true);
						add_post_meta( $zurcf_post_id_post, ZURCF7_META_PREFIX.'user_email', wp_slash($google_email), true);
						add_post_meta( $zurcf_post_id_post, ZURCF7_META_PREFIX.'role', $zurcf7_userrole_field, true);
						
						
						if(($zurcf7_user_approval == 1) && ($zurcf7_auto_login != 1)) {
							add_post_meta( $zurcf_post_id_post, ZURCF7_META_PREFIX.'user_status', 0, true);
						} else {
							add_post_meta( $zurcf_post_id_post, ZURCF7_META_PREFIX.'user_status', 1, true);
						}

						add_post_meta( $zurcf_post_id_post,ZURCF7_META_PREFIX.'type', 'Google', true);
						add_user_meta( $user_id,ZURCF7_META_PREFIX.'type', 'Google', true);

						if($zurcf7_skipcf7_email == 1) { 
							
							$from = get_post_meta($current_form_id, 'zurcf7_admin_from_pass',true);
							$subject = get_post_meta($current_form_id, 'zurcf7_admin_subject_pass',true);
							$message = html_entity_decode(get_post_meta($current_form_id,'zurcf7_adminpassword_body',true));

							if(!empty($from)){
								$headers[] = $from;
							}if(!empty($subject)){
								$subject = get_post_meta($current_form_id, 'zurcf7_admin_subject_pass',true);
							}else{
								$subject = 'Create Your Password';
							}if(!empty($message)){
								$message = html_entity_decode(get_post_meta($current_form_id,'zurcf7_adminpassword_body',true));

								//[UserName]   [UserFirstname]   [UserLastname]  [UserEmail]   [UserPassword]   [Blogname]   [LoginUrl] 
								if (strpos($message, '[UserName]') !== false) {
									$message = str_replace('[UserName]', $google_email, $message);
								}
								if (strpos($message, '[UserFirstname]') !== false) {
									$message = str_replace('[UserFirstname]', $google_name, $message);
								}
								if (strpos($message, '[UserLastname]') !== false) {
									$message = str_replace('[UserLastname]', $google_family_name, $message);
								}
								if (strpos($message, '[UserEmail]') !== false) {
									$message = str_replace('[UserEmail]', $google_email, $message);
								}
								if (strpos($message, '[UserPassword]') !== false) {
									$message = str_replace('[UserPassword]', $generate_password, $message);
								}
								if (strpos($message, '[Blogname]') !== false) {
									$message = str_replace('[Blogname]', $blogname, $message);
								}
								if (strpos($message, '[LoginUrl]') !== false) {
									$message = str_replace('[LoginUrl]', $login_url, $message);
								}
								
							}else{

								$data = array('Name' => $google_name, 'passsword' => $generate_password, 'email' => $google_email);
								$options = array('http' => array('method' => 'POST','content' => http_build_query($data)));
								$stream = stream_context_create($options);
								$template = file_get_contents(ZURCF7_URL.'inc/admin/template/emailtemplates/signup-password-tmp/zurcf7.layout1.php', false,$stream);
								$headers[] = 'Content-type: text/html; charset=utf-8';
								$headers[] = 'From: '.get_bloginfo("name").' <'.get_bloginfo("admin_email").'>' . "\r\n";
								$message = $template;
							}
							wp_mail($google_email,$subject, $message, $headers);
						}
						send_notification($current_form_id);
					}
					$creds = array(
						'user_login'    => $google_email,
						'user_password' => $generate_password,
						'remember'      => true
					);
					$user = wp_signon( $creds, false );
					exit();	
				}
				//
			}
		}
		
		 /**
		 * Save google sign up form settings
		 */
		function save_google_signup_setting() {
			if (
			! wp_verify_nonce( $_POST['setting_save'], 'google_signup_setting_save' )
			) {
				echo '<div class="error">
				<p>' . __( 'Sorry, your nonce was not correct. Please try again.', 'google-signup' ) . '</p>
				</div>';
				exit;

			} else {

				$google_signup_app_id = sanitize_text_field( $_POST['google_signup_app_id'] );
				$google_app_secret = sanitize_text_field( $_POST['google_app_secret'] );
				$google_signup_logo = isset( $_POST['google_signup_logo'] ) ? sanitize_text_field( $_POST['google_signup_logo'] ) : '';

				if (! empty( $google_signup_app_id ) || ! empty( $google_app_secret ) || ! empty( $google_signup_logo )) {

					update_option( 'google_signup_app_id', $google_signup_app_id);
					update_option( 'google_app_secret', $google_app_secret);
					update_option( 'google_signup_logo', $google_signup_logo);

					if(!isset($_REQUEST['setting_zurcf7_submit'])) {
						echo '<div class="notice notice-success is-dismissible">
							<p>' . __( 'Fields update successfully.', 'google-signup-logo' ) . '</p>
						</div>';	
					}
				}
				else {
					if(!isset($_REQUEST['setting_zurcf7_submit'])) {
						echo '<div class="notice notice-error is-dismissible">
						<p>' . __( 'Fill all required fields.', 'google-signup' ) . '</p>
						</div>';
					}
				}	
			}
		}
		
	}
}
