<?php
class ANUIWP_User_Notifications_Hooks {

	public function __construct(){
		add_action('admin_init', array($this, 'user_notifications_settings'));
	}

	function user_notifications_callback() { ?>
		<form action="options.php?tab=wspc-product-description" method="post">
			<?php settings_fields('anuiwp-user-notifications-options'); ?>

			<div class="anuiwp-section">
				<?php do_settings_sections('anuiwp_user_approve_notification_email_section'); ?>
			</div>

			<div class="anuiwp-section">
				<?php do_settings_sections('anuiwp_user_deny_notification_email_section'); ?>
			</div>

			<div class="anuiwp-section">
				<?php do_settings_sections('anuiwp_user_welcome_notification_email_section'); ?>
			</div>

			<?php
			submit_button('Save Settings');
			?>
		</form>
		<?php
	}

	/**
	 * Register user notifications settings
	 */
	public function user_notifications_settings() {
		register_setting('anuiwp-user-notifications-options', 'anuiwp_user_notifications_options', array($this, 'sanitize_settings'));

		/** Approve Notification section start */
		add_settings_section(
			'anuiwp_user_approve_notification_email',
			__('Approve Notification', 'approve-new-user'),
			array(),
			'anuiwp_user_approve_notification_email_section'
		);

		add_settings_field(
			'user_approve_notification_subject',
			__('Approve notification subject', 'approve-new-user'),
			array($this, 'text_field_html'),
			'anuiwp_user_approve_notification_email_section',
			'anuiwp_user_approve_notification_email',
			[
				'label_for' => 'user_approve_notification_subject',
			]
		);

		add_settings_field(
			'user_approve_notification_message',
			__('Approve notification message', 'approve-new-user'),
			array($this, 'user_approve_notification_message_html'),
			'anuiwp_user_approve_notification_email_section',
			'anuiwp_user_approve_notification_email',
			[
				'label_for' => 'user_approve_notification_message',
			]
		);

		/** Deny Notification section start */
		add_settings_section(
			'anuiwp_user_deny_notification_email',
			__('Deny Notification', 'approve-new-user'),
			array(),
			'anuiwp_user_deny_notification_email_section'
		);

		add_settings_field(
			'suppress_user_denial_message',
			__("Suppress denial message", 'approve-new-user'),
			array($this, 'switch_field_html'),
			'anuiwp_user_deny_notification_email_section',
			'anuiwp_user_deny_notification_email',
			[
				'label_for' => 'suppress_user_denial_message',
			]
		);

		add_settings_field(
			'user_deny_notification_subject',
			__('Deny notification subject', 'approve-new-user'),
			array($this, 'text_field_html'),
			'anuiwp_user_deny_notification_email_section',
			'anuiwp_user_deny_notification_email',
			[
				'label_for' => 'user_deny_notification_subject',
			]
		);

		add_settings_field(
			'user_deny_notification_message',
			__('Deny notification message', 'approve-new-user'),
			array($this, 'user_deny_notification_message_html'),
			'anuiwp_user_deny_notification_email_section',
			'anuiwp_user_deny_notification_email',
			[
				'label_for' => 'user_deny_notification_message',
			]
		);

		/** Welcome Notification section start */
		add_settings_section(
			'anuiwp_user_welcome_notification_email',
			__('Welcome Notification', 'approve-new-user'),
			array(),
			'anuiwp_user_welcome_notification_email_section'
		);

		add_settings_field(
			'user_welcome_email_status',
			__("User Welcome Email", 'approve-new-user'),
			array($this, 'switch_field_html'),
			'anuiwp_user_welcome_notification_email_section',
			'anuiwp_user_welcome_notification_email',
			[
				'label_for' => 'user_welcome_email_status',
			]
		);

		add_settings_field(
			'user_welcome_notification_subject',
			__('Welcome notification subject', 'approve-new-user'),
			array($this, 'text_field_html'),
			'anuiwp_user_welcome_notification_email_section',
			'anuiwp_user_welcome_notification_email',
			[
				'label_for' => 'user_welcome_notification_subject',
			]
		);

		add_settings_field(
			'user_welcome_notification_message',
			__('Welcome notification message', 'approve-new-user'),
			array($this, 'user_welcome_notification_message_html'),
			'anuiwp_user_welcome_notification_email_section',
			'anuiwp_user_welcome_notification_email',
			[
				'label_for' => 'user_welcome_notification_message',
			]
		);
	}

	public function switch_field_html($args){
		$anuiwp_user_notifications_options = anuiwp_user_notifications_options();
		$value = isset($anuiwp_user_notifications_options[$args['label_for']]) ? $anuiwp_user_notifications_options[$args['label_for']] : '';
		?>
		<label class="anuiwp-switch">
			<input type="checkbox" class="anuiwp-checkbox" name="anuiwp_user_notifications_options[<?php esc_attr_e( $args['label_for'] ); ?>]" id="<?php esc_attr_e( $args['label_for'] ); ?>" value="on" <?php if($value == "on"){ _e('checked'); } ?>>
			<span class="anuiwp-slider anuiwp-round"></span>
		</label>
		<?php
	}

	public function text_field_html($args){
		$anuiwp_user_notifications_options = anuiwp_user_notifications_options();
		$value = isset($anuiwp_user_notifications_options[$args['label_for']]) ? $anuiwp_user_notifications_options[$args['label_for']] : '';
		?>
		<input type="text" name="anuiwp_user_notifications_options[<?php esc_attr_e( $args['label_for'] ); ?>]" id="<?php esc_attr_e( $args['label_for'] ); ?>" value="<?php _e($value); ?>">
		<?php
	}

	public function user_approve_notification_message_html($args){
		$anuiwp_user_notifications_options = anuiwp_user_notifications_options();
		$value = isset($anuiwp_user_notifications_options[$args['label_for']]) ? $anuiwp_user_notifications_options[$args['label_for']] : '';
		?>
		<textarea name="anuiwp_user_notifications_options[<?php esc_attr_e( $args['label_for'] ); ?>]" id="<?php esc_attr_e( $args['label_for'] ); ?>" class="anuiwp_content"><?php echo wp_unslash($value); ?></textarea>
		<p class="anuiwp-input-note"><?php esc_html_e('This message is sent to users once they are approved. Customizations can be made to the message above using the following email tags:','approve-new-user'); ?></p>
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

	public function user_deny_notification_message_html($args){
		$anuiwp_user_notifications_options = anuiwp_user_notifications_options();
		$value = isset($anuiwp_user_notifications_options[$args['label_for']]) ? $anuiwp_user_notifications_options[$args['label_for']] : '';
		?>
		<textarea name="anuiwp_user_notifications_options[<?php esc_attr_e( $args['label_for'] ); ?>]" id="<?php esc_attr_e( $args['label_for'] ); ?>" class="anuiwp_content"><?php echo wp_unslash($value); ?></textarea>
		<p class="anuiwp-input-note"><?php esc_html_e('This message is sent to users once they are denied. Customizations can be made to the message above using the following email tags:','approve-new-user'); ?></p>
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

	public function user_welcome_notification_message_html($args){
		$anuiwp_user_notifications_options = anuiwp_user_notifications_options();

		$value = isset($anuiwp_user_notifications_options[$args['label_for']]) ? $anuiwp_user_notifications_options[$args['label_for']] : '';
		?>
		<textarea name="anuiwp_user_notifications_options[<?php esc_attr_e( $args['label_for'] ); ?>]" id="<?php esc_attr_e( $args['label_for'] ); ?>" class="anuiwp_content"><?php echo wp_unslash($value); ?></textarea>
		<p class="anuiwp-input-note"><?php esc_html_e('Users will receive Welcome emails when they are registered to the site.','approve-new-user'); ?></p>
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

		if (isset($input['user_approve_notification_subject'])) {
			$new_input['user_approve_notification_subject'] = sanitize_text_field($input['user_approve_notification_subject']);
		}

		if (isset($input['user_approve_notification_message'])) {
			$new_input['user_approve_notification_message'] = $input['user_approve_notification_message'];
		}

		if (isset($input['suppress_user_denial_message'])) {
			$new_input['suppress_user_denial_message'] = sanitize_text_field($input['suppress_user_denial_message']);
		}

		if (isset($input['user_deny_notification_subject'])) {
			$new_input['user_deny_notification_subject'] = sanitize_text_field($input['user_deny_notification_subject']);
		}

		if (isset($input['user_deny_notification_message'])) {
			$new_input['user_deny_notification_message'] = $input['user_deny_notification_message'];
		}

		if (isset($input['user_welcome_email_status'])) {
			$new_input['user_welcome_email_status'] = sanitize_text_field($input['user_welcome_email_status']);
		}

		if (isset($input['user_welcome_notification_subject'])) {
			$new_input['user_welcome_notification_subject'] = sanitize_text_field($input['user_welcome_notification_subject']);
		}

		if (isset($input['user_welcome_notification_message'])) {
			$new_input['user_welcome_notification_message'] = $input['user_welcome_notification_message'];
		}

		return $new_input;
	}
}