<?php
/**
 * Location list table.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Medsol_Location_List_Table
 */
class Medsol_Location_List_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'location',
			'plural'   => 'locations',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return array(
			'cb'      => '<input type="checkbox" />',
			'name'    => __( 'Name', 'medsol-appointments' ),
			'address' => __( 'Address', 'medsol-appointments' ),
			'phone'   => __( 'Phone', 'medsol-appointments' ),
			'actions' => __( 'Actions', 'medsol-appointments' ),
		);
	}

	protected function get_sortable_columns() {
		return array(
			'name' => array( 'name', true ),
		);
	}

	public function prepare_items() {
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$per_page = $this->get_items_per_page( 'medsol_locations_per_page', 20 );
		$current_page = $this->get_pagenum();

		$args = array(
			'search'   => sanitize_text_field( $_GET['s'] ?? '' ),
			'paged'    => $current_page,
			'per_page' => $per_page,
			'orderby'  => sanitize_text_field( $_GET['orderby'] ?? 'name' ),
			'order'    => sanitize_text_field( $_GET['order'] ?? 'ASC' ),
		);

		$data = Medsol_Location::get_all( $args );

		$this->items = $data['locations'];
		$this->set_pagination_args( array(
			'total_items' => $data['total'],
			'per_page'    => $per_page,
		) );
	}

	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
				return esc_html( $item->name );
			case 'address':
				return esc_html( $item->address );
			case 'phone':
				return esc_html( $item->phone );
			case 'actions':
				return '<button type="button" class="button edit-location" data-id="' . esc_attr( $item->id ) . '">' . __( 'Edit', 'medsol-appointments' ) . '</button>';
			default:
				return '';
		}
	}

	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="location[]" value="%s" />', $item->id );
	}

	protected function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'medsol-appointments' ),
		);
	}

	public function process_bulk_action() {
		if ( 'delete' === $this->current_action() && check_admin_referer( 'bulk-locations' ) ) {
			$ids = array_map( 'absint', $_REQUEST['location'] ?? array() );
			foreach ( $ids as $id ) {
				Medsol_Location::delete( $id );
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
			<button type="button" class="button add-location"><?php esc_html_e( 'Add New Location', 'medsol-appointments' ); ?></button>
			<?php
		}
	}
}