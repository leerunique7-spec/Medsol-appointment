<?php
/**
 * Employee model.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Medsol_Employee
 */
class Medsol_Employee {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private static $table = 'medsol_employees';

	/**
	 * Days off table.
	 *
	 * @var string
	 */
	private static $days_off_table = 'medsol_employee_days_off';

	/**
	 * Create a new employee.
	 *
	 * @param array $data Employee data.
	 * @return int|false Employee ID or false.
	 */
	public static function create( array $data ) {
		global $wpdb;

		$data = apply_filters( 'medsol_employee_before_create', $data );

		// Validate required fields.
		$required = array( 'first_name', 'last_name', 'email' );
		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new WP_Error( 'missing_field', __( 'Missing required field: ' . $field, 'medsol-appointments' ) );
			}
		}

		// Sanitize data.
		$data['first_name'] = sanitize_text_field( $data['first_name'] );
		$data['last_name']  = sanitize_text_field( $data['last_name'] );
		$data['email']      = sanitize_email( $data['email'] );
		$data['phone']      = sanitize_text_field( $data['phone'] ?? '' );
		$data['role']       = sanitize_text_field( $data['role'] ?? '' );

		$inserted = $wpdb->insert(
			$wpdb->prefix . self::$table,
			array(
				'first_name' => $data['first_name'],
				'last_name'  => $data['last_name'],
				'email'      => $data['email'],
				'phone'      => $data['phone'],
				'role'       => $data['role'],
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);

		return $inserted ? $wpdb->insert_id : false;
	}

	/**
	 * Get a single employee by ID.
	 *
	 * @param int $id Employee ID.
	 * @return object|false
	 */
	public static function get( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		$employee = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}" . self::$table . " WHERE id = %d", $id )
		);

		if ( $employee ) {
			$employee->days_off = self::get_days_off( $id );
		}

		return $employee ?: false;
	}

	/**
	 * Get all employees with filters.
	 *
	 * @param array $args Query args (search, paged, per_page).
	 * @return array Employees and total.
	 */
	public static function get_all( array $args = array() ) {
		global $wpdb;

		$defaults = array(
			'search'   => '',
			'paged'    => 1,
			'per_page' => 20,
			'orderby'  => 'last_name',
			'order'    => 'ASC',
		);
		$args = wp_parse_args( $args, $defaults );

		if ( $args['per_page'] <= 0 && empty( $args['search'] ) ) { // Cache only full lists without search.
			$transient_key = 'medsol_employees_all';
			$cached = get_transient( $transient_key );
			if ( $cached ) {
				return $cached;
			}
		}

		$where = 'WHERE 1=1';
		$where_params = array();

		if ( $args['search'] ) {
			$search = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where .= ' AND (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s)';
			$where_params[] = $search;
			$where_params[] = $search;
			$where_params[] = $search;
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
			$employees = $wpdb->get_results( $wpdb->prepare( $query, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL
		} else {
			$employees = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		$total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}" . self::$table . ( $where ? ' ' . $where : '' );

		if ( ! empty( $where_params ) ) {
			$total = $wpdb->get_var( $wpdb->prepare( $total_query, $where_params ) ); // phpcs:ignore WordPress.DB.PreparedSQL
		} else {
			$total = $wpdb->get_var( $total_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		$result = array( 'employees' => $employees ?: array(), 'total' => (int) $total );

		if ( $args['per_page'] <= 0 && empty( $args['search'] ) ) {
			set_transient( $transient_key, $result, HOUR_IN_SECONDS ); // Cache 1 hour.
		}

		return $result;
	}

	/**
	 * Update an employee.
	 *
	 * @param int   $id   Employee ID.
	 * @param array $data Data to update.
	 * @return bool
	 */
	public static function update( $id, array $data ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id || ! self::get( $id ) ) {
			return false;
		}

		$data = apply_filters( 'medsol_employee_before_update', $data, $id );

		// Sanitize data.
		if ( isset( $data['first_name'] ) ) $data['first_name'] = sanitize_text_field( $data['first_name'] );
		if ( isset( $data['last_name'] ) ) $data['last_name'] = sanitize_text_field( $data['last_name'] );
		if ( isset( $data['email'] ) ) $data['email'] = sanitize_email( $data['email'] );
		if ( isset( $data['phone'] ) ) $data['phone'] = sanitize_text_field( $data['phone'] );
		if ( isset( $data['role'] ) ) $data['role'] = sanitize_text_field( $data['role'] );

		$formats = array_fill( 0, count( $data ), '%s' );

		return (bool) $wpdb->update(
			$wpdb->prefix . self::$table,
			$data,
			array( 'id' => $id ),
			$formats,
			array( '%d' )
		);
	}

	/**
	 * Delete an employee.
	 *
	 * @param int $id Employee ID.
	 * @return bool
	 */
	public static function delete( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		do_action( 'medsol_employee_before_delete', $id );

		// Delete days off first.
		$wpdb->delete( $wpdb->prefix . self::$days_off_table, array( 'employee_id' => $id ), array( '%d' ) );

		return (bool) $wpdb->delete( $wpdb->prefix . self::$table, array( 'id' => $id ), array( '%d' ) );
	}

	/**
	 * Add a day off for employee.
	 *
	 * @param int   $employee_id Employee ID.
	 * @param array $data Day off data (reason, start_date, end_date).
	 * @return int|false Day off ID or false.
	 */
	public static function add_day_off( $employee_id, array $data ) {
		global $wpdb;

		$employee_id = absint( $employee_id );
		if ( ! $employee_id ) {
			return false;
		}

		$data['reason']     = sanitize_text_field( $data['reason'] ?? '' );
		$data['start_date'] = sanitize_text_field( $data['start_date'] );
		$data['end_date']   = sanitize_text_field( $data['end_date'] );

		$inserted = $wpdb->insert(
			$wpdb->prefix . self::$days_off_table,
			array(
				'employee_id' => $employee_id,
				'reason'      => $data['reason'],
				'start_date'  => $data['start_date'],
				'end_date'    => $data['end_date'],
			),
			array( '%d', '%s', '%s', '%s' )
		);

		return $inserted ? $wpdb->insert_id : false;
	}

	/**
	 * Get days off for employee.
	 *
	 * @param int $employee_id Employee ID.
	 * @return array
	 */
	public static function get_days_off( $employee_id ) {
		global $wpdb;

		$employee_id = absint( $employee_id );

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}" . self::$days_off_table . " WHERE employee_id = %d", $employee_id )
		);
	}

	/**
	 * Delete a day off.
	 *
	 * @param int $day_off_id Day off ID.
	 * @return bool
	 */
	public static function delete_day_off( $day_off_id ) {
		global $wpdb;

		$day_off_id = absint( $day_off_id );

		return (bool) $wpdb->delete( $wpdb->prefix . self::$days_off_table, array( 'id' => $day_off_id ), array( '%d' ) );
	}
}