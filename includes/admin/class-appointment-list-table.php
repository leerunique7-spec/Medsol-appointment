<?php
/**
 * Appointment list table.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Medsol_Appointment_List_Table
 */
class Medsol_Appointment_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'appointment',
			'plural'   => 'appointments',
			'ajax'     => false,
		) );
	}

	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'        => '<input type="checkbox" />',
			'customer'  => __( 'Customer', 'medsol-appointments' ),
			'employee'  => __( 'Employee', 'medsol-appointments' ),
			'service'   => __( 'Service', 'medsol-appointments' ),
			'duration'  => __( 'Duration', 'medsol-appointments' ),
			'location'  => __( 'Location', 'medsol-appointments' ),
			'status'    => __( 'Status', 'medsol-appointments' ),
			'actions'   => __( 'Actions', 'medsol-appointments' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'status' => array( 'status', true ),
		);
	}

	/**
	 * Prepare items.
	 */
	public function prepare_items() {
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$per_page = $this->get_items_per_page( 'medsol_appointments_per_page', 20 );
		$current_page = $this->get_pagenum();

		$args = array(
			'search'       => sanitize_text_field( $_GET['s'] ?? '' ),
			'employee_id'  => absint( $_GET['employee_id'] ?? 0 ),
			'customer_name'=> sanitize_text_field( $_GET['customer_name'] ?? '' ),
			'service_id'   => absint( $_GET['service_id'] ?? 0 ),
			'date_from'    => sanitize_text_field( $_GET['date_from'] ?? '' ),
			'date_to'      => sanitize_text_field( $_GET['date_to'] ?? '' ),
			'status'       => sanitize_text_field( $_GET['status'] ?? '' ),
			'paged'        => $current_page,
			'per_page'     => $per_page,
			'orderby'      => sanitize_text_field( $_GET['orderby'] ?? 'date' ),
			'order'        => sanitize_text_field( $_GET['order'] ?? 'DESC' ),
		);

		$data = Medsol_Appointment::get_all( $args );

		$this->items = $data['appointments'];
		$this->set_pagination_args( array(
			'total_items' => $data['total'],
			'per_page'    => $per_page,
		) );
	}

	/**
	 * Column default.
	 *
	 * @param object $item Item.
	 * @param string $column_name Column.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'customer':
				return esc_html( $item->customer_name . ' (' . $item->customer_email . ')' );
			case 'employee':
				$employee = Medsol_Employee::get( $item->employee_id );
				return $employee ? esc_html( $employee->first_name . ' ' . $employee->last_name ) : __( 'Unknown', 'medsol-appointments' );
			case 'service':
				$service = Medsol_Service::get( $item->service_id );
				return $service ? esc_html( $service->name ) : __( 'Unknown', 'medsol-appointments' );
			case 'duration':
				return esc_html( $item->duration . ' min' );
			case 'location':
				$location = Medsol_Location::get( $item->location_id );
				return $location ? esc_html( $location->name ) : __( 'Unknown', 'medsol-appointments' );
			case 'status':
				return esc_html( ucfirst( $item->status ) );
			case 'actions':
				return '<button type="button" class="button edit-appointment" data-id="' . esc_attr( $item->id ) . '">' . __( 'Edit', 'medsol-appointments' ) . '</button>';
			default:
				return '';
		}
	}

	/**
	 * Checkbox column.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="appointment[]" value="%s" />', $item->id );
	}

	/**
	 * Bulk actions.
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'medsol-appointments' ),
		);
	}

	/**
	 * Process bulk actions.
	 */
	public function process_bulk_action() {
		if ( 'delete' === $this->current_action() && check_admin_referer( 'bulk-appointments' ) ) {
			$ids = array_map( 'absint', $_REQUEST['appointment'] ?? array() );
			foreach ( $ids as $id ) {
				Medsol_Appointment::delete( $id );
			}
		}
	}

	/**
	 * Extra table nav (filters).
	 *
	 * @param string $which Top or bottom.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			// Filters: search, employee, customer, service, date range, status.
			?>
			<div class="alignleft actions">
				<input type="text" name="s" value="<?php echo esc_attr( $_GET['s'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Search', 'medsol-appointments' ); ?>">
				<select name="employee_id">
					<option value="0"><?php esc_html_e( 'All Employees', 'medsol-appointments' ); ?></option>
					<?php
					$employees = Medsol_Employee::get_all( array( 'per_page' => -1 ) )['employees'];
					foreach ( $employees as $emp ) {
						printf( '<option value="%d" %s>%s</option>', $emp->id, selected( $_GET['employee_id'] ?? 0, $emp->id, false ), esc_html( $emp->first_name . ' ' . $emp->last_name ) );
					}
					?>
				</select>
				<input type="text" name="customer_name" value="<?php echo esc_attr( $_GET['customer_name'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Customer Name', 'medsol-appointments' ); ?>">
				<select name="service_id">
					<option value="0"><?php esc_html_e( 'All Services', 'medsol-appointments' ); ?></option>
					<?php
					$services = Medsol_Service::get_all( array( 'per_page' => -1 ) )['services'];
					foreach ( $services as $serv ) {
						printf( '<option value="%d" %s>%s</option>', $serv->id, selected( $_GET['service_id'] ?? 0, $serv->id, false ), esc_html( $serv->name ) );
					}
					?>
				</select>
				<input type="date" name="date_from" value="<?php echo esc_attr( $_GET['date_from'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Date From', 'medsol-appointments' ); ?>">
				<input type="date" name="date_to" value="<?php echo esc_attr( $_GET['date_to'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Date To', 'medsol-appointments' ); ?>">
				<select name="status">
					<option value=""><?php esc_html_e( 'All Statuses', 'medsol-appointments' ); ?></option>
					<option value="pending" <?php selected( $_GET['status'] ?? '', 'pending' ); ?>><?php esc_html_e( 'Pending', 'medsol-appointments' ); ?></option>
					<option value="approved" <?php selected( $_GET['status'] ?? '', 'approved' ); ?>><?php esc_html_e( 'Approved', 'medsol-appointments' ); ?></option>
					<option value="declined" <?php selected( $_GET['status'] ?? '', 'declined' ); ?>><?php esc_html_e( 'Declined', 'medsol-appointments' ); ?></option>
					<option value="canceled" <?php selected( $_GET['status'] ?? '', 'canceled' ); ?>><?php esc_html_e( 'Canceled', 'medsol-appointments' ); ?></option>
				</select>
				<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'medsol-appointments' ); ?>">
			</div>
			<button type="button" class="button add-appointment"><?php esc_html_e( 'Add New Appointment', 'medsol-appointments' ); ?></button>
			<?php
		}
	}
}