<?php
/**
 * Service controller.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Medsol_Service_Controller
 */
class Medsol_Service_Controller {

	public function __construct() {
		add_action( 'wp_ajax_medsol_get_service_modal', array( $this, 'get_modal' ) );
		add_action( 'wp_ajax_medsol_save_service', array( $this, 'save' ) );
	}

	public function get_modal() {
		check_ajax_referer( 'medsol_appointments_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'medsol-appointments' ) );
		}

		$id = absint( $_POST['id'] ?? 0 );
		$service = $id ? Medsol_Service::get( $id ) : null;

		ob_start();
		include MEDSOL_APPOINTMENTS_PATH . 'templates/admin/modal-service.php';
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	public function save() {
		check_ajax_referer( 'medsol_appointments_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'medsol-appointments' ) );
		}

		$data = array(
			'name'             => sanitize_text_field( $_POST['name'] ?? '' ),
			'duration'         => absint( $_POST['duration'] ?? 0 ),
			'slot_capacity'    => absint( $_POST['slot_capacity'] ?? 0 ),
			'min_booking_time' => absint( $_POST['min_booking_time'] ?? 0 ),
		);

		$id = absint( $_POST['id'] ?? 0 );

		global $wpdb;

		if ( $id ) {
			$updated = Medsol_Service::update( $id, $data );
			if ( $updated === false && $wpdb->last_error ) { // Allow 0 as success (no changes).
				wp_send_json_error( __( 'Failed to update service.', 'medsol-appointments' ) );
			}
		} else {
			$id = Medsol_Service::create( $data );
			if ( ! $id || is_wp_error( $id ) ) {
				wp_send_json_error( __( 'Failed to create service.', 'medsol-appointments' ) );
			}
		}

		do_action( 'medsol_after_save_service', $id );

		if (ob_get_level() > 0) ob_clean(); // Conditional clean to avoid notice.
		wp_send_json_success( array( 'message' => __( 'Service saved.', 'medsol-appointments' ) ) );
	}
}