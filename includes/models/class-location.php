<?php
/**
 * Location model.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Medsol_Location
 */
class Medsol_Location {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private static $table = 'medsol_locations';

	/**
	 * Days off table.
	 *
	 * @var string
	 */
	private static $days_off_table = 'medsol_location_days_off';

	/**
	 * Create a new location.
	 *
	 * @param array $data Location data.
	 * @return int|false Location ID or false.
	 */
	public static function create( array $data ) {
		global $wpdb;

		$data = apply_filters( 'medsol_location_before_create', $data );

		// Validate required fields.
		if ( empty( $data['name'] ) ) {
			return new WP_Error( 'missing_field', __( 'Missing required field: name', 'medsol-appointments' ) );
		}

		// Sanitize data.
		$data['name']                 = sanitize_text_field( $data['name'] );
		$data['address']              = sanitize_textarea_field( $data['address'] ?? '' );
		$data['phone']                = sanitize_text_field( $data['phone'] ?? '' );
		$data['min_booking_time']     = absint( $data['min_booking_time'] ?? 0 );
		$data['weekly_availability']  = wp_json_encode( $data['weekly_availability'] ?? array() ); // Assume array of days.

		$inserted = $wpdb->insert(
			$wpdb->prefix . self::$table,
			array(
				'name'                => $data['name'],
				'address'             => $data['address'],
				'phone'               => $data['phone'],
				'min_booking_time'    => $data['min_booking_time'],
				'weekly_availability' => $data['weekly_availability'],
			),
			array( '%s', '%s', '%s', '%d', '%s' )
		);

		return $inserted ? $wpdb->insert_id : false;
	}

	/**
	 * Get a single location by ID.
	 *
	 * @param int $id Location ID.
	 * @return object|false
	 */
	public static function get( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		$location = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}" . self::$table . " WHERE id = %d", $id )
		);

		if ( $location ) {
			$location->weekly_availability = json_decode( $location->weekly_availability, true );
			$location->days_off = self::get_days_off( $id );
		}

		return $location ?: false;
	}

	/**
	 * Get all locations with filters.
	 *
	 * @param array $args Query args (search, paged, per_page).
	 * @return array Locations and total.
	 */
	public static function get_all( array $args = array() ) {
		global $wpdb;

		$defaults = array(
			'search'   => '',
			'paged'    => 1,
			'per_page' => 20,
			'orderby'  => 'name',
			'order'    => 'ASC',
		);
		$args = wp_parse_args( $args, $defaults );

		if ( $args['per_page'] <= 0 && empty( $args['search'] ) ) { // Cache only full lists without search.
			$transient_key = 'medsol_locations_all';
			$cached = get_transient( $transient_key );
			if ( $cached ) {
				return $cached;
			}
		}

		$where = 'WHERE 1=1';
		$where_params = array();

		if ( $args['search'] ) {
			$search = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where .= ' AND (name LIKE %s OR address LIKE %s)';
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
			$locations = $wpdb->get_results( $wpdb->prepare( $query, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL
		} else {
			$locations = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		if ( $locations ) {
			foreach ( $locations as $location ) {
				$location->weekly_availability = json_decode( $location->weekly_availability, true );
			}
		}

		$total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}" . self::$table . ( $where ? ' ' . $where : '' );

		if ( ! empty( $where_params ) ) {
			$total = $wpdb->get_var( $wpdb->prepare( $total_query, $where_params ) ); // phpcs:ignore WordPress.DB.PreparedSQL
		} else {
			$total = $wpdb->get_var( $total_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		$result = array( 'locations' => $locations ?: array(), 'total' => (int) $total );

		if ( $args['per_page'] <= 0 && empty( $args['search'] ) ) {
			set_transient( $transient_key, $result, HOUR_IN_SECONDS ); // Cache 1 hour.
		}

		return $result;
	}

	/**
	 * Update a location.
	 *
	 * @param int   $id   Location ID.
	 * @param array $data Data to update.
	 * @return bool
	 */
	public static function update( $id, array $data ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id || ! self::get( $id ) ) {
			return false;
		}

		$data = apply_filters( 'medsol_location_before_update', $data, $id );

		// Sanitize data.
		if ( isset( $data['name'] ) ) $data['name'] = sanitize_text_field( $data['name'] );
		if ( isset( $data['address'] ) ) $data['address'] = sanitize_textarea_field( $data['address'] );
		if ( isset( $data['phone'] ) ) $data['phone'] = sanitize_text_field( $data['phone'] );
		if ( isset( $data['min_booking_time'] ) ) $data['min_booking_time'] = absint( $data['min_booking_time'] );
		if ( isset( $data['weekly_availability'] ) ) $data['weekly_availability'] = wp_json_encode( $data['weekly_availability'] );

		$formats = array();
		foreach ( $data as $key => $value ) {
			$formats[ $key ] = ( 'min_booking_time' === $key ) ? '%d' : '%s';
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
	 * Delete a location.
	 *
	 * @param int $id Location ID.
	 * @return bool
	 */
	public static function delete( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		do_action( 'medsol_location_before_delete', $id );

		// Delete days off first.
		$wpdb->delete( $wpdb->prefix . self::$days_off_table, array( 'location_id' => $id ), array( '%d' ) );

		return (bool) $wpdb->delete( $wpdb->prefix . self::$table, array( 'id' => $id ), array( '%d' ) );
	}

	/**
	 * Add a day off for location.
	 *
	 * @param int   $location_id Location ID.
	 * @param array $data Day off data.
	 * @return int|false
	 */
	public static function add_day_off( $location_id, array $data ) {
		global $wpdb;

		$location_id = absint( $location_id );
		if ( ! $location_id ) {
			return false;
		}

		$data['reason']     = sanitize_text_field( $data['reason'] ?? '' );
		$data['start_date'] = sanitize_text_field( $data['start_date'] );
		$data['end_date']   = sanitize_text_field( $data['end_date'] );

		$inserted = $wpdb->insert(
			$wpdb->prefix . self::$days_off_table,
			array(
				'location_id' => $location_id,
				'reason'      => $data['reason'],
				'start_date'  => $data['start_date'],
				'end_date'    => $data['end_date'],
			),
			array( '%d', '%s', '%s', '%s' )
		);

		return $inserted ? $wpdb->insert_id : false;
	}

	/**
	 * Get days off for location.
	 *
	 * @param int $location_id Location ID.
	 * @return array
	 */
	public static function get_days_off( $location_id ) {
		global $wpdb;

		$location_id = absint( $location_id );

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}" . self::$days_off_table . " WHERE location_id = %d", $location_id )
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