<?php
class ANUIWP_Admin_Notifications_Settings_Hooks {

	public function __construct(){       
		add_action('admin_init', array($this, 'admin_notifications_settings'));
	}

	function admin_notifications_callback() {?>
		<form action="options.php?tab=wspc-product-description" method="post">

			<?php settings_fields('anuiwp-admin-notifications-options'); ?>


			<div class="anuiwp-section">
				<?php do_settings_sections('anuiwp_admin_notifications_settings_section'); ?>
			</div>

			<div class="anuiwp-section">
				<?php do_settings_sections('anuiwp_admin_notification_email_section'); ?>
			</div>

			<?php
			submit_button('Save Settings');
			?>
		</form>
		<?php
	}

	/**
	 * Register admin notifications settings
	 */
	public function admin_notifications_settings() {
		register_setting('anuiwp-admin-notifications-options', 'anuiwp_admin_notifications_options', array($this, 'sanitize_settings'));

		/** Notification options section start */
		add_settings_section(
			'anuiwp_admin_notifications_setting',
			__('Notification options', 'approve-new-user'),
			array(),
			'anuiwp_admin_notifications_settings_section'
		);

		add_settings_field(
			'send_notifications_emails_to_all_admin',
			__('Send notification emails to all admins', 'approve-new-user'),
			array($this, 'send_notifications_emails_html'),
			'anuiwp_admin_notifications_settings_section',
			'anuiwp_admin_notifications_setting',
			[
				'label_for' => 'send_notifications_emails_to_all_admin',
			]
		);

		add_settings_field(
			'dont_send_notifications_to_admin',
			__("Don't send notification emails to current site admin", 'approve-new-user'),
			array($this, 'dont_send_notifications_to_admin_html'),
			'anuiwp_admin_notifications_settings_section',
			'anuiwp_admin_notifications_setting',
			[
				'label_for' => 'dont_send_notifications_to_admin',
			]
		);

		/** Notification Emails section start */
		add_settings_section(
			'anuiwp_admin_notification_email',
			__('Notification Emails', 'approve-new-user'),
			array(),
			'anuiwp_admin_notification_email_section'
		);

		add_settings_field(
			'admin_notification_subject',
			__('Notification Email Subject', 'approve-new-user'),
			array($this, 'text_field_html'),
			'anuiwp_admin_notification_email_section',
			'anuiwp_admin_notification_email',
			[
				'label_for' => 'admin_notification_subject',
			]
		);
		
		add_settings_field(
			'admin_notification_message',
			__('Notification Email Message', 'approve-new-user'),
			array($this, 'textarea_field_html'),
			'anuiwp_admin_notification_email_section',
			'anuiwp_admin_notification_email',
			[
				'label_for' => 'admin_notification_message',
			]
		);
	}

	public function send_notifications_emails_html($args){
		$anuiwp_admin_notifications_options = anuiwp_admin_notifications_options();
		$value = isset($anuiwp_admin_notifications_options[$args['label_for']]) ? $anuiwp_admin_notifications_options[$args['label_for']] : '';
		?>
		<label class="anuiwp-switch">
			<input type="checkbox" class="anuiwp-checkbox" name="anuiwp_admin_notifications_options[<?php esc_attr_e( $args['label_for'] ); ?>]" id="<?php esc_attr_e( $args['label_for'] ); ?>" value="on" <?php if($value == "on"){ _e('checked'); } ?>>
			<span class="anuiwp-slider anuiwp-round"></span>
		</label>
		<p class="anuiwp-input-note"><?php esc_html_e('By default, only the site admin will be notified when a user is awaiting approval. Checking this option will send the notification to all users with admin access.','approve-new-user'); ?></p>
		<?php
	}

	public function dont_send_notifications_to_admin_html($args){
		$anuiwp_admin_notifications_options = anuiwp_admin_notifications_options();
		$value = isset($anuiwp_admin_notifications_options[$args['label_for']]) ? $anuiwp_admin_notifications_options[$args['label_for']] : '';
		?>
		<label class="anuiwp-switch">
			<input type="checkbox" class="anuiwp-checkbox" name="anuiwp_admin_notifications_options[<?php esc_attr_e( $args['label_for'] ); ?>]" id="<?php esc_attr_e( $args['label_for'] ); ?>" value="on" <?php if($value == "on"){ _e('checked'); } ?>>
			<span class="anuiwp-slider anuiwp-round"></span>
		</label>
		<p class="anuiwp-input-note"><?php echo get_option( 'admin_email' ); ?></p>
		<?php
	}

	public function text_field_html($args){
		$anuiwp_admin_notifications_options = anuiwp_admin_notifications_options();
		$value = isset($anuiwp_admin_notifications_options[$args['label_for']]) ? $anuiwp_admin_notifications_options[$args['label_for']] : '';
		?>
		<input type="text" name="anuiwp_admin_notifications_options[<?php esc_attr_e( $args['label_for'] ); ?>]" id="<?php esc_attr_e( $args['label_for'] ); ?>" value="<?php _e($value); ?>">
		<?php
	}
	
	public function textarea_field_html($args){
		$anuiwp_admin_notifications_options = anuiwp_admin_notifications_options();
		$value = isset($anuiwp_admin_notifications_options[$args['label_for']]) ? $anuiwp_admin_notifications_options[$args['label_for']] : '';
		?>
		<textarea name="anuiwp_admin_notifications_options[<?php esc_attr_e( $args['label_for'] ); ?>]" id="<?php esc_attr_e( $args['label_for'] ); ?>" class="anuiwp_content"><?php echo wp_unslash($value); ?></textarea>
		<p class="anuiwp-input-note"><?php esc_html_e('This message is sent to the site admin when a user registers for the site. Customizations can be made to the message above using the following email tags:','approve-new-user'); ?></p>
		<br>
		<p class="anuiwp-input-note"><strong>{username}</strong> - <?php esc_html_e("The user's username on the site as well as the Username label","approve-new-user"); ?></p>
		<p class="anuiwp-input-note"><strong>{user_email}</strong> - <?php esc_html_e("The user's email address","approve-new-user"); ?></p>
		<p class="anuiwp-input-note"><strong>{sitename}</strong> - <?php esc_html_e("Your site name","approve-new-user"); ?></p>
		<p class="anuiwp-input-note"><strong>{site_url}</strong> - <?php esc_html_e("Your site URL","approve-new-user"); ?></p>
		<p class="anuiwp-input-note"><strong>{admin_approve_url}</strong> - <?php esc_html_e("The URL to approve/deny users","approve-new-user"); ?></p>
		<p class="anuiwp-input-note"><strong>{login_url}</strong> - <?php esc_html_e("The URL to login to the site","approve-new-user"); ?></p>
		<p class="anuiwp-input-note"><strong>{reset_password_url}</strong> - <?php esc_html_e("The URL for a user to set/reset their password","approve-new-user"); ?></p>
		<p class="anuiwp-input-note"><strong>{password}</strong> - <?php esc_html_e("Generates the password for the user to add to the email","approve-new-user"); ?></p>
		<?php
	}

	public function sanitize_settings($input) {
		$new_input = array();

		if (isset($input['send_notifications_emails_to_all_admin'])) {
			$new_input['send_notifications_emails_to_all_admin'] = sanitize_text_field($input['send_notifications_emails_to_all_admin']);
		}

		if (isset($input['dont_send_notifications_to_admin'])) {
			$new_input['dont_send_notifications_to_admin'] = sanitize_text_field($input['dont_send_notifications_to_admin']);
		}

		if (isset($input['admin_notification_subject'])) {
			$new_input['admin_notification_subject'] = sanitize_text_field($input['admin_notification_subject']);
		}

		if (isset($input['admin_notification_message'])) {
			$new_input['admin_notification_message'] = $input['admin_notification_message'];
		}

		return $new_input;
	}
}