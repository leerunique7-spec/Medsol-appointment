<?php
/**
 * Employee modal template.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="medsol-off-canvas">
	<div class="medsol-off-canvas-header">
		<h2><?php echo $employee ? esc_html__( 'Edit Employee', 'medsol-appointments' ) : esc_html__( 'Add Employee', 'medsol-appointments' ); ?></h2>
	</div>
	<div class="medsol-off-canvas-body">
		<h2 class="nav-tab-wrapper">
			<a href="#employee-details" class="nav-tab nav-tab-active"><?php esc_html_e( 'Details', 'medsol-appointments' ); ?></a>
			<a href="#employee-days-off" class="nav-tab"><?php esc_html_e( 'Days Off', 'medsol-appointments' ); ?></a>
		</h2>
		<form id="medsol-employee-form">
			<input type="hidden" name="id" value="<?php echo esc_attr( $employee->id ?? 0 ); ?>">
			<div id="employee-details" class="tab-content">
				<label><?php esc_html_e( 'First Name', 'medsol-appointments' ); ?></label>
				<input type="text" name="first_name" value="<?php echo esc_attr( $employee->first_name ?? '' ); ?>">

				<label><?php esc_html_e( 'Last Name', 'medsol-appointments' ); ?></label>
				<input type="text" name="last_name" value="<?php echo esc_attr( $employee->last_name ?? '' ); ?>">

				<label><?php esc_html_e( 'Email', 'medsol-appointments' ); ?></label>
				<input type="email" name="email" value="<?php echo esc_attr( $employee->email ?? '' ); ?>">

				<label><?php esc_html_e( 'Phone', 'medsol-appointments' ); ?></label>
				<input type="text" name="phone" value="<?php echo esc_attr( $employee->phone ?? '' ); ?>">

				<label><?php esc_html_e( 'Role', 'medsol-appointments' ); ?></label>
				<input type="text" name="role" value="<?php echo esc_attr( $employee->role ?? '' ); ?>">
			</div>
			<div id="employee-days-off" class="tab-content" style="display:none;">
				<div class="days-off-list">
					<?php if ( ! empty( $employee->days_off ) ) : ?>
						<?php foreach ( $employee->days_off as $index => $day ) : ?>
							<div class="day-off-row">
								<input type="hidden" name="days_off[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $day->id ); ?>">
								<input type="text" name="days_off[<?php echo esc_attr( $index ); ?>][reason]" value="<?php echo esc_attr( $day->reason ); ?>" placeholder="<?php esc_attr_e( 'Reason', 'medsol-appointments' ); ?>">
								<input type="date" name="days_off[<?php echo esc_attr( $index ); ?>][start_date]" value="<?php echo esc_attr( $day->start_date ); ?>">
								<input type="date" name="days_off[<?php echo esc_attr( $index ); ?>][end_date]" value="<?php echo esc_attr( $day->end_date ); ?>">
								<button type="button" class="button remove-day-off" data-day-off-id="<?php echo esc_attr( $day->id ); ?>"><?php esc_html_e( 'Remove', 'medsol-appointments' ); ?></button>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<button type="button" class="button add-day-off"><?php esc_html_e( 'Add Day Off', 'medsol-appointments' ); ?></button>
			</div>
		</form>
	</div>
	<div class="medsol-off-canvas-footer">
		<button class="button button-primary medsol-save"><?php esc_html_e( 'Save', 'medsol-appointments' ); ?></button>
		<button class="button medsol-cancel"><?php esc_html_e( 'Cancel', 'medsol-appointments' ); ?></button>
	</div>
</div>