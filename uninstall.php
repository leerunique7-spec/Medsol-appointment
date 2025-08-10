<?php
/**
 * Medsol Appointments Uninstall
 *
 * Uninstalling Medsol Appointments deletes all data if option is set.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Check if the erase data option is enabled (added in settings later).
$settings = get_option( 'medsol_appointments_settings', array() );
$erase_data = $settings['erase_on_uninstall'] ?? false;

if ( $erase_data ) {
	// Drop custom tables.
	$tables = array(
		'medsol_appointments',
		'medsol_employees',
		'medsol_employee_days_off',
		'medsol_services',
		'medsol_locations',
		'medsol_location_days_off',
	);
	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	// Delete options.
	delete_option( 'medsol_appointments_version' ); 
	delete_option( 'medsol_appointments_settings' ); 
	delete_option( 'medsol_appointments_notifications' ); 

	// Transients (cached data; optional but good for cleanup).
	delete_transient( 'medsol_employees_all' );
	delete_transient( 'medsol_services_all' );
	delete_transient( 'medsol_locations_all' );

	// Any future options: Add here if you extend the plugin (e.g., delete_option( 'medsol_custom_option' ); ).
}