<?php
/**
 * Admin menu registration.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Medsol_Admin_Menu
 */
class Medsol_Admin_Menu {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_menu_page(
			__( 'Medsol Appointments', 'medsol-appointments' ),
			__( 'Appointments', 'medsol-appointments' ),
			'manage_options',
			'medsol-appointments',
			array( $this, 'appointments_page' ),
			'dashicons-calendar-alt',
			30
		);

		add_submenu_page(
			'medsol-appointments',
			__( 'Appointments', 'medsol-appointments' ),
			__( 'Appointments', 'medsol-appointments' ),
			'manage_options',
			'medsol-appointments',
			array( $this, 'appointments_page' )
		);

		add_submenu_page(
			'medsol-appointments',
			__( 'Employees', 'medsol-appointments' ),
			__( 'Employees', 'medsol-appointments' ),
			'manage_options',
			'medsol-employees',
			array( $this, 'employees_page' )
		);

		add_submenu_page(
			'medsol-appointments',
			__( 'Services', 'medsol-appointments' ),
			__( 'Services', 'medsol-appointments' ),
			'manage_options',
			'medsol-services',
			array( $this, 'services_page' )
		);

		add_submenu_page(
			'medsol-appointments',
			__( 'Locations', 'medsol-appointments' ),
			__( 'Locations', 'medsol-appointments' ),
			'manage_options',
			'medsol-locations',
			array( $this, 'locations_page' )
		);

		add_submenu_page(
			'medsol-appointments',
			__( 'Notifications', 'medsol-appointments' ),
			__( 'Notifications', 'medsol-appointments' ),
			'manage_options',
			'medsol-notifications',
			array( $this, 'notifications_page' )
		);

		add_submenu_page(
			'medsol-appointments',
			__( 'Settings', 'medsol-appointments' ),
			__( 'Settings', 'medsol-appointments' ),
			'manage_options',
			'medsol-settings',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Appointments page callback.
	 */
	public function appointments_page() {
		require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-appointments.php';
		medsol_appointments_page();
	}

	/**
	 * Employees page callback.
	 */
	public function employees_page() {
		require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-employees.php';
		medsol_employees_page();
	}

	/**
	 * Services page callback.
	 */
	public function services_page() {
		require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-services.php';
		medsol_services_page();
	}

	/**
	 * Locations page callback.
	 */
	public function locations_page() {
		require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-locations.php';
		medsol_locations_page();
	}

	/**
	 * Notifications page callback.
	 */
	public function notifications_page() {
		require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-notifications.php';
		medsol_notifications_page();
	}

	/**
	 * Settings page callback.
	 */
	public function settings_page() {
		require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-settings.php';
		medsol_settings_page();
	}
}