<?php
/**
 * Employee controller.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Medsol_Employee_Controller
 */
class Medsol_Employee_Controller {

	public function __construct() {
		add_action( 'wp_ajax_medsol_get_employee_modal', array( $this, 'get_modal' ) );
		add_action( 'wp_ajax_medsol_save_employee', array( $this, 'save' ) );
		add_action( 'wp_ajax_medsol_delete_employee_day_off', array( $this, 'delete_day_off' ) );
	}

	public function get_modal() {
		check_ajax_referer( 'medsol_appointments_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'medsol-appointments' ) );
		}

		$id = absint( $_POST['id'] ?? 0 );
		$employee = $id ? Medsol_Employee::get( $id ) : null;

		ob_start();
		include MEDSOL_APPOINTMENTS_PATH . 'templates/admin/modal-employee.php';
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	public function save() {
		check_ajax_referer( 'medsol_appointments_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'medsol-appointments' ) );
		}

		$data = array(
			'first_name' => sanitize_text_field( $_POST['first_name'] ?? '' ),
			'last_name'  => sanitize_text_field( $_POST['last_name'] ?? '' ),
			'email'      => sanitize_email( $_POST['email'] ?? '' ),
			'phone'      => sanitize_text_field( $_POST['phone'] ?? '' ),
			'role'       => sanitize_text_field( $_POST['role'] ?? '' ),
		);

		$id = absint( $_POST['id'] ?? 0 );

		global $wpdb;

		if ( $id ) {
			$updated = Medsol_Employee::update( $id, $data );
			if ( $updated === false && $wpdb->last_error ) {
				wp_send_json_error( __( 'Failed to update employee details.', 'medsol-appointments' ) );
			}
		} else {
			$id = Medsol_Employee::create( $data );
			if ( ! $id || is_wp_error( $id ) ) {
				wp_send_json_error( __( 'Failed to create employee.', 'medsol-appointments' ) );
			}
		}

		// Handle days off without deleting all.
		$days_off = $_POST['days_off'] ?? array();
		$existing_days = Medsol_Employee::get_days_off( $id );
		$existing_ids = array_column( $existing_days, 'id' );
		$submitted_ids = array();

		foreach ( $days_off as $day ) {
			$day_id = absint( $day['id'] ?? 0 );
			$day_data = array(
				'reason'     => sanitize_text_field( $day['reason'] ?? '' ),
				'start_date' => sanitize_text_field( $day['start_date'] ?? '' ),
				'end_date'   => sanitize_text_field( $day['end_date'] ?? '' ),
			);

			if ( empty( $day_data['start_date'] ) || empty( $day_data['end_date'] ) ) {
				continue; // Skip invalid.
			}

			if ( $day_id ) {
				// Update existing.
				$wpdb->update(
					$wpdb->prefix . 'medsol_employee_days_off',
					$day_data,
					array( 'id' => $day_id, 'employee_id' => $id ),
					array( '%s', '%s', '%s' ),
					array( '%d', '%d' )
				);
				$submitted_ids[] = $day_id;
			} else {
				// Add new.
				Medsol_Employee::add_day_off( $id, $day_data );
			}
		}

		// Delete removed days off.
		$to_delete = array_diff( $existing_ids, $submitted_ids );
		foreach ( $to_delete as $delete_id ) {
			Medsol_Employee::delete_day_off( $delete_id );
		}

		do_action( 'medsol_after_save_employee', $id );

		if (ob_get_level() > 0) ob_clean(); // Conditional clean to avoid notice.
		wp_send_json_success( array( 'message' => __( 'Employee saved.', 'medsol-appointments' ) ) );
	}

	public function delete_day_off() {
		check_ajax_referer( 'medsol_appointments_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'medsol-appointments' ) );
		}

		$day_off_id = absint( $_POST['day_off_id'] );
		$deleted = Medsol_Employee::delete_day_off( $day_off_id );

		if ( $deleted ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}
}