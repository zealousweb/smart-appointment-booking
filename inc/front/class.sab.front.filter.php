<?php
/**
 * SAB_Front_Filter Class
 *
 * Handles the Frontend Filters.
 *
 * @package WordPress
 * @subpackage Smart Appointment & Booking
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'SAB_Front_Filter' ) ) {

    /**
     *  The SAB_Front_Filter Class
     */
    class SAB_Front_Filter {

        public function __construct() {
            // Add your constructor code here, if needed
        }

        /*
        ######## #### ##       ######## ######## ########   ######
        ##        ##  ##          ##    ##       ##     ## ##    ##
        ##        ##  ##          ##    ##       ##     ## ##
        ######    ##  ##          ##    ######   ########   ######
        ##        ##  ##          ##    ##       ##   ##         ##
        ##        ##  ##          ##    ##       ##    ##  ##    ##
        ##       #### ########    ##    ######## ##     ##  ######
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

    }
	add_action( 'plugins_loaded', function() {
		$SAB_Front_Filter = new SAB_Front_Filter();
	} );

}
