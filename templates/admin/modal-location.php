<?php
/**
 * Location modal template.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

$days = array( 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' );
?>

<div class="medsol-off-canvas">
	<div class="medsol-off-canvas-header">
		<h2><?php echo $location ? esc_html__( 'Edit Location', 'medsol-appointments' ) : esc_html__( 'Add Location', 'medsol-appointments' ); ?></h2>
	</div>
	<div class="medsol-off-canvas-body">
		<h2 class="nav-tab-wrapper">
			<a href="#location-details" class="nav-tab nav-tab-active"><?php esc_html_e( 'Details', 'medsol-appointments' ); ?></a>
			<a href="#location-days-off" class="nav-tab"><?php esc_html_e( 'Days Off', 'medsol-appointments' ); ?></a>
		</h2>
		<form id="medsol-location-form">
			<input type="hidden" name="id" value="<?php echo esc_attr( $location->id ?? 0 ); ?>">
			<div id="location-details" class="tab-content">
				<label><?php esc_html_e( 'Name', 'medsol-appointments' ); ?></label>
				<input type="text" name="name" value="<?php echo esc_attr( $location->name ?? '' ); ?>">

				<label><?php esc_html_e( 'Address', 'medsol-appointments' ); ?></label>
				<textarea name="address"><?php echo esc_textarea( $location->address ?? '' ); ?></textarea>

				<label><?php esc_html_e( 'Phone', 'medsol-appointments' ); ?></label>
				<input type="text" name="phone" value="<?php echo esc_attr( $location->phone ?? '' ); ?>">

				<label><?php esc_html_e( 'Minimum Time Before Booking (hours)', 'medsol-appointments' ); ?></label>
				<input type="number" name="min_booking_time" value="<?php echo esc_attr( $location->min_booking_time ?? 0 ); ?>">

				<h3><?php esc_html_e( 'Weekly Availability', 'medsol-appointments' ); ?></h3>
				<?php foreach ( $days as $day ) : ?>
					<div class="availability-row">
						<label><?php echo esc_html( ucfirst( $day ) ); ?></label>
						<input type="time" name="weekly_availability[<?php echo esc_attr( $day ); ?>][from]" value="<?php echo esc_attr( $location->weekly_availability[$day]['from'] ?? '' ); ?>">
						<input type="time" name="weekly_availability[<?php echo esc_attr( $day ); ?>][to]" value="<?php echo esc_attr( $location->weekly_availability[$day]['to'] ?? '' ); ?>">
					</div>
				<?php endforeach; ?>
			</div>
			<div id="location-days-off" class="tab-content" style="display:none;">
				<div class="days-off-list">
					<?php if ( ! empty( $location->days_off ) ) : ?>
						<?php foreach ( $location->days_off as $index => $day ) : ?>
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