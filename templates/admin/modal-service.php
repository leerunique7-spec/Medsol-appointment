<?php
/**
 * Service modal template.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="medsol-off-canvas">
	<div class="medsol-off-canvas-header">
		<h2><?php echo $service ? esc_html__( 'Edit Service', 'medsol-appointments' ) : esc_html__( 'Add Service', 'medsol-appointments' ); ?></h2>
	</div>
	<div class="medsol-off-canvas-body">
		<h2 class="nav-tab-wrapper">
			<a href="#service-details" class="nav-tab nav-tab-active"><?php esc_html_e( 'Details', 'medsol-appointments' ); ?></a>
			<a href="#service-settings" class="nav-tab"><?php esc_html_e( 'Settings', 'medsol-appointments' ); ?></a>
		</h2>
		<form id="medsol-service-form">
			<input type="hidden" name="id" value="<?php echo esc_attr( $service->id ?? 0 ); ?>">
			<div id="service-details" class="tab-content">
				<label><?php esc_html_e( 'Name', 'medsol-appointments' ); ?></label>
				<input type="text" name="name" value="<?php echo esc_attr( $service->name ?? '' ); ?>">

				<label><?php esc_html_e( 'Duration (minutes)', 'medsol-appointments' ); ?></label>
				<input type="number" name="duration" value="<?php echo esc_attr( $service->duration ?? '' ); ?>">
			</div>
			<div id="service-settings" class="tab-content" style="display:none;">
				<label><?php esc_html_e( 'Slot Capacity (0 for unlimited)', 'medsol-appointments' ); ?></label>
				<input type="number" name="slot_capacity" value="<?php echo esc_attr( $service->slot_capacity ?? 0 ); ?>">

				<label><?php esc_html_e( 'Minimum Time Before Booking (hours)', 'medsol-appointments' ); ?></label>
				<input type="number" name="min_booking_time" value="<?php echo esc_attr( $service->min_booking_time ?? 0 ); ?>">
			</div>
		</form>
	</div>
	<div class="medsol-off-canvas-footer">
		<button class="button button-primary medsol-save"><?php esc_html_e( 'Save', 'medsol-appointments' ); ?></button>
		<button class="button medsol-cancel"><?php esc_html_e( 'Cancel', 'medsol-appointments' ); ?></button>
	</div>
</div>