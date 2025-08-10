<?php
/**
 * Settings admin page.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render settings page.
 */
function medsol_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to access this page.', 'medsol-appointments' ) );
	}

	// Register settings.
	register_setting( 'medsol_settings', 'medsol_appointments_settings' );

	add_settings_section( 'medsol_settings_general', __( 'General Settings', 'medsol-appointments' ), null, 'medsol_settings' );

	add_settings_field( 'medsol_busy_slots', __( 'Busy Slots Calculated By', 'medsol-appointments' ), 'medsol_busy_slots_callback', 'medsol_settings', 'medsol_settings_general' );
	add_settings_field( 'medsol_default_status', __( 'Default Status of Booking', 'medsol-appointments' ), 'medsol_default_status_callback', 'medsol_settings', 'medsol_settings_general' );
	add_settings_field( 'medsol_erase_on_uninstall', __( 'Remove All Data on Uninstall', 'medsol-appointments' ), 'medsol_erase_on_uninstall_callback', 'medsol_settings', 'medsol_settings_general' );

	$options = get_option( 'medsol_appointments_settings', array() );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Settings', 'medsol-appointments' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'medsol_settings' );
			do_settings_sections( 'medsol_settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Busy slots callback.
 */
function medsol_busy_slots_callback() {
	$options = get_option( 'medsol_appointments_settings', array() );
	$value = $options['busy_slots'] ?? 'location';
	?>
	<label><input type="radio" name="medsol_appointments_settings[busy_slots]" value="location" <?php checked( 'location', $value ); ?>> <?php esc_html_e( 'By Location', 'medsol-appointments' ); ?></label>
	<label><input type="radio" name="medsol_appointments_settings[busy_slots]" value="service" <?php checked( 'service', $value ); ?>> <?php esc_html_e( 'By Service', 'medsol-appointments' ); ?></label>
	<?php
}

/**
 * Default status callback.
 */
function medsol_default_status_callback() {
	$options = get_option( 'medsol_appointments_settings', array() );
	$value = $options['default_status'] ?? 'pending';
	?>
	<select name="medsol_appointments_settings[default_status]">
		<option value="pending" <?php selected( 'pending', $value ); ?>><?php esc_html_e( 'Pending', 'medsol-appointments' ); ?></option>
		<option value="approved" <?php selected( 'approved', $value ); ?>><?php esc_html_e( 'Approved', 'medsol-appointments' ); ?></option>
		<option value="declined" <?php selected( 'declined', $value ); ?>><?php esc_html_e( 'Declined', 'medsol-appointments' ); ?></option>
		<option value="canceled" <?php selected( 'canceled', $value ); ?>><?php esc_html_e( 'Canceled', 'medsol-appointments' ); ?></option>
	</select>
	<?php
}

/**
 * Erase on uninstall callback.
 */
function medsol_erase_on_uninstall_callback() {
	$options = get_option( 'medsol_appointments_settings', array() );
	$checked = $options['erase_on_uninstall'] ?? 0;
	echo '<input type="checkbox" name="medsol_appointments_settings[erase_on_uninstall]" value="1" ' . checked( 1, $checked, false ) . '>';
	echo '<p class="description">' . esc_html__( 'Erase all plugin data and drop tables on uninstall.', 'medsol-appointments' ) . '</p>';
}