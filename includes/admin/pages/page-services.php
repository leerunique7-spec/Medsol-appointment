<?php
/**
 * Services admin page.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render services page.
 */
function medsol_services_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to access this page.', 'medsol-appointments' ) );
	}

	$table = new Medsol_Service_List_Table();
	$table->process_bulk_action();
	$table->prepare_items();

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Services', 'medsol-appointments' ); ?></h1>
		<form method="get">
			<input type="hidden" name="page" value="medsol-services">
			<?php $table->display(); ?>
		</form>
		<div id="medsol-modal" style="display:none;"></div>
	</div>
	<?php
}