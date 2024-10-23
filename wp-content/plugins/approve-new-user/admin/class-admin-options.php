<?php
class ANUIWP_Admin_Options_Hooks {
    /**
	 * The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 */
	private $version;

    /**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $settings ) {
		$this->plugin_name = $settings['plugin_name'];
		$this->version = $settings['version'];
	}

    /**
     * 
     */
    public function admin_menu_page() {

        add_menu_page( 
            __( 'Approve New User', 'approve-new-user' ),
            __( 'Approve New User', 'approve-new-user' ),
            'manage_options',
            'anuiwp-menu-page',
            array($this, 'users_list_menu_page'),
            'dashicons-yes-alt',
            70
        ); 

        add_submenu_page('anuiwp-menu-page', __('Settings', 'approve-new-user'), __('Settings', 'approve-new-user'), 'manage_options', 'anuiwp-settings', array($this, 'settings_menu_page'));
    }

    /**
     * Approve New User List. Pending, Approved and Denied Users
     */
    public function users_list_menu_page() {
        include( plugin_dir_path( __DIR__ ) . 'admin/class-approve-users.php' );
    }

    /**
     * Approve New Users Settings
     */
    public function settings_menu_page() {
        $default_tab = null;
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : $default_tab;
        ?>
        <?php settings_errors(); ?>
            <div class="anuiwp-main-box">
                <div class="anuiwp-container">
                    <div class="anuiwp-header">
                        <div class="anuiwp-h1">
                            <div class="anuiwp-option-section">

                                <div class="anuiwp-tabbing-box">
                                    <ul class="anuiwp-tab-list">
                                        <li><a href="?page=anuiwp-settings" class="nav-tab <?php if ($tab === null) : ?>nav-tab-active<?php endif; ?>"><?php _e('General Settings', 'approve-new-user'); ?></a></li>
                                        <li><a href="?page=anuiwp-settings&tab=anuiwp-registration-settings" class="nav-tab <?php if ($tab === 'anuiwp-registration-settings') : ?>nav-tab-active<?php endif; ?>"><?php _e('Registration Notifications', 'approve-new-user'); ?></a></li>
                                        <li><a href="?page=anuiwp-settings&tab=anuiwp-admin-notifications" class="nav-tab <?php if ($tab === 'anuiwp-admin-notifications') : ?>nav-tab-active<?php endif; ?>"><?php _e('Admin Notifications', 'approve-new-user'); ?></a></li>
                                        <li><a href="?page=anuiwp-settings&tab=anuiwp-user-notifications" class="nav-tab <?php if ($tab === 'anuiwp-user-notifications') : ?>nav-tab-active<?php endif; ?>"><?php _e('User Notifications', 'approve-new-user'); ?></a></li>
                                    </ul>
                                </div>

                                <div class="anuiwp-tabing-option">
                                    <?php if ($tab == null) { 

                                        $general  = new ANUIWP_General_Settings_Hooks();
                                        $general->general_settings_callback();
                                    }

                                    if ($tab == "anuiwp-registration-settings") {

                                        $registration_settings  = new ANUIWP_Registration_Settings_Hooks();                                
                                        $registration_settings->registration_settings_callback();
                                    }

                                    if ($tab == "anuiwp-admin-notifications") {

                                        $admin_notifications_settings  = new ANUIWP_Admin_Notifications_Settings_Hooks();
                                        $admin_notifications_settings->admin_notifications_callback();
                                    }


                                    if ($tab == "anuiwp-user-notifications") {

                                        $user_notifications_settings  = new ANUIWP_User_Notifications_Hooks();
                                        $user_notifications_settings->user_notifications_callback();
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }
}