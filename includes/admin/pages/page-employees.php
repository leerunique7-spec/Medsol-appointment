<?php
/**
 * Employees admin page.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render employees page.
 */
function medsol_employees_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to access this page.', 'medsol-appointments' ) );
	}

	$table = new Medsol_Employee_List_Table();
	$table->process_bulk_action();
	$table->prepare_items();

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Employees', 'medsol-appointments' ); ?></h1>
		<form method="get">
			<input type="hidden" name="page" value="medsol-employees">
			<?php $table->display(); ?>
		</form>
		<div id="medsol-modal" style="display:none;"></div>
	</div>
	<?php
}