<?php
/**
 * Plugin Name: Medsol Appointments
 * Plugin URI: https://example.com/medsol-appointments
 * Description: A custom WordPress plugin for managing appointments, employees, services, locations, and notifications.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL-2.0-or-later
 * Text Domain: medsol-appointments
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

// Define plugin constants.
define( 'MEDSOL_APPOINTMENTS_VERSION', '1.0.0' );
define( 'MEDSOL_APPOINTMENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'MEDSOL_APPOINTMENTS_URL', plugin_dir_url( __FILE__ ) );
define( 'MEDSOL_APPOINTMENTS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Get supported shortcodes for notifications.
 *
 * @return array
 */
function medsol_appointments_shortcodes() {
	return array(
		'%customer_name%',
		'%customer_email%',
		'%customer_phone%',
		'%appointment_date%',
		'%appointment_time%',
		'%appointment_status%',
		'%employee_name%',
		'%service_name%',
		'%location_name%',
		'%note%',
	);
}

// Load core class.
require_once MEDSOL_APPOINTMENTS_PATH . 'includes/class-medsol-appointments.php';

/**
 * Returns the main instance of Medsol_Appointments.
 *
 * @return Medsol_Appointments
 */
function medsol_appointments() {
	return Medsol_Appointments::instance();
}

// Global for backwards compatibility.
$GLOBALS['medsol_appointments'] = medsol_appointments();

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, array( medsol_appointments(), 'activate' ) );
register_deactivation_hook( __FILE__, array( medsol_appointments(), 'deactivate' ) );