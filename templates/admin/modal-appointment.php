<?php
/**
 * Appointment modal template.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="medsol-off-canvas">
	<div class="medsol-off-canvas-header">
		<h2><?php echo $appointment ? esc_html__( 'Edit Appointment', 'medsol-appointments' ) : esc_html__( 'Add Appointment', 'medsol-appointments' ); ?></h2>
	</div>
	<div class="medsol-off-canvas-body">
		<form id="medsol-appointment-form">
			<input type="hidden" name="id" value="<?php echo esc_attr( $appointment->id ?? 0 ); ?>">
			<!-- Select Location -->
			<label><?php esc_html_e( 'Location', 'medsol-appointments' ); ?></label>
			<select name="location_id">
				<option value="0"><?php esc_html_e( 'Select Location', 'medsol-appointments' ); ?></option>
				<?php
				$locations = Medsol_Location::get_all( array( 'per_page' => -1 ) )['locations'];
				if ( empty( $locations ) ) {
					echo '<option value="">' . esc_html__( 'No locations found - add some first!', 'medsol-appointments' ) . '</option>';
				} else {
					foreach ( $locations as $loc ) {
						printf( '<option value="%d" %s>%s</option>', esc_attr( $loc->id ), selected( $appointment->location_id ?? 0, $loc->id, false ), esc_html( $loc->name ) );
					}
				}
				?>
			</select>

			<!-- Select Service -->
			<label><?php esc_html_e( 'Service', 'medsol-appointments' ); ?></label>
			<select name="service_id">
				<option value="0"><?php esc_html_e( 'Select Service', 'medsol-appointments' ); ?></option>
				<?php
				$services = Medsol_Service::get_all( array( 'per_page' => -1 ) )['services'];
				if ( empty( $services ) ) {
					echo '<option value="">' . esc_html__( 'No services found - add some first!', 'medsol-appointments' ) . '</option>';
				} else {
					foreach ( $services as $serv ) {
						printf( '<option value="%d" %s>%s</option>', esc_attr( $serv->id ), selected( $appointment->service_id ?? 0, $serv->id, false ), esc_html( $serv->name ) );
					}
				}
				?>
			</select>

			<!-- Select Employee -->
			<label><?php esc_html_e( 'Employee', 'medsol-appointments' ); ?></label>
			<select name="employee_id">
				<option value="0"><?php esc_html_e( 'Select Employee', 'medsol-appointments' ); ?></option>
				<?php
				$employees = Medsol_Employee::get_all( array( 'per_page' => -1 ) )['employees'];
				if ( empty( $employees ) ) {
					echo '<option value="">' . esc_html__( 'No employees found - add some first!', 'medsol-appointments' ) . '</option>';
				} else {
					foreach ( $employees as $emp ) {
						printf( '<option value="%d" %s>%s</option>', esc_attr( $emp->id ), selected( $appointment->employee_id ?? 0, $emp->id, false ), esc_html( $emp->first_name . ' ' . $emp->last_name ) );
					}
				}
				?>
			</select>

			<!-- Date and Time -->
			<label><?php esc_html_e( 'Date', 'medsol-appointments' ); ?></label>
			<input type="date" name="date" value="<?php echo esc_attr( $appointment->date ?? '' ); ?>">

			<label><?php esc_html_e( 'Time', 'medsol-appointments' ); ?></label>
			<input type="time" name="time" value="<?php echo esc_attr( $appointment->time ?? '' ); ?>">

			<!-- Customer Details -->
			<label><?php esc_html_e( 'Customer Name', 'medsol-appointments' ); ?></label>
			<input type="text" name="customer_name" value="<?php echo esc_attr( $appointment->customer_name ?? '' ); ?>">

			<label><?php esc_html_e( 'Customer Email', 'medsol-appointments' ); ?></label>
			<input type="email" name="customer_email" value="<?php echo esc_attr( $appointment->customer_email ?? '' ); ?>">

			<label><?php esc_html_e( 'Confirm Email', 'medsol-appointments' ); ?></label>
			<input type="email" name="confirm_email" value="<?php echo esc_attr( $appointment->customer_email ?? '' ); ?>">

			<label><?php esc_html_e( 'Customer Phone', 'medsol-appointments' ); ?></label>
			<input type="text" name="customer_phone" value="<?php echo esc_attr( $appointment->customer_phone ?? '' ); ?>">

			<label><?php esc_html_e( 'Note', 'medsol-appointments' ); ?></label>
			<textarea name="note"><?php echo esc_textarea( $appointment->note ?? '' ); ?></textarea>

			<!-- Duration (read-only, from service) -->
			<label><?php esc_html_e( 'Duration (minutes)', 'medsol-appointments' ); ?></label>
			<input type="number" name="duration" value="<?php echo esc_attr( $appointment->duration ?? '' ); ?>" readonly>

			<!-- Status -->
			<label><?php esc_html_e( 'Status', 'medsol-appointments' ); ?></label>
			<select name="status">
				<option value="pending" <?php selected( $appointment->status ?? 'pending', 'pending' ); ?>><?php esc_html_e( 'Pending', 'medsol-appointments' ); ?></option>
				<option value="approved" <?php selected( $appointment->status ?? '', 'approved' ); ?>><?php esc_html_e( 'Approved', 'medsol-appointments' ); ?></option>
				<option value="declined" <?php selected( $appointment->status ?? '', 'declined' ); ?>><?php esc_html_e( 'Declined', 'medsol-appointments' ); ?></option>
				<option value="canceled" <?php selected( $appointment->status ?? '', 'canceled' ); ?>><?php esc_html_e( 'Canceled', 'medsol-appointments' ); ?></option>
			</select>
		</form>
	</div>
	<div class="medsol-off-canvas-footer">
		<button class="button button-primary medsol-save"><?php esc_html_e( 'Save', 'medsol-appointments' ); ?></button>
		<button class="button medsol-cancel"><?php esc_html_e( 'Cancel', 'medsol-appointments' ); ?></button>
	</div>
</div>