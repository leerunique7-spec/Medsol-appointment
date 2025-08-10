<?php
/**
 * Employee list table.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Medsol_Employee_List_Table
 */
class Medsol_Employee_List_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'employee',
			'plural'   => 'employees',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return array(
			'cb'     => '<input type="checkbox" />',
			'name'   => __( 'Name', 'medsol-appointments' ),
			'email'  => __( 'Email', 'medsol-appointments' ),
			'phone'  => __( 'Phone', 'medsol-appointments' ),
			'actions'=> __( 'Actions', 'medsol-appointments' ),
		);
	}

	protected function get_sortable_columns() {
		return array(
			'name' => array( 'last_name', true ),
		);
	}

	public function prepare_items() {
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$per_page = $this->get_items_per_page( 'medsol_employees_per_page', 20 );
		$current_page = $this->get_pagenum();

		$args = array(
			'search'   => sanitize_text_field( $_GET['s'] ?? '' ),
			'paged'    => $current_page,
			'per_page' => $per_page,
			'orderby'  => sanitize_text_field( $_GET['orderby'] ?? 'last_name' ),
			'order'    => sanitize_text_field( $_GET['order'] ?? 'ASC' ),
		);

		$data = Medsol_Employee::get_all( $args );

		$this->items = $data['employees'];
		$this->set_pagination_args( array(
			'total_items' => $data['total'],
			'per_page'    => $per_page,
		) );
	}

	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
				return esc_html( $item->first_name . ' ' . $item->last_name );
			case 'email':
				return esc_html( $item->email );
			case 'phone':
				return esc_html( $item->phone );
			case 'actions':
				return '<button type="button" class="button edit-employee" data-id="' . esc_attr( $item->id ) . '">' . __( 'Edit', 'medsol-appointments' ) . '</button>';
			default:
				return '';
		}
	}

	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="employee[]" value="%s" />', $item->id );
	}

	protected function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'medsol-appointments' ),
		);
	}

	public function process_bulk_action() {
		if ( 'delete' === $this->current_action() && check_admin_referer( 'bulk-employees' ) ) {
			$ids = array_map( 'absint', $_REQUEST['employee'] ?? array() );
			foreach ( $ids as $id ) {
				Medsol_Employee::delete( $id );
			}
		}
	}

	protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			?>
			<div class="alignleft actions">
				<input type="text" name="s" value="<?php echo esc_attr( $_GET['s'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Search', 'medsol-appointments' ); ?>">
				<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'medsol-appointments' ); ?>">
			</div>
			<button type="button" class="button add-employee"><?php esc_html_e( 'Add New Employee', 'medsol-appointments' ); ?></button>
			<?php
		}
	}
}