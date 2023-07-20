<?php
/**
 * Plugin Name: WP Smart Appointment & Booking
 * Plugin URL: https://wordpress.org/plugins/wp-smart-appointment-booking/
 * Description: This is the all-in-one solution for efficient appointment management, offering customizable forms, seamless booking andmodifications waitlist management.
 * Version: 1.0
 * Author: ZealousWeb
 * Author URI: https://www.zealousweb.com
 * Developer: The Zealousweb Team
 * Developer E-Mail: opensource@zealousweb.com
 * Text Domain: wp-smart-appointment-booking
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

if ( ! defined( 'WP_SAB_VERSION' ) ) {
	define( 'WP_SAB_VERSION', '1.0' ); // Version of the plugin
}

if ( ! defined( 'WP_SAB_FILE' ) ) {
	define( 'WP_SAB_FILE', __FILE__ ); // Plugin File
}

if ( ! defined( 'WP_SAB_DIR' ) ) {
	define( 'WP_SAB_DIR', dirname( __FILE__ ) ); // Plugin directory path
}

if ( ! defined( 'WP_SAB_URL' ) ) {
	define( 'WP_SAB_URL', plugin_dir_url( __FILE__ ) ); // Plugin URL
}

if ( ! defined( 'WP_SAB_PLUGIN_BASENAME' ) ) {
	define( 'WP_SAB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // Plugin base name
}

if ( ! defined( 'WP_SAB_META_PREFIX' ) ) {
	define( 'WP_SAB_META_PREFIX', 'wp_sab_' ); // Plugin metabox prefix
}

if ( ! defined( 'WP_SAB_PREFIX' ) ) {
	define( 'WP_SAB_PREFIX', 'wp_sab' ); // Plugin prefix
}

/**
 * Initialize the main class
 */
// if ( ! function_exists( 'WP_SAB' ) ) {
// 	if ( is_admin() ) {
// 		require_once( WP_SAB_DIR . '/inc/admin/class.' . WP_SAB_PREFIX . '.admin.php' );
// 		require_once( WP_SAB_DIR . '/inc/admin/class.' . WP_SAB_PREFIX . '.admin.action.php' );
// 		require_once( WP_SAB_DIR . '/inc/admin/class.' . WP_SAB_PREFIX . '.admin.fieldmeta.php' );
// 		require_once( WP_SAB_DIR . '/inc/admin/class.' . WP_SAB_PREFIX . '.admin.filter.php' );
// 	} else {
// 		require_once( WP_SAB_DIR . '/inc/front/class.' . WP_SAB_PREFIX . '.front.php' );
// 		require_once( WP_SAB_DIR . '/inc/front/class.' . WP_SAB_PREFIX . '.front.filter.php' );
// 	}
// 	require_once( WP_SAB_DIR . '/inc/front/class.' . WP_SAB_PREFIX . '.front.action.php' );
// 	require_once( WP_SAB_DIR . '/inc/lib/class.' . WP_SAB_PREFIX . '.lib.php' );

// 	// Initialize all the things.
// 	require_once( WP_SAB_DIR . '/inc/class.' . WP_SAB_PREFIX . '.php' );
// }
// if ( !defined( 'PB_VERSION' ) ) {
// 	define( 'PB_VERSION', '1.0' ); // Version of plugin
// }

// if ( !defined( 'PB_FILE' ) ) {
// 	define( 'PB_FILE', __FILE__ ); // Plugin File
// }

// if ( !defined( 'PB_DIR' ) ) {
// 	define( 'PB_DIR', dirname( __FILE__ ) ); // Plugin dir
// }

// if ( !defined( 'PB_URL' ) ) {
// 	define( 'PB_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
// }

// if ( !defined( 'PB_PLUGIN_BASENAME' ) ) {
// 	define( 'PB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // Plugin base name
// }

// if ( !defined( 'PB_META_PREFIX' ) ) {
// 	define( 'PB_META_PREFIX', 'pb_' ); // Plugin metabox prefix
// }

// if ( !defined( 'PB_PREFIX' ) ) {
// 	define( 'PB_PREFIX', 'pb' ); // Plugin prefix
// }

/**
 * Initialize the main class
 */
if ( !function_exists( 'PB' ) ) {

	if( is_admin()) {
		require_once( PB_DIR . '/inc/admin/class.' . PB_PREFIX . '.admin.php' );
		require_once( PB_DIR . '/inc/admin/class.' . PB_PREFIX . '.admin.action.php' );
		require_once( PB_DIR . '/inc/admin/class.' . PB_PREFIX . '.admin.fieldmeta.php' );
		require_once( PB_DIR . '/inc/admin/class.' . PB_PREFIX . '.admin.filter.php' );
	}else{
		require_once( PB_DIR . '/inc/front/class.' . PB_PREFIX . '.front.php' );
		
		require_once( PB_DIR . '/inc/front/class.' . PB_PREFIX . '.front.filter.php' );
	}
	require_once( PB_DIR . '/inc/front/class.' . PB_PREFIX . '.front.action.php' );
	require_once( PB_DIR . '/inc/lib/class.' . PB_PREFIX . '.lib.php' );

	//Initialize all the things.
	require_once( PB_DIR . '/inc/class.' . PB_PREFIX . '.php' );
}
