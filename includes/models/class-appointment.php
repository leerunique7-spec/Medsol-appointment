<?php
/**
 * Appointment model.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Medsol_Appointment
 */
class Medsol_Appointment {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private static $table = 'medsol_appointments';

	/**
	 * Create a new appointment.
	 *
	 * @param array $data Appointment data.
	 * @return int|false Appointment ID or false on failure.
	 */
	public static function create( array $data ) {
		global $wpdb;

		$data = apply_filters( 'medsol_appointment_before_create', $data );

		// Validate required fields.
		$required = array( 'customer_name', 'customer_email', 'employee_id', 'service_id', 'location_id', 'date', 'time', 'duration', 'status' );
		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new WP_Error( 'missing_field', __( 'Missing required field: ' . $field, 'medsol-appointments' ) );
			}
		}

		// Sanitize data.
		$data['customer_name']  = sanitize_text_field( $data['customer_name'] );
		$data['customer_email'] = sanitize_email( $data['customer_email'] );
		$data['customer_phone'] = sanitize_text_field( $data['customer_phone'] ?? '' );
		$data['note']           = sanitize_textarea_field( $data['note'] ?? '' );
		$data['employee_id']    = absint( $data['employee_id'] );
		$data['service_id']     = absint( $data['service_id'] );
		$data['location_id']    = absint( $data['location_id'] );
		$data['date']           = sanitize_text_field( $data['date'] ); // Assume YYYY-MM-DD.
		$data['time']           = sanitize_text_field( $data['time'] ); // Assume HH:MM.
		$data['duration']       = absint( $data['duration'] );
		$data['status']         = in_array( $data['status'], array( 'pending', 'approved', 'declined', 'canceled' ), true ) ? $data['status'] : 'pending';

		$inserted = $wpdb->insert(
			$wpdb->prefix . self::$table,
			array(
				'customer_name'  => $data['customer_name'],
				'customer_email' => $data['customer_email'],
				'customer_phone' => $data['customer_phone'],
				'note'           => $data['note'],
				'employee_id'    => $data['employee_id'],
				'service_id'     => $data['service_id'],
				'location_id'    => $data['location_id'],
				'date'           => $data['date'],
				'time'           => $data['time'],
				'duration'       => $data['duration'],
				'status'         => $data['status'],
			),
			array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%s' )
		);

		return $inserted ? $wpdb->insert_id : false;
	}

	/**
	 * Get a single appointment by ID.
	 *
	 * @param int $id Appointment ID.
	 * @return object|false Appointment object or false.
	 */
	public static function get( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		$appointment = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}" . self::$table . " WHERE id = %d", $id )
		);

		return $appointment ?: false;
	}

	/**
	 * Get all appointments with filters.
	 *
	 * @param array $args Query args (search, employee_id, customer_name, service_id, date_from, date_to, status, paged, per_page).
	 * @return array Appointments and total count.
	 */
	public static function get_all( array $args = array() ) {
		global $wpdb;

		$defaults = array(
			'search'       => '',
			'employee_id'  => 0,
			'customer_name'=> '',
			'service_id'   => 0,
			'date_from'    => '',
			'date_to'      => '',
			'status'       => '',
			'paged'        => 1,
			'per_page'     => 20,
			'orderby'      => 'date',
			'order'        => 'DESC',
		);
		$args = wp_parse_args( $args, $defaults );

		$where = 'WHERE 1=1';
		$where_params = array();

		if ( $args['search'] ) {
			$search = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where .= ' AND (customer_name LIKE %s OR customer_email LIKE %s)';
			$where_params[] = $search;
			$where_params[] = $search;
		}

		if ( $args['employee_id'] ) {
			$where .= ' AND employee_id = %d';
			$where_params[] = absint( $args['employee_id'] );
		}

		if ( $args['customer_name'] ) {
			$where .= ' AND customer_name LIKE %s';
			$where_params[] = '%' . $wpdb->esc_like( sanitize_text_field( $args['customer_name'] ) ) . '%';
		}

		if ( $args['service_id'] ) {
			$where .= ' AND service_id = %d';
			$where_params[] = absint( $args['service_id'] );
		}

		if ( $args['date_from'] ) {
			$where .= ' AND date >= %s';
			$where_params[] = sanitize_text_field( $args['date_from'] );
		}

		if ( $args['date_to'] ) {
			$where .= ' AND date <= %s';
			$where_params[] = sanitize_text_field( $args['date_to'] );
		}

		if ( $args['status'] ) {
			$where .= ' AND status = %s';
			$where_params[] = sanitize_text_field( $args['status'] );
		}

		$orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
		$orderby = $orderby ? "ORDER BY $orderby" : '';

		$limit_str = '';
		$limit_params = array();
		if ( $args['per_page'] > 0 ) {
			$offset = max( 0, ( absint( $args['paged'] ) - 1 ) * absint( $args['per_page'] ) );
			$limit_str = 'LIMIT %d OFFSET %d';
			$limit_params = array( absint( $args['per_page'] ), $offset );
		}

		$params = array_merge( $where_params, $limit_params );

		$query = "SELECT * FROM {$wpdb->prefix}" . self::$table . ( $where ? ' ' . $where : '' ) . ( $orderby ? ' ' . $orderby : '' ) . ( $limit_str ? ' ' . $limit_str : '' );

		if ( ! empty( $params ) ) {
			$appointments = $wpdb->get_results( $wpdb->prepare( $query, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL
		} else {
			$appointments = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		$total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}" . self::$table . ( $where ? ' ' . $where : '' );

		if ( ! empty( $where_params ) ) {
			$total = $wpdb->get_var( $wpdb->prepare( $total_query, $where_params ) ); // phpcs:ignore WordPress.DB.PreparedSQL
		} else {
			$total = $wpdb->get_var( $total_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		return array( 'appointments' => $appointments ?: array(), 'total' => (int) $total );
	}

	/**
	 * Update an appointment.
	 *
	 * @param int   $id   Appointment ID.
	 * @param array $data Data to update.
	 * @return bool
	 */
	public static function update( $id, array $data ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id || ! self::get( $id ) ) {
			return false;
		}

		$data = apply_filters( 'medsol_appointment_before_update', $data, $id );

		// Sanitize data (similar to create).
		if ( isset( $data['customer_name'] ) ) $data['customer_name'] = sanitize_text_field( $data['customer_name'] );
		if ( isset( $data['customer_email'] ) ) $data['customer_email'] = sanitize_email( $data['customer_email'] );
		if ( isset( $data['customer_phone'] ) ) $data['customer_phone'] = sanitize_text_field( $data['customer_phone'] );
		if ( isset( $data['note'] ) ) $data['note'] = sanitize_textarea_field( $data['note'] );
		if ( isset( $data['employee_id'] ) ) $data['employee_id'] = absint( $data['employee_id'] );
		if ( isset( $data['service_id'] ) ) $data['service_id'] = absint( $data['service_id'] );
		if ( isset( $data['location_id'] ) ) $data['location_id'] = absint( $data['location_id'] );
		if ( isset( $data['date'] ) ) $data['date'] = sanitize_text_field( $data['date'] );
		if ( isset( $data['time'] ) ) $data['time'] = sanitize_text_field( $data['time'] );
		if ( isset( $data['duration'] ) ) $data['duration'] = absint( $data['duration'] );
		if ( isset( $data['status'] ) ) $data['status'] = in_array( $data['status'], array( 'pending', 'approved', 'declined', 'canceled' ), true ) ? $data['status'] : 'pending';

		$formats = array();
		foreach ( $data as $key => $value ) {
			switch ( $key ) {
				case 'duration': case 'employee_id': case 'service_id': case 'location_id':
					$formats[ $key ] = '%d';
					break;
				default:
					$formats[ $key ] = '%s';
			}
		}

		return (bool) $wpdb->update(
			$wpdb->prefix . self::$table,
			$data,
			array( 'id' => $id ),
			array_values( $formats ),
			array( '%d' )
		);
	}

	/**
	 * Delete an appointment.
	 *
	 * @param int $id Appointment ID.
	 * @return bool
	 */
	public static function delete( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		do_action( 'medsol_appointment_before_delete', $id );

		return (bool) $wpdb->delete( $wpdb->prefix . self::$table, array( 'id' => $id ), array( '%d' ) );
	}
}