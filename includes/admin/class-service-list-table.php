<?php
/**
 * Service list table.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Medsol_Service_List_Table
 */
class Medsol_Service_List_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'service',
			'plural'   => 'services',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'name'       => __( 'Name', 'medsol-appointments' ),
			'duration'   => __( 'Duration', 'medsol-appointments' ),
			'slot_capacity' => __( 'Slot Capacity', 'medsol-appointments' ),
			'actions'    => __( 'Actions', 'medsol-appointments' ),
		);
	}

	protected function get_sortable_columns() {
		return array(
			'name' => array( 'name', true ),
		);
	}

	public function prepare_items() {
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$per_page = $this->get_items_per_page( 'medsol_services_per_page', 20 );
		$current_page = $this->get_pagenum();

		$args = array(
			'search'   => sanitize_text_field( $_GET['s'] ?? '' ),
			'paged'    => $current_page,
			'per_page' => $per_page,
			'orderby'  => sanitize_text_field( $_GET['orderby'] ?? 'name' ),
			'order'    => sanitize_text_field( $_GET['order'] ?? 'ASC' ),
		);

		$data = Medsol_Service::get_all( $args );

		$this->items = $data['services'];
		$this->set_pagination_args( array(
			'total_items' => $data['total'],
			'per_page'    => $per_page,
		) );
	}

	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
				return esc_html( $item->name );
			case 'duration':
				return esc_html( $item->duration . ' min' );
			case 'slot_capacity':
				return esc_html( $item->slot_capacity ? $item->slot_capacity : __( 'Unlimited', 'medsol-appointments' ) );
			case 'actions':
				return '<button type="button" class="button edit-service" data-id="' . esc_attr( $item->id ) . '">' . __( 'Edit', 'medsol-appointments' ) . '</button>';
			default:
				return '';
		}
	}

	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="service[]" value="%s" />', $item->id );
	}

	protected function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'medsol-appointments' ),
		);
	}

	public function process_bulk_action() {
		if ( 'delete' === $this->current_action() && check_admin_referer( 'bulk-services' ) ) {
			$ids = array_map( 'absint', $_REQUEST['service'] ?? array() );
			foreach ( $ids as $id ) {
				Medsol_Service::delete( $id );
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
			<button type="button" class="button add-service"><?php esc_html_e( 'Add New Service', 'medsol-appointments' ); ?></button>
			<?php
		}
	}
}