<?php
/**
 * Appointment controller.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Medsol_Appointment_Controller
 */
class Medsol_Appointment_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_medsol_get_appointment_modal', array( $this, 'get_modal' ) );
		add_action( 'wp_ajax_medsol_save_appointment', array( $this, 'save' ) );
	}

	/**
	 * Get modal content via AJAX.
	 */
	public function get_modal() {
		check_ajax_referer( 'medsol_appointments_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'medsol-appointments' ) );
		}

		$id = absint( $_POST['id'] ?? 0 );
		$appointment = $id ? Medsol_Appointment::get( $id ) : null;

		ob_start();
		include MEDSOL_APPOINTMENTS_PATH . 'templates/admin/modal-appointment.php';
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Save appointment via AJAX.
	 */
	public function save() {
		check_ajax_referer( 'medsol_appointments_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'medsol-appointments' ) );
		}

		$data = array(
			'customer_name'  => sanitize_text_field( $_POST['customer_name'] ),
			'customer_email' => sanitize_email( $_POST['customer_email'] ),
			'customer_phone' => sanitize_text_field( $_POST['customer_phone'] ),
			'note'           => sanitize_textarea_field( $_POST['note'] ),
			'employee_id'    => absint( $_POST['employee_id'] ),
			'service_id'     => absint( $_POST['service_id'] ),
			'location_id'    => absint( $_POST['location_id'] ),
			'date'           => sanitize_text_field( $_POST['date'] ),
			'time'           => sanitize_text_field( $_POST['time'] ),
			'duration'       => absint( $_POST['duration'] ),
			'status'         => sanitize_text_field( $_POST['status'] ),
		);

		$id = absint( $_POST['id'] ?? 0 );

		if ( $id ) {
			$updated = Medsol_Appointment::update( $id, $data );
			if ( $updated ) {
				wp_send_json_success( array( 'message' => __( 'Appointment updated.', 'medsol-appointments' ) ) );
			} else {
				wp_send_json_error( __( 'Failed to update appointment.', 'medsol-appointments' ) );
			}
		} else {
			$new_id = Medsol_Appointment::create( $data );
			if ( $new_id && ! is_wp_error( $new_id ) ) {
				wp_send_json_success( array( 'message' => __( 'Appointment created.', 'medsol-appointments' ) ) );
			} else {
				wp_send_json_error( $new_id->get_error_message() ?? __( 'Failed to create appointment.', 'medsol-appointments' ) );
			}
		}
	}
}