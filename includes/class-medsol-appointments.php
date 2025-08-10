<?php
/**
 * Core plugin class.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Medsol_Appointments class.
 */
class Medsol_Appointments {

	/**
	 * The single instance of the class.
	 *
	 * @var Medsol_Appointments
	 */
	protected static $instance = null;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		// Load includes.
		$this->includes();

		// Initialize hooks.
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );	
		new Medsol_Appointment_Controller();
			new Medsol_Employee_Controller();
			new Medsol_Service_Controller();
			new Medsol_Location_Controller();	
	}

	/**
	 * Register admin menu.
	 */
	public function register_admin_menu() {
		new Medsol_Admin_Menu();
	}

	/**
	 * Get the plugin instance.
	 *
	 * @return Medsol_Appointments
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Include required files.
	 */
	private function includes() {
	// Models.
	require_once MEDSOL_APPOINTMENTS_PATH . 'includes/models/class-appointment.php';
	require_once MEDSOL_APPOINTMENTS_PATH . 'includes/models/class-employee.php';
	require_once MEDSOL_APPOINTMENTS_PATH . 'includes/models/class-service.php';
	require_once MEDSOL_APPOINTMENTS_PATH . 'includes/models/class-location.php';
		if ( is_admin() ) {
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/class-admin-menu.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/class-appointment-list-table.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/class-employee-list-table.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/class-service-list-table.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/class-location-list-table.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-appointments.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-employees.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-services.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-locations.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-notifications.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/admin/pages/page-settings.php';
		}
			// Controllers.
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/controllers/class-appointment-controller.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/controllers/class-employee-controller.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/controllers/class-service-controller.php';
			require_once MEDSOL_APPOINTMENTS_PATH . 'includes/controllers/class-location-controller.php';

				// Controllers for notifications/settings (if needed; here using direct settings API).
	}

	/**
	 * Init hook.
	 */
	public function init() {
		// Load textdomain for translations.
		load_plugin_textdomain( 'medsol-appointments', false, dirname( MEDSOL_APPOINTMENTS_BASENAME ) . '/languages' );
	}

	/**
	 * Enqueue admin assets (CSS/JS) only on plugin pages.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Enqueue if hook contains 'medsol-' (covers medsol-appointments, medsol-employees, etc.).
		if ( strpos( $hook, 'medsol-' ) === false ) {
			return;
		}
		wp_enqueue_style( 'medsol-appointments-admin', MEDSOL_APPOINTMENTS_URL . 'assets/css/admin.css', array(), MEDSOL_APPOINTMENTS_VERSION );
		wp_enqueue_script( 'medsol-appointments-admin', MEDSOL_APPOINTMENTS_URL . 'assets/js/admin.js', array( 'jquery' ), MEDSOL_APPOINTMENTS_VERSION, true );

		// Localize script with AJAX URL and nonce (for security).
		wp_localize_script( 'medsol-appointments-admin', 'medsolAppointments', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'medsol_appointments_nonce' ),
		) );
	}

	/**
	 * Activation hook: Create database tables and set version.
	 */
	public function activate() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// Appointments table.
		$sql = "CREATE TABLE {$wpdb->prefix}medsol_appointments (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			customer_name VARCHAR(255) NOT NULL,
			customer_email VARCHAR(255) NOT NULL,
			customer_phone VARCHAR(50) DEFAULT NULL,
			note TEXT DEFAULT NULL,
			employee_id BIGINT UNSIGNED NOT NULL,
			service_id BIGINT UNSIGNED NOT NULL,
			location_id BIGINT UNSIGNED NOT NULL,
			date DATE NOT NULL,
			time TIME NOT NULL,
			duration INT NOT NULL,
			status ENUM('pending', 'approved', 'declined', 'canceled') DEFAULT 'pending',
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY employee_id (employee_id),
			KEY service_id (service_id),
			KEY location_id (location_id),
			KEY date (date)
		) $charset_collate;";
		dbDelta( $sql );

		// Employees table.
		$sql = "CREATE TABLE {$wpdb->prefix}medsol_employees (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			first_name VARCHAR(255) NOT NULL,
			last_name VARCHAR(255) NOT NULL,
			email VARCHAR(255) NOT NULL,
			phone VARCHAR(50) DEFAULT NULL,
			role VARCHAR(100) DEFAULT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Employee days off table.
		$sql = "CREATE TABLE {$wpdb->prefix}medsol_employee_days_off (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			employee_id BIGINT UNSIGNED NOT NULL,
			reason VARCHAR(255) DEFAULT NULL,
			start_date DATE NOT NULL,
			end_date DATE NOT NULL,
			PRIMARY KEY (id),
			KEY employee_id (employee_id)
		) $charset_collate;";
		dbDelta( $sql );

		// Services table.
		$sql = "CREATE TABLE {$wpdb->prefix}medsol_services (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			duration INT NOT NULL,
			slot_capacity INT DEFAULT 0,
			min_booking_time INT DEFAULT 0,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Locations table.
		$sql = "CREATE TABLE {$wpdb->prefix}medsol_locations (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			address TEXT DEFAULT NULL,
			phone VARCHAR(50) DEFAULT NULL,
			min_booking_time INT DEFAULT 0,
			weekly_availability JSON DEFAULT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Location days off table.
		$sql = "CREATE TABLE {$wpdb->prefix}medsol_location_days_off (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			location_id BIGINT UNSIGNED NOT NULL,
			reason VARCHAR(255) DEFAULT NULL,
			start_date DATE NOT NULL,
			end_date DATE NOT NULL,
			PRIMARY KEY (id),
			KEY location_id (location_id)
		) $charset_collate;";
		dbDelta( $sql );

		// Set installed version.
		update_option( 'medsol_appointments_version', MEDSOL_APPOINTMENTS_VERSION );
	}

	/**
	 * Deactivation hook: Currently no-op.
	 */
	public function deactivate() {
		// Placeholder for future cleanup if needed.
	}
}