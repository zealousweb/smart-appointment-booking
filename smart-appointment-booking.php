<?php
/**
 * Plugin Name: Smart Appointment & Booking Pro
 * Plugin URL: https://www.zealousweb.com/store/smart-appointment-booking-pro/
 * Description: This is the all-in-one solution for efficient appointment management, offering customizable forms, seamless booking andmodifications waitlist management.
 * Version: 1.0
 * Author: ZealousWeb
 * Author URI: https://www.zealousweb.com
 * Developer: The Zealousweb Team
 * Developer E-Mail: support@zealousweb.com
 * Text Domain: smart-appointment-booking-pro
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
 * @package Plugin name
 * @since 1.0
 */

if ( ! defined( 'SAB_VERSION' ) ) {
	define( 'SAB_VERSION', '1.0' ); // Version of the plugin
}

if ( ! defined( 'SAB_FILE' ) ) {
	define( 'SAB_FILE', __FILE__ ); // Plugin File
}

if ( ! defined( 'SAB_DIR' ) ) {
	define( 'SAB_DIR', dirname( __FILE__ ) ); // Plugin directory path
}

if ( ! defined( 'SAB_URL' ) ) {
	define( 'SAB_URL', plugin_dir_url( __FILE__ ) ); // Plugin URL
}

if ( ! defined( 'SAB_PLUGIN_BASENAME' ) ) {
	define( 'SAB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // Plugin base name
}

if ( ! defined( 'SAB_META_PREFIX' ) ) {
	define( 'SAB_META_PREFIX', 'sab_' ); // Plugin metabox prefix
}

if ( ! defined( 'SAB_PREFIX' ) ) {
	define( 'SAB_PREFIX', 'sab' ); // Plugin prefix
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
if ( ! function_exists( 'SAB' ) ) {
	if ( is_admin() ) {
        require_once( SAB_DIR . '/inc/admin/class.sab.admin.action.php' );
        require_once( SAB_DIR . '/inc/admin/class.sab.admin.fieldmeta.php' );
        require_once( SAB_DIR . '/inc/admin/class.sab.admin.filter.php' );
    }else{
		require_once( SAB_DIR . '/inc/front/class.sab.front.filter.php' );
   	}
    
    require_once( SAB_DIR . '/inc/front/class.sab.front.action.php' );
    require_once( SAB_DIR . '/inc/class.sab.php' );
}
