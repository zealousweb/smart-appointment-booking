<?php
/**
 * Plugin Name: Smart Appointment & Booking
 * Plugin URL: https://wordpress.org/plugins/smart-appointment-booking/
 * Description: This is the all-in-one solution for efficient appointment management, offering customizable forms, seamless booking and modifications waitlist management.
 * Version: 1.0.5
 * Author: ZealousWeb
 * Author URI: https://www.zealousweb.com
 * Developer: The Zealousweb Team
 * Developer E-Mail: support@zealousweb.com
 * Text Domain: smart-appointment-booking
 * Domain Path: /languages
 *
 * Copyright: © 2009-2023 ZealousWeb Technologies.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions
 *
 * @package Smart Appointment & Booking
 * @since 1.0.2
 */

if ( ! defined( 'SAAB_VERSION' ) ) {
	define( 'SAAB_VERSION', '1.0.5' ); // Version of the plugin
}

if ( ! defined( 'SAAB_FILE' ) ) {
	define( 'SAAB_FILE', __FILE__ ); // Plugin File
}

if ( ! defined( 'SAAB_DIR' ) ) {
	define( 'SAAB_DIR', dirname( __FILE__ ) ); // Plugin directory path
}

if ( ! defined( 'SAAB_URL' ) ) {
	define( 'SAAB_URL', plugin_dir_url( __FILE__ ) ); // Plugin URL
}

if ( ! defined( 'SAAB_PLUGIN_BASENAME' ) ) {
	define( 'SAAB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // Plugin base name
}

if ( ! defined( 'SAAB_META_PREFIX' ) ) {
	define( 'SAAB_META_PREFIX', 'saab_' ); // Plugin metabox prefix
}

if ( ! defined( 'SAAB_PREFIX' ) ) {
	define( 'SAAB_PREFIX', 'saab' ); // Plugin prefix
}

if ( ! function_exists( 'SAAB' ) ) {
	if ( is_admin() ) {
        require_once( SAAB_DIR . '/inc/admin/class.saab.admin.action.php' );
        require_once( SAAB_DIR . '/inc/admin/class.saab.admin.fieldmeta.php' );
        require_once( SAAB_DIR . '/inc/admin/class.saab.admin.filter.php' );
    }else{
		require_once( SAAB_DIR . '/inc/front/class.saab.front.filter.php' );
   	}
    
    require_once( SAAB_DIR . '/inc/front/class.saab.front.action.php' );
    require_once( SAAB_DIR . '/inc/class.saab.php' );
}

