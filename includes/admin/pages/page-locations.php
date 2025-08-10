<?php
/**
 * Locations admin page.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render locations page.
 */
function medsol_locations_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to access this page.', 'medsol-appointments' ) );
	}

	$table = new Medsol_Location_List_Table();
	$table->process_bulk_action();
	$table->prepare_items();

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Locations', 'medsol-appointments' ); ?></h1>
		<form method="get">
			<input type="hidden" name="page" value="medsol-locations">
			<?php $table->display(); ?>
		</form>
		<div id="medsol-modal" style="display:none;"></div>
	</div>
	<?php
}