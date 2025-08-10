<?php
/**
 * Notifications admin page.
 *
 * @package Medsol_Appointments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render notifications page.
 */
function medsol_notifications_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to access this page.', 'medsol-appointments' ) );
	}

	// Register settings.
	register_setting( 'medsol_notifications', 'medsol_appointments_notifications' );

	// Sections and fields.
	add_settings_section( 'medsol_notifications_general', __( 'General', 'medsol-appointments' ), null, 'medsol_notifications' );
	add_settings_field( 'medsol_enable_notifications', __( 'Enable Notifications', 'medsol-appointments' ), 'medsol_enable_notifications_callback', 'medsol_notifications', 'medsol_notifications_general' );

	// Email sections (one per recipient for simplicity).
	$recipients = array( 'customer' => __( 'To Customer', 'medsol-appointments' ), 'employee' => __( 'To Employee', 'medsol-appointments' ), 'admin' => __( 'To Admin', 'medsol-appointments' ) );
	$templates = array(
		'pending'   => __( 'Appointment Pending', 'medsol-appointments' ),
		'approved'  => __( 'Appointment Approved', 'medsol-appointments' ),
		'declined'  => __( 'Appointment Declined', 'medsol-appointments' ),
		'canceled'  => __( 'Appointment Canceled', 'medsol-appointments' ),
		'reminder'  => __( 'Appointment Reminder', 'medsol-appointments' ),
		'follow_up' => __( 'Appointment Follow Up', 'medsol-appointments' ),
	);

	foreach ( $recipients as $key => $label ) {
		add_settings_section( "medsol_email_{$key}", $label, null, 'medsol_notifications_email' );
		foreach ( $templates as $tmpl_key => $tmpl_label ) {
			add_settings_field( "medsol_email_{$key}_{$tmpl_key}_subject", "{$tmpl_label} Subject", function() use ( $key, $tmpl_key ) { medsol_template_field_callback( $key, $tmpl_key, 'subject' ); }, 'medsol_notifications_email', "medsol_email_{$key}" );
			add_settings_field( "medsol_email_{$key}_{$tmpl_key}_body", "{$tmpl_label} Body", function() use ( $key, $tmpl_key ) { medsol_template_field_callback( $key, $tmpl_key, 'body' ); }, 'medsol_notifications_email', "medsol_email_{$key}" );
		}
	}

	$options = get_option( 'medsol_appointments_notifications', array() );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Notifications', 'medsol-appointments' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'medsol_notifications' ); ?>
			<h2 class="nav-tab-wrapper">
				<a href="#email" class="nav-tab nav-tab-active"><?php esc_html_e( 'Email', 'medsol-appointments' ); ?></a>
				<a href="#sms" class="nav-tab"><?php esc_html_e( 'SMS', 'medsol-appointments' ); ?></a>
				<a href="#whatsapp" class="nav-tab"><?php esc_html_e( 'WhatsApp', 'medsol-appointments' ); ?></a>
			</h2>
			<div id="email" class="tab-content">
				<?php do_settings_sections( 'medsol_notifications' ); // General enable. ?>
				<h3><?php esc_html_e( 'Email Templates', 'medsol-appointments' ); ?></h3>
				<div class="nav-sub-tab-wrapper">
					<?php foreach ( $recipients as $key => $label ) : ?>
						<a href="#email-<?php echo esc_attr( $key ); ?>" class="nav-sub-tab"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</div>
				<?php foreach ( $recipients as $key => $label ) : ?>
					<div id="email-<?php echo esc_attr( $key ); ?>" class="sub-tab-content" style="display:none;">
						<?php do_settings_sections( "medsol_email_{$key}" ); ?>
						<p><?php esc_html_e( 'Supported Shortcodes:', 'medsol-appointments' ); ?> <?php echo esc_html( implode( ', ', medsol_appointments_shortcodes() ) ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
			<div id="sms" class="tab-content" style="display:none;">
				<p><?php esc_html_e( 'Content for later', 'medsol-appointments' ); ?></p>
			</div>
			<div id="whatsapp" class="tab-content" style="display:none;">
				<p><?php esc_html_e( 'Content for later', 'medsol-appointments' ); ?></p>
			</div>
			<?php submit_button(); ?>
		</form>
	</div>
	<script>
		// Tab switching JS (simple, no jQuery dependency beyond core).
		document.querySelectorAll('.nav-tab').forEach(tab => {
			tab.addEventListener('click', e => {
				e.preventDefault();
				document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('nav-tab-active'));
				tab.classList.add('nav-tab-active');
				document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
				document.querySelector(tab.getAttribute('href')).style.display = 'block';
			});
		});
		document.querySelectorAll('.nav-sub-tab').forEach(subTab => {
			subTab.addEventListener('click', e => {
				e.preventDefault();
				document.querySelectorAll('.nav-sub-tab').forEach(t => t.classList.remove('nav-tab-active'));
				subTab.classList.add('nav-tab-active');
				document.querySelectorAll('.sub-tab-content').forEach(c => c.style.display = 'none');
				document.querySelector(subTab.getAttribute('href')).style.display = 'block';
			});
		});
		// Show first sub-tab by default.
		document.querySelector('.nav-sub-tab').click();
	</script>
	<?php
}

/**
 * Enable notifications callback.
 */
function medsol_enable_notifications_callback() {
	$options = get_option( 'medsol_appointments_notifications', array() );
	$checked = $options['enable'] ?? 0;
	echo '<input type="checkbox" name="medsol_appointments_notifications[enable]" value="1" ' . checked( 1, $checked, false ) . '>';
}

/**
 * Template field callback.
 *
 * @param string $recipient Recipient key.
 * @param string $template Template key.
 * @param string $field Field (subject/body).
 */
function medsol_template_field_callback( $recipient, $template, $field ) {
	$options = get_option( 'medsol_appointments_notifications', array() );
	$value = $options['email'][$recipient][$template][$field] ?? '';
	if ( 'subject' === $field ) {
		echo '<input type="text" name="medsol_appointments_notifications[email][' . esc_attr( $recipient ) . '][' . esc_attr( $template ) . '][subject]" value="' . esc_attr( $value ) . '" style="width:100%;">';
	} else {
		echo '<textarea name="medsol_appointments_notifications[email][' . esc_attr( $recipient ) . '][' . esc_attr( $template ) . '][body]" rows="10" style="width:100%;">' . esc_textarea( $value ) . '</textarea>';
		echo '<p>' . esc_html__( 'Use HTML for formatting.', 'medsol-appointments' ) . '</p>';
	}
}