<?php
/**
 * The core plugin class.
 *
 * This is used to define admin-specific hooks and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 */
class ANUIWP_Approve_New_User_Main {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power the plugin.
	 */
	protected $loader;

	/**
	 * The current settings of the plugin.
	 */
	protected $settings;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct( $settings = array() ) {
		$this->settings = $settings;

		$this->load_dependencies();
		$this->user_list();
		$this->define_admin_options();
		$this->define_admin_hooks();
		$this->define_global_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 */
	public function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-global-functions.php';// global functions
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-email-tags.php';	    // defining email tags
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-messages.php';	    // defining messages
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-loader.php';	        // actions and filters

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/admin-notifications.php';	    // admin notification settings
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/general-settings.php';	    // general settings
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/registration-settings.php';	// registration settings
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/user-notifications.php';	    // user notifications
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-options.php';	    // admin options page actions

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin.php';	            // users menu page actions
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/user-list.php';	    // users menu page actions
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-global-hooks.php';	// global hooks
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-memberpress.php';	    // global hooks

		$this->loader = new ANUIWP_Loader();
	}

	/**
	 * Register all of the hooks related to the users list area functionality of the plugin.
	 */
	public function user_list() {
		$plugin_user_list = new ANUIWP_User_List( $this->settings );

		// Actions
		$this->loader->add_action( 'load-users.php', $plugin_user_list, 'update_action' );
		$this->loader->add_action( 'restrict_manage_users', $plugin_user_list, 'status_filter', 10, 1 );

		$this->loader->add_action( 'pre_user_query', $plugin_user_list, 'filter_by_status' );

        $this->loader->add_action( 'load-users.php', $plugin_user_list, 'bulk_action' );

		// $this->loader->add_action( 'init', $plugin_user_list, 'deniend_user' );

		$this->loader->add_action( 'show_user_profile', $plugin_user_list, 'profile_status_field' );
		$this->loader->add_action( 'edit_user_profile', $plugin_user_list, 'profile_status_field' );
		$this->loader->add_action( 'edit_user_profile_update', $plugin_user_list, 'save_profile_status_field' );
		$this->loader->add_action( 'admin_menu', $plugin_user_list, 'pending_users_bubble', 999 );

		// Filters
		$this->loader->add_filter( 'user_row_actions', $plugin_user_list, 'user_table_actions', 10, 2 );
		$this->loader->add_filter( 'manage_users_columns', $plugin_user_list, 'add_column' );
		$this->loader->add_filter( 'manage_users_custom_column', $plugin_user_list, 'status_column', 10, 3 );
	}

    /**
     * Defining admin options
     */
    public function define_admin_options() {
        global $pagenow;

        $general_settings = new ANUIWP_General_Settings_Hooks();
        $registration_settings = new ANUIWP_Registration_Settings_Hooks();
        $admin_notifications_settings = new ANUIWP_Admin_Notifications_Settings_Hooks();
        $user_notifications_settings = new ANUIWP_User_Notifications_Hooks();

        if(isset($pagenow) && $pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == "anuiwp-setting") {
            $tab = (isset($_GET['tab']) && !empty($_GET['tab'])) ? $_GET['tab'] : null;

            if ($tab === null) {
                $this->loader->add_action( 'admin_init', $general_settings, 'general_settings' ); // General Settings
            }

            if ($tab === "anuiwp-registration-settings") {
                $this->loader->add_action( 'admin_init', $registration_settings, 'registration_settings' ); // Registration Settings
            }

            if ($tab === "anuiwp-admin-notifications") {
                $this->loader->add_action( 'admin_init', $admin_notifications_settings, 'admin_notifications_settings' ); // Admin Notifications
            }

            if ($tab === "anuiwp-user-notifications") {
                $this->loader->add_action( 'admin_init', $user_notifications_settings, 'user_notifications_settings' ); // User Notifications
            }
        }

        $plugin_admin_options_hooks = new ANUIWP_Admin_Options_Hooks( $this->settings );

        $this->loader->add_action( 'admin_menu', $plugin_admin_options_hooks, 'admin_menu_page' );  // Approve New User Menu Page
    }

    /**
     * Defining admin hooks
     */
    public function define_admin_hooks() {
        $plugin_admin_hooks = new ANUIWP_Admin_Hooks( $this->settings );

        $this->loader->add_filter("plugin_action_links_approve-new-user/approve-new-user.php", $plugin_admin_hooks, 'plugin_add_settings_link');

        // Admin init
        $this->loader->add_action( 'admin_init', $plugin_admin_hooks, 'process_user_data' );

        // Admin screen handled
        $this->loader->add_action( 'admin_footer-users.php', $plugin_admin_hooks, 'admin_footer' );
        $this->loader->add_action( 'admin_notices', $plugin_admin_hooks, 'admin_notices' );
        $this->loader->add_action( 'rightnow_end', $plugin_admin_hooks, 'dashboard_stats' );

        // Enqueue scripts
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin_hooks, 'admin_scripts' );
    }

	/**
	 * Defining global hooks
	 */
	public function define_global_hooks() {
		$plugin_global_hooks = new ANUIWP_Init( $this->settings );

		// Delete the transient storing all of the user statuses
		$this->loader->add_action( 'user_register', $plugin_global_hooks, 'delete_approve_new_user_transient', 11 );
		$this->loader->add_action( 'anuiwp_approve_user', $plugin_global_hooks, 'delete_approve_new_user_transient', 11 );
		$this->loader->add_action( 'anuiwp_deny_user', $plugin_global_hooks, 'delete_approve_new_user_transient', 11 );
		$this->loader->add_action( 'deleted_user', $plugin_global_hooks, 'delete_approve_new_user_transient' );

		$this->loader->add_action( 'register_post', $plugin_global_hooks, 'create_new_user', 10, 3 );  // When user register disable by default mail of WP and send mail to user to tell "account created please wait for approval"
		$this->loader->add_action( 'woocommerce_created_customer', $plugin_global_hooks, 'anuiwp_welcome_email_woo_new_user' );  // In woocommerce send mail to user to tell "Your registration is pending for approval"
		$this->loader->add_action( 'lostpassword_post', $plugin_global_hooks, 'lost_password', 99,2);   // Only give a user their password if they have been approved
		$this->loader->add_action( 'user_register', $plugin_global_hooks, 'add_user_status' );  // Update user status if admin create from admin
		$this->loader->add_action( 'user_register', $plugin_global_hooks, 'request_admin_approval_email_2' );   //  Send an email to the admin to request approval
		$this->loader->add_action( 'anuiwp_approve_user', $plugin_global_hooks, 'approve_user' );   //  Send email to user after Admin approval of user
		$this->loader->add_action( 'anuiwp_deny_user', $plugin_global_hooks, 'deny_user' );    // Send email to user to notify user of denial by admin
		$this->loader->add_action( 'anuiwp_deny_user', $plugin_global_hooks, 'update_deny_status' );    // Update user status when denying user by admin
		$this->loader->add_action( 'admin_init', $plugin_global_hooks, 'verify_settings' );   // Show admin notice if the membership setting is turned off.
		$this->loader->add_action( 'wp_login', $plugin_global_hooks, 'login_user', 10, 2 );   // After a user successfully logs in, record in user meta. This will only be recorded one time. The password will not be reset after a successful login.

		// Filters
		$this->loader->add_filter( 'wp_authenticate_user', $plugin_global_hooks, 'authenticate_user' );   // authenticate user when sign in if user have valid  status
		$this->loader->add_filter( 'registration_errors', $plugin_global_hooks, 'show_user_pending_message', 99 );   // Display a message to the user after they have registered
		$this->loader->add_filter( 'login_message', $plugin_global_hooks, 'welcome_user' );     // Add message to login page saying registration is required
		$this->loader->add_filter( 'anuiwp_validate_status_update', $plugin_global_hooks, 'validate_status_update', 10, 3 );
		$this->loader->add_filter( 'shake_error_codes', $plugin_global_hooks, 'failure_shake' );  // Add error codes to shake the login form on failure
		$this->loader->add_filter( 'woocommerce_registration_auth_new_customer', $plugin_global_hooks, 'disable_woo_auto_login' );  // Disable auto login for WooCommerce

		// Actions
		$this->loader->add_action( 'woocommerce_checkout_order_processed', $plugin_global_hooks, 'disable_woo_auto_login_on_checkout', 10, 0);  // Disable auto login on WooCommerce checkout
		$this->loader->add_action( 'init', $plugin_global_hooks, 'update_admin_notice');    // Update admin notice settings if clicks on hide.
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/** ------------------------------------------------- */
	/**
     * Get the status of a user.
     */
    public function get_user_status( $user_id )
    {
        $user_status = get_user_meta( $user_id, 'anuiwp_user_status', true );
        if ( empty($user_status) ) {
            $user_status = 'approved';
        }
        return $user_status;
    }

	/**
     * Update the status of a user. The new status must be either 'approve' or 'deny'.
     */
    public function update_user_status( $user, $status )
    {
        $user_id = absint( $user );
        if ( !$user_id ) {
            return false;
        }
        if ( !in_array( $status, array( 'approve', 'deny' ) ) ) {
            return false;
        }
        $do_update = apply_filters(
            'anuiwp_validate_status_update',
            true,
            $user_id,
            $status
        );
        if ( !$do_update ) {
            return false;
        }
        // where it all happens
        do_action( 'anuiwp_' . $status . '_user', $user_id );
        do_action( 'anuiwp_user_status_update', $user_id, $status );
        //update user count
        $user_statuses = $this->_get_user_statuses();
        update_option('anuiwp_users_count',$user_statuses);

        return true;
    }

	/**
     * Get the valid statuses. Anything outside of the returned array is an invalid status.
     */
    public function get_valid_statuses()
    {
        return array( 'pending', 'approved', 'denied' );
    }

	// updated function for user count fix
    public function _get_user_statuses($count = true) {
        global $wpdb;
        $statuses = array();

        foreach ( $this->get_valid_statuses() as $status ) {
            $query = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->users} u LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id WHERE um.meta_key = %s AND um.meta_value = %s", 'anuiwp_user_status', $status );

            $total = $wpdb->get_var( $query );

            if ( $count === true ) {
                $statuses[$status] = $total;
            } else {
                // If count is false, return the list of users
                $user_query = new WP_User_Query( array(
                    'meta_key' => 'anuiwp_user_status',
                    'meta_value' => $status,
                    ) );
                $statuses[$status] = $user_query->get_results();

                if(!empty($statuses['denied'])){
                    $user_id  = $statuses[$status][0]->ID;

                    // echo '<pre>'; print_r( $statuses[$status] ); echo '</pre>';
                    // $statuses[$status] = array();
                    // echo '<pre>'; print_r( $statuses[$status] ); echo '</pre>';
                }
            }
        }
        return $statuses;
    }

	/**
     * Get a list of statuses with a count of users using WPQuery not transient
     */
    public function _get_users_by_status( $count = true, $status = "" )
    {
        $paged = isset($_REQUEST['paged'] ) && !empty($_REQUEST['paged'] ) ? absint($_REQUEST['paged'])  : 1;

        $statuses = array();
        foreach ( $this->get_valid_statuses() as $status ) {
            // Query the users table
            if ( $status != 'approved' ) {

                // Query the users table
                $query = array(
                    'meta_key'    => 'anuiwp_user_status',
                    'meta_value'  => $status,
                    'count_total' => true,
                    'number'      => 15,
                    'paged'       => $paged,
                );
            } else {

                // Get all approved users and any user without a status
                $query = array(
                    'meta_query'  => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'anuiwp_user_status',
                        'value'   => 'approved',
                        'compare' => '=',
                    ),
                    array(
                        'key'     => 'anuiwp_user_status',
                        'value'   => '',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
                'count_total' => true,
                'number'      => 15,
                'paged'       =>$paged,
                );
            }

            $wp_user_search = new WP_User_Query( $query );
            if ( $count === true ) {
                $statuses[$status] = $wp_user_search->get_total();
            } else {
                $statuses[$status] = $wp_user_search->get_results();
            }
        }
        return $statuses;
    }

	/**
     * Get a list of statuses with a count of users with that status
     */
    public function get_count_of_user_statuses()
    {
        $user_statuses = get_option( 'anuiwp_user_statuses_count',array());
        if ( empty($user_statuses)) {
            $user_statuses = $this->_get_user_statuses();
            update_option('anuiwp_user_statuses_count',$user_statuses );
        }
        return $user_statuses;
    }

	/**
     * Update users statuses count
     *
     */
    public function update_users_statuses_count($new_status,$user_id)
    {
        $old_status = get_user_meta( $user_id, 'anuiwp_user_status',true);
        if( $old_status == $new_status ){return;}

        $user_statuses = get_option( 'anuiwp_user_statuses_count',array());

        if(empty($user_statuses))
        {
            $user_statuses = $this->_get_user_statuses();
        }

        foreach ( $this->get_valid_statuses() as $status ) {

            if(isset($user_statuses[$status]) && $old_status == $status)
            {
                $count = $user_statuses[$status];
                $user_statuses[$status] = $count-1;
            }elseif(isset($user_statuses[$status]) && $new_status == $status){
                $count=$user_statuses[$status];
                $user_statuses[$status]=$count+1;
            }
        }
        update_option( 'anuiwp_user_statuses_count', $user_statuses);

    }
}