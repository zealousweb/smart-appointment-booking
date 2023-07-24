<?php
/**
 * Plugin Name: WP Smart Appointment & Booking
 * Plugin URL: https://wordpress.org/plugins/wp-smart-appointment-booking/
 * Description: This is the all-in-one solution for efficient appointment management, offering customizable forms, seamless booking andmodifications waitlist management.
 * Version: 1.0
 * Author: ZealousWeb
 * Author URI: https://www.zealousweb.com
 * Developer: The Zealousweb Team
 * Developer E-Mail: support@zealousweb.com
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

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
// function sab_activate_plugin_name() {
// 	require_once plugin_dir_path( __FILE__ ) . '/inc/class.wp_sab.activator.php';
// 	Plugin_Name_Activator::activate();
// }

// /**
//  * The code that runs during plugin deactivation.
//  * This action is documented in includes/class-plugin-name-deactivator.php
//  */
// function sab_deactivate_plugin_name() {
// 	require_once plugin_dir_path( __FILE__ ) . '/inc/class.wp_sab.deactivator.php';
// 	Plugin_Name_Deactivator::deactivate();
// }

// register_activation_hook( __FILE__, 'sab_activate_plugin_name' );
// register_deactivation_hook( __FILE__, 'sab_deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
if ( ! function_exists( 'WP_SAB' ) ) {
	if ( is_admin() ) {
        require_once( WP_SAB_DIR . '/inc/admin/class.wp_sab.admin.action.php' );
        require_once( WP_SAB_DIR . '/inc/admin/class.wp_sab.admin.fieldmeta.php' );
        require_once( WP_SAB_DIR . '/inc/admin/class.wp_sab.admin.filter.php' );
    }else{
		require_once( WP_SAB_DIR . '/inc/front/class.wp_sab.front.filter.php' );
   	}
    
    require_once( WP_SAB_DIR . '/inc/front/class.wp_sab.front.action.php' );
    require_once( WP_SAB_DIR . '/inc/class.wp_sab.php' );
}
