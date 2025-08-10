<?php
/**
 * Appointments admin page.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render appointments page.
 */
function medsol_appointments_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to access this page.', 'medsol-appointments' ) );
	}

	$table = new Medsol_Appointment_List_Table();
	$table->process_bulk_action();
	$table->prepare_items();

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Appointments', 'medsol-appointments' ); ?></h1>
		<form method="get">
			<input type="hidden" name="page" value="medsol-appointments">
			<?php $table->display(); ?>
		</form>
		<!-- Modal placeholder (loaded via JS in Phase 4) -->
		<div id="medsol-modal" style="display:none;"></div>
	</div>
	<?php
}