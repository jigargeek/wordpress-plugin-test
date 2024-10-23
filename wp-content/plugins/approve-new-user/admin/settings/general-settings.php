<?php
class ANUIWP_General_Settings_Hooks {

	public function __construct(){       
		add_action('admin_init', array($this, 'general_settings'));
	}

	function general_settings_callback() { ?>
		<form action="options.php" method="post">

			<?php settings_fields('anuiwp-general-options'); ?>

			<div class="anuiwp-section">				
				<?php do_settings_sections('anuiwp_general_settings_section'); ?>
			</div>

			<?php
			submit_button('Save Settings');
			?>
		</form>
		<?php
	}
    
	/**
	 * Register general settings
	 */
	public function general_settings() {
		register_setting('anuiwp-general-options', 'anuiwp_general_options', array($this, 'sanitize_settings'));

		/** General settings section start */
		add_settings_section(
			'anuiwp_general_setting',
			__('General Settings', 'approve-new-user'),
			array(),
			'anuiwp_general_settings_section'
		);

		add_settings_field(
			'hide_dashboard_stats',
			__('Hide Dashboard Stats', 'approve-new-user'),
			array($this, 'switch_field_html'),
			'anuiwp_general_settings_section',
			'anuiwp_general_setting',
			[
				'label_for'     => 'hide_dashboard_stats',
				'description'   => "Remove this plugin's stats from the admin dashboard."
			]
		);

		add_settings_field(
			'change_the_sender_email',
			__('Change the Admin/Sender Email', 'approve-new-user'),
			array($this, 'text_field_html'),
			'anuiwp_general_settings_section',
			'anuiwp_general_setting',
			[
				'label_for' => 'change_the_sender_email',
				'placeholder' => get_option( 'admin_email' ),
				'description'   => "Change the admin/sender Email."
			]
		);
	}

	public function switch_field_html($args){
		$anuiwp_general_options = anuiwp_general_options();
		$value = isset($anuiwp_general_options[$args['label_for']]) ? $anuiwp_general_options[$args['label_for']] : '';
		?>
		<label class="anuiwp-switch">
			<input type="checkbox" class="anuiwp-checkbox" name="anuiwp_general_options[<?php esc_attr_e( $args['label_for'] ); ?>]" id="<?php esc_attr_e( $args['label_for'] ); ?>" value="on" <?php if($value == "on"){ _e('checked'); } ?>>
			<span class="anuiwp-slider anuiwp-round"></span>
		</label>
		<p class="anuiwp-input-note"><?php esc_attr_e($args['description'],'approve-new-user') ?></p>
		<?php
	}

	public function text_field_html($args){
		$anuiwp_general_options = anuiwp_general_options();
		$value = isset($anuiwp_general_options[$args['label_for']]) ? $anuiwp_general_options[$args['label_for']] : '';
		?>
		<input type="text" name="anuiwp_general_options[<?php esc_attr_e( $args['label_for'] ); ?>]" id="<?php esc_attr_e( $args['label_for'] ); ?>" value="<?php _e($value); ?>" placeholder="<?php esc_attr_e( isset($args['placeholder']) ? $args['placeholder'] : "" ); ?>">
		<p class="anuiwp-input-note"><?php esc_attr_e($args['description'],'approve-new-user') ?></p>
		<?php
	}

	public function sanitize_settings($input) {
		$new_input = array();

		if (isset($input['hide_dashboard_stats'])) {
			$new_input['hide_dashboard_stats'] = sanitize_text_field($input['hide_dashboard_stats']);
		}

		if (isset($input['change_the_sender_email'])) {
			$new_input['change_the_sender_email'] = sanitize_text_field($input['change_the_sender_email']);
		}

		return $new_input;
	}
}