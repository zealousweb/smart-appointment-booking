<?php
/**
 * PB_Front_ActionV Class
 *
 * Handles the Frontend Actions.
 *
 * @package WordPress
 * @subpackage Plugin name
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PB_Front_ActionV' ) ){

	/**
	 *  The PB_Front_Action Class
	 */
	class PB_Front_ActionV {
		

		function __construct()  {
			add_action('wp_ajax_bms_front_save_post_meta', array( $this, 'bms_front_save_post_meta' ) );
			add_action('wp_ajax_nopriv_bms_front_save_post_meta', array( $this, 'bms_front_save_post_meta' ) );
			
			add_action( 'wp_enqueue_scripts',  array( $this, 'action__enqueue_styles' ));
			add_action( 'wp_enqueue_scripts', array( $this, 'action__wp_enqueue_scripts' ));
			
			//add shortcode
			add_shortcode('booking_form',array( $this, 'zealbms_get_booking_form' ));

			add_action( 'wp_enqueue_scripts', array( $this, 'action__wp_enqueue_scripts' ) );

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
		 * Action: wp_enqueue_scripts
		 *
		 * - enqueue script in front side
		 *
		 */
		function action__wp_enqueue_scripts() {
			
			wp_enqueue_script( PB_PREFIX . '_front_js', PB_URL . 'assets/js/front.min.js', array( 'jquery-core' ), PB_VERSION );


			wp_enqueue_script( PB_PREFIX . '_bookingform', PB_URL . 'assets/js/booking-form.js', array( 'jquery-core' ), PB_VERSION );

			wp_enqueue_script( PB_PREFIX . '_front_js', PB_URL . 'assets/js/front.min.js', array( 'jquery-core' ), PB_VERSION );
			wp_enqueue_script( 'bms_formio_full_min', PB_URL.'assets/js/formio.full.min.js', array( 'jquery' ), 1.1, false );
			wp_localize_script('bms_formio_full_min', 'myAjax', array(
				'ajaxurl' => admin_url('admin-ajax.php')
			));
			wp_enqueue_script( 'bms_bootstrap.min', PB_URL.'assets/js/bootstrap.min.js', array( 'jquery' ), 1.1, false );
			wp_enqueue_script( 'bms_jquery-3.7.0.slim.min', PB_URL.'assets/js/jquery-3.7.0.slim.min.js', array( 'jquery' ), 1.1, false );
			wp_enqueue_script( 'bms_jquery-3.7.0.min',PB_URL.'assets/js/jquery-3.7.0.min.js', array( 'jquery' ), 1.1, false );

		}

		function action__enqueue_styles() {
			
			wp_enqueue_style( 'bms_boostrap_min',PB_URL.'assets/css/bootstrap.min.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'bms_formio_full_min',PB_URL.'assets/css/formio.full.min.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'bms_font-awesomev1','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', array(), 1.1, 'all' );
				
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
		function zealbms_get_booking_form() {
			ob_start();
			?>
			<p id="clickajax">click</p>
			<?php
			return ob_get_clean();
		}

		function bms_front_save_post_meta() {
			echo "test";
			error_log('front_save_post_meta function called');
			wp_die();
		}
		

	}
	/**
	* Run plugins loaded
	*/
	add_action( 'plugins_loaded' , function() {
		PB()->front = new PB_Front_ActionV;
	} );

}
