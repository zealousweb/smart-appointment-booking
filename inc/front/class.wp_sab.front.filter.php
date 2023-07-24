<?php
/**
 * WP_SAB_Front_Filter Class
 *
 * Handles the Frontend Filters.
 *
 * @package WordPress
 * @subpackage Plugin name
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_SAB_Front_Filter' ) ) {

    /**
     *  The WP_SAB_Front_Filter Class
     */
    class WP_SAB_Front_Filter {

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
		$WP_SAB_Front_Filter = new WP_SAB_Front_Filter();
	} );

}
