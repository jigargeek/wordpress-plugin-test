<?php
class ANUIWP_Init {
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
     * Delete the transient storing all of the user statuses.
     */
    public function delete_approve_new_user_transient()
    {
        delete_transient( 'anuiwp_user_statuses' );
    }

    /**
     * Verify settings upon activation
     */
    public function verify_settings()
    {
        // make sure the membership setting is turned on
        if ( get_option( 'users_can_register' ) != 1 ) {
            add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        }
    }

    /**
     * Show admin notice if the membership setting is turned off.
     */
    public function admin_notices()
    {
        $user_id = get_current_user_id();
        $show_notice = get_user_meta( $user_id, 'anuiwp_approve_new_user_settings_notice' );
        // one last chance to show the update
        $show_notice = apply_filters( 'anuiwp_show_membership_notice', $show_notice ,$user_id);
        // Check that the user hasn't already clicked to ignore the message
        if ( !$show_notice ) {
            $notice_nounce=wp_create_nonce('anuiwp_notice_nounce');
            echo  '<div class="error"><p>' ;
            printf( wp_kses_post( 'The Membership setting must be turned on in order for the New User Approve to work correctly. <a href="%1$s">Update in settings</a>. | <a href="%2$s">Hide Notice</a>') , esc_url( admin_url( 'options-general.php' ) ), esc_url(add_query_arg( array(
                'approve-new-user-settings-notice' => 1,
                'notice-nounce'=>$notice_nounce,
            )) ) );
            echo  "</p></div>" ;
        }
    }

    /**
     * Admin approval of user
     */
    public function approve_user( $user_id )
    {

        $user = new WP_User( $user_id );
        wp_cache_delete( $user->ID, 'users' );
        wp_cache_delete( $user->data->user_login, 'userlogins' );
        // send email to user telling of approval
        $user_login = stripslashes( $user->data->user_login );
        $user_email = stripslashes( $user->data->user_email );
        // format the message
        $user_notifications_options = anuiwp_user_notifications_options();
        $message = (isset($user_notifications_options['user_approve_notification_message']) && !empty($user_notifications_options['user_approve_notification_message'])) ? $user_notifications_options['user_approve_notification_message'] : anuiwp_default_approve_user_message();
        $message = anuiwp_do_email_tags( $message, array(
            'context'    => 'approve_user',
            'user'       => $user,
            'user_login' => $user_login,
            'user_email' => $user_email,
        ) );
        $message = preg_replace('/<br(\s+)?\/?>/i', "\n", $message);
        $message = apply_filters( 'anuiwp_approve_new_user_message', $message, $user );
        /* translators: %s: search term */
        $subject = (isset($user_notifications_options['user_approve_notification_subject']) && !empty($user_notifications_options['user_approve_notification_subject'])) ? $user_notifications_options['user_approve_notification_subject'] : anuiwp_approve_new_user_subject();

        // send the mail
        wp_mail( $user_email, $subject, $message, $this->email_message_headers() );
        // to update statuses count
        $this->update_users_statuses_count('approved',$user_id );

        // change usermeta tag in database to approved
        update_user_meta( $user->ID, 'anuiwp_user_status', 'approved' );

        do_action( 'anuiwp_approve_new_user_after_approved', $user );
    }

    /**
     * Send email to notify user of denial.
     */
    public function deny_user( $user_id )
    {
        $user_notifications_options = anuiwp_user_notifications_options();
        $suppress_user_denial_message = (isset($user_notifications_options['suppress_user_denial_message'])) ? $user_notifications_options['suppress_user_denial_message'] : "";
        if(isset($suppress_user_denial_message) && $suppress_user_denial_message != "on") {
            $user = new WP_User( $user_id );
            // send email to user telling of denial
            $user_email = stripslashes( $user->data->user_email );
            $user_login = stripslashes( $user->data->user_login );
            // format the message
            $message = (isset($user_notifications_options['user_deny_notification_message']) && !empty($user_notifications_options['user_deny_notification_message'])) ? $user_notifications_options['user_deny_notification_message'] : anuiwp_default_deny_user_message();
            $message = anuiwp_do_email_tags( $message, array(
                'context' => 'deny_user',
                'user_email' => $user_email,
                'user_login' => $user_login
            ) );
            $message = preg_replace('/<br(\s+)?\/?>/i', "\n", $message);
            $message = apply_filters( 'anuiwp_approve_new_user_deny_user_message', $message, $user );
            /* translators: %s: search term */
            $subject = (isset($user_notifications_options['user_deny_notification_subject']) && !empty($user_notifications_options['user_deny_notification_subject'])) ? $user_notifications_options['user_deny_notification_subject'] : anuiwp_default_deny_user_subject();

            // send the mail
            wp_mail( $user_email, $subject, $message, $this->email_message_headers() );
        }
    }

    /**
     * Update user status when denying user.
     */
    public function update_deny_status( $user_id )
    {
        $user = new WP_User( $user_id );
        //to update statuses count
        $this->update_users_statuses_count('denied',$user_id );
        // change usermeta tag in database to denied
        update_user_meta( $user->ID, 'anuiwp_user_status', 'denied' );

        do_action( 'anuiwp_approve_new_user_denied', $user );
    }

    /**
     * Update admin notice settings if clicks on hide.
     */
    public function update_admin_notice()
    {
        if (isset($_GET['notice-nounce']) && wp_verify_nonce( sanitize_text_field($_GET['notice-nounce']), 'anuiwp_notice_nounce' )) {
        // if the user isn't an admin, definitely don't show the notice
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
        // update the setting for the current user
        if ( isset( $_GET['approve-new-user-settings-notice'] ) && '1' == $_GET['approve-new-user-settings-notice'] ) {
            $user_id = get_current_user_id();
            add_user_meta(
                $user_id,
                'anuiwp_approve_new_user_settings_notice',
                '1',
                true
            );

        }
        }
    }

    /**
     * After a user successfully logs in, record in user meta. This will only be recorded
     * one time. The password will not be reset after a successful login.
     */
    public function login_user( $user_login, $user = null )
    {
        if ( $user != null && is_object( $user ) ) {
            if ( !get_user_meta( $user->ID, 'anuiwp_approve_new_user_has_signed_in' ) ) {
                add_user_meta( $user->ID, 'anuiwp_approve_new_user_has_signed_in', time() );
            }
        }
    }

    /**
     * The default message that is shown to a user depending on their status
     * when trying to sign in.
     */
    public function default_authentication_message( $status )
    {
        $message = '';
        $registration_options = anuiwp_registration_options();
        if ( $status == 'pending' ) {
            $message = (isset($registration_options['pending_error_message']) && !empty($registration_options['pending_error_message'])) ? $registration_options['pending_error_message'] : anuiwp_approve_new_user_pending_error();
        } else {
            if ( $status == 'denied' ) {
                $message = (isset($registration_options['reject_error_message']) && !empty($registration_options['reject_error_message'])) ? $registration_options['reject_error_message'] : anuiwp_approve_new_user_denied_error();
            }
        }

        $message = apply_filters( 'anuiwp_approve_new_user_default_authentication_message', $message, $status );
        return $message;
    }

    /**
     * Determine if the user is good to sign in based on their status.
     */
    public function authenticate_user( $userdata )
    {
        $status = anuiwp_approve_new_user()->get_user_status( $userdata->ID );
        if ( empty($status) ) {
            // the user does not have a status so let's assume the user is good to go
            return $userdata;
        }
        $message = false;
        switch ( $status ) {
            case 'pending':
                $pending_message = $this->default_authentication_message( 'pending' );
                $message = new WP_Error( 'pending_approval', $pending_message );
                break;
            case 'denied':
                $denied_message = $this->default_authentication_message( 'denied' );
                $message = new WP_Error( 'denied_access', $denied_message );
                break;
            case 'approved':
                $message = $userdata;
                break;
        }
        return $message;
    }

    /**
     * Create a new user after the registration has been validated. Normally,
     * when a user registers, an email is sent to the user containing their
     * username and password. The email does not get sent to the user until
     * the user is approved when using the default behavior of this plugin.
     */
    public function create_new_user( $user_login, $user_email, $errors )
    {
        if ( $errors->get_error_code() ) {
            return;
        }
        // create the user
        $user_pass = wp_generate_password( 12, false );
        $user_pass = apply_filters('anuiwp_pass_create_new_user', $user_pass);
        $user_id = wp_create_user( $user_login, $user_pass, $user_email );
        if ( !$user_id ) {
            /* translators: %s: search term */
            $errors->add( 'registerfail', sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !', 'approve-new-user' ), get_option( 'admin_email' ) ) );
        } else {
            // User Registeration welcome email
            $disable = apply_filters('anuiwp_disable_welcome_email',false, $user_id);
            if(false===$disable) {
                $user_notifications_options = anuiwp_user_notifications_options();

                $user_welcome_email_status = (isset($user_notifications_options['user_welcome_email_status']) && !empty($user_notifications_options['user_welcome_email_status'])) ? $user_notifications_options['user_welcome_email_status'] : "";

                if(isset($user_welcome_email_status) && $user_welcome_email_status == "on") {

                    $message = (isset($user_notifications_options['user_welcome_notification_message']) && !empty($user_notifications_options['user_welcome_notification_message'])) ? $user_notifications_options['user_welcome_notification_message'] : anuiwp_default_registeration_welcome_email();
                    $message = preg_replace('/<br(\s+)?\/?>/i', "\n", $message);
                    $message = apply_filters( 'anuiwp_approve_new_user_welcome_user_message', $message, $user_email );
                    /* translators: %s: search term */
                    $subject = (isset($user_notifications_options['user_welcome_notification_subject']) && !empty($user_notifications_options['user_welcome_notification_subject'])) ? $user_notifications_options['user_welcome_notification_subject'] : anuiwp_default_registeration_welcome_email_subject();

                    wp_mail( $user_email, $subject, $message, $this->email_message_headers() );
                }
            }
        }
    }

    /**
     * Display a message to the user after they have registered
     *
     * @uses registration_errors
     */
    public function show_user_pending_message( $errors )
    {
        $nonce = '';
        if ( wp_verify_nonce($nonce) ) {return;}
        $disable_redirect = apply_filters( 'anuiwp_disable_redirect_to_field', false );
        if ( !empty($_POST['redirect_to']) && false === $disable_redirect ) {
            // if a redirect_to is set, honor it
            wp_safe_redirect( wp_unslash($_POST['redirect_to'] ));
            exit;
        }

        // if there is an error already, let it do it's thing
        if ( !empty($errors) && is_wp_error($errors) && $errors->get_error_code() ) {
            return  $errors;
        }

        $registration_options = anuiwp_registration_options();
        $message = (isset($registration_options['registration_complete_message']) && !empty($registration_options['registration_complete_message'])) ? $registration_options['registration_complete_message'] : anuiwp_default_registration_complete_message();

        $message = anuiwp_do_email_tags( $message, array(
            'context' => 'pending_message',
        ) );
        $message = apply_filters( 'anuiwp_approve_new_user_pending_message', $message );
        $errors->add( 'registration_required', $message, 'message' );
        $success_message = __( 'Registration successful.', 'approve-new-user' );
        $success_message = apply_filters( 'anuiwp_approve_new_user_registration_message', $success_message );

        if ( function_exists( 'login_header' ) ) {
            login_header( __( 'Pending Approval', 'approve-new-user' ), '<p class="message register">' . $success_message . '</p>', $errors );
        }
        if ( function_exists( 'login_footer' ) ) {
            login_footer();
        }

        do_action( 'anuiwp_approve_new_user_after_registration', $errors, $success_message );

        // an exit is necessary here so the normal process for user registration doesn't happen
        exit;
    }

    /**
     * Add message to login page saying registration is required.
     */
    public function welcome_user( $message )
    {
        if ( !isset( $_GET['action'] ) ) {
            $registration_options = anuiwp_registration_options();
            $welcome = (isset($registration_options['welcome_message']) && !empty($registration_options['welcome_message'])) ? $registration_options['welcome_message'] : anuiwp_default_welcome_message();

            $welcome = anuiwp_do_email_tags( $welcome, array(
                'context' => 'welcome_message',
            ) );
            $welcome = apply_filters( 'anuiwp_approve_new_user_welcome_message', $welcome );
            if ( !empty($welcome) ) {
                $message .= '<p class="message register">' . $welcome . '</p>';
            }
        }

        $nonce = '';
        if ( wp_verify_nonce($nonce) ) {return;}
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'register' && !$_POST ) {
            $registration_options = anuiwp_registration_options();
            $instructions = (isset($registration_options['registration_message']) && !empty($registration_options['registration_message'])) ? $registration_options['registration_message'] : anuiwp_default_registration_message();

            $instructions = anuiwp_do_email_tags( $instructions, array(
                'context' => 'registration_message',
            ) );
            $instructions = apply_filters( 'anuiwp_approve_new_user_register_instructions', $instructions );
            if ( !empty($instructions) ) {
                $message .= '<p class="message register">' . $instructions . '</p>';
            }
        }

        return $message;
    }

    /**
     *
     */
    public function anuiwp_welcome_email_woo_new_user( $customer_id ) {
        $user_notifications_options = anuiwp_user_notifications_options();

        $user_welcome_email_status = (isset($user_notifications_options['user_welcome_email_status']) && !empty($user_notifications_options['user_welcome_email_status'])) ? $user_notifications_options['user_welcome_email_status'] : "";

        if(isset($user_welcome_email_status) && $user_welcome_email_status == "on") {

            $customer = new WC_Customer( $customer_id );
            $user_email = $customer->get_email();
            // $message = anuiwp_default_registeration_welcome_email();
            $message = (isset($user_notifications_options['user_welcome_notification_message']) && !empty($user_notifications_options['user_welcome_notification_message'])) ? $user_notifications_options['user_welcome_notification_message'] : anuiwp_default_registeration_welcome_email();
            $message = preg_replace('/<br(\s+)?\/?>/i', "\n", $message);
            $message = apply_filters( 'anuiwp_approve_new_user_welcome_user_message', $message, $user_email );

            /* translators: %s: search term */
            $subject = (isset($user_notifications_options['user_welcome_notification_subject']) && !empty($user_notifications_options['user_welcome_notification_subject'])) ? $user_notifications_options['user_welcome_notification_subject'] : anuiwp_default_registeration_welcome_email_subject();

            $disable_welcome_email = apply_filters('anuiwp_disable_welcome_email_woo_new_user', array($this, false) );
            if($disable_welcome_email===true) {
                return;
            }

            wp_mail( $user_email, $subject, $message, $this->email_message_headers() );
        }
    }

    /**
     * Only give a user their password if they have been approved
     */
    public function lost_password( $errors, $user_data )
    {   $user_login=$user_data->user_login;
        if(empty($user_login)) {return;}

        $is_email = strpos( sanitize_text_field(wp_unslash($user_login)), '@' );

        if ( $is_email === false ) {
            $username = sanitize_user( wp_unslash($user_login ));
            $user_data = get_user_by( 'login', trim( $username ) );
        } else {
            $email = is_email( wp_unslash($user_login) );
            $user_data = get_user_by( 'email', $email );
        }

        if ( isset($user_data) && is_object($user_data) && $user_data->anuiwp_user_status && $user_data->anuiwp_user_status != 'approved' ) {
            $errors->add( 'unapproved_user', __( '<strong>ERROR</strong>: User has not been approved.', 'approve-new-user' ) );
        }
        return $errors;
    }

    /**
     * Only validate the update if the status has been updated to prevent unnecessary update and especially emails.
     */
    public function validate_status_update( $do_update, $user_id, $status )
    {
        $current_status = anuiwp_approve_new_user()->get_user_status( $user_id );

        if ( $status == 'approve' ) {
            $new_status = 'approved';
        } else {
            $new_status = 'denied';
        }

        if ( $current_status == $new_status ) {
            $do_update = false;
        }
        return $do_update;
    }

    /**
     * Add error codes to shake the login form on failure
     */
    public function failure_shake( $error_codes )
    {
        $error_codes[] = 'pending_approval';
        $error_codes[] = 'denied_access';
        return $error_codes;
    }

    /**
     * Give the user a status
     */
    public function add_user_status( $user_id )
    {
        $status = 'pending';
        // This check needs to happen when a user is created in the admin
        if ( isset( $_REQUEST['action'] ) && 'createuser' == $_REQUEST['action'] ) {
            $status = 'approved';
        }
        $status = apply_filters( 'anuiwp_approve_new_user_default_status', $status, $user_id );
        //update user count
        $this->update_users_statuses_count( $status,$user_id );
        update_user_meta( $user_id, 'anuiwp_user_status', $status );

    }

    /**
     * Send an email to the admin to request approval.
     */
    public function request_admin_approval_email_2( $user_id )
    {
        $user = new WP_User( $user_id );
        $user_login = stripslashes( $user->data->user_login );
        $user_email = stripslashes( $user->data->user_email );
        $this->admin_approval_email( $user_login, $user_email );
    }

    /**
     * Send email to admin requesting approval.
     */
    public function admin_approval_email( $user_login, $user_email )
    {
        $default_admin_url = admin_url( 'users.php?s&anuiwp-status-query-submit-top=Filter&approve_new_user_filter-top=pending&paged=1&approve_new_user_filter-bottom=view_all' );
        $admin_url = apply_filters( 'anuiwp_approve_new_user_admin_link', $default_admin_url );
        /* send email to admin for approval */
        $admin_notifications_options = anuiwp_admin_notifications_options();
        $message = (isset($admin_notifications_options['admin_notification_message']) && !empty($admin_notifications_options['admin_notification_message'])) ? $admin_notifications_options['admin_notification_message'] : anuiwp_default_notification_message();

        $message = anuiwp_do_email_tags( $message, array(
            'context'    => 'request_admin_approval_email',
            'user_login' => $user_login,
            'user_email' => $user_email,
            'admin_url'  => $admin_url,
        ) );
        $message = preg_replace('/<br(\s+)?\/?>/i', "\n", $message);
        $message = apply_filters(
            'anuiwp_approve_new_user_request_approval_message',
            $message,
            $user_login,
            $user_email
        );
        /* translators: %s: search term */
        $subject = (isset($admin_notifications_options['admin_notification_subject']) && !empty($admin_notifications_options['admin_notification_subject'])) ? $admin_notifications_options['admin_notification_subject'] : anuiwp_default_notification_subject();

        $general_options = anuiwp_general_options();
        $site_admin_email = get_option( 'admin_email' );
        $admin_emails = array( $site_admin_email );

        if(isset($admin_notifications_options['send_notifications_emails_to_all_admin']) && $admin_notifications_options['send_notifications_emails_to_all_admin'] == "on") {
            $admins = get_users( array(
                'role'    => 'administrator',
                'fields'  => array( 'user_email'),
            ) );

            $admin_emails = wp_list_pluck($admins, 'user_email');
        }

        if(isset($admin_notifications_options['dont_send_notifications_to_admin']) && $admin_notifications_options['dont_send_notifications_to_admin'] == "on") {
            $key = array_search($site_admin_email, $admin_emails);

            if ($key !== false) {
                unset($admin_emails[$key]);
            }
        }

        if(isset($general_options['change_the_sender_email']) && !empty($general_options['change_the_sender_email'])) {
            $admin_emails[] = $general_options['change_the_sender_email'];
        }

        $to = apply_filters( 'anuiwp_approve_new_user_email_admins', $admin_emails );
        $to = array_unique( $to );

        // send the mail
        wp_mail( $to, $subject, $message, $this->email_message_headers() );
    }

    public function email_message_headers()
    {
        $admin_email = get_option( 'admin_email' );
        $from_name = get_option( 'blogname' );
        $headers = array( "From: \"{$from_name}\" <{$admin_email}>\n" );
        $headers = apply_filters( 'anuiwp_approve_new_user_email_header', $headers );
        return $headers;
    }

    /**
     * Update users statuses count
     *
     */
    public function update_users_statuses_count($new_status,$user_id)
    {
        $old_status=get_user_meta( $user_id, 'anuiwp_user_status',true);

        if( $old_status ==$new_status ){return;}

        $user_statuses = get_option( 'anuiwp_user_statuses_count',array());
        if(empty($user_statuses))
        {
            $user_statuses = anuiwp_approve_new_user()->_get_user_statuses();
        }

        foreach ( anuiwp_approve_new_user()->get_valid_statuses() as $status ) {

            if(isset($user_statuses[$status]) && $old_status == $status)
            {
                $count=$user_statuses[$status];
                $user_statuses[$status]=$count-1;
            }elseif(isset($user_statuses[$status]) && $new_status == $status)
            {
                $count=$user_statuses[$status];
                $user_statuses[$status]=$count+1;
            }
        }
        update_option( 'anuiwp_user_statuses_count', $user_statuses);
    }

    /**
     * Disable auto login for WooCommerce
     *
     * @return boolean
     */
    public function disable_woo_auto_login( $new_customer )
    {
        return false;
    }
    /**
     * Disable auto login on WooCommerce checkout
     *
     */
    public function disable_woo_auto_login_on_checkout()
    {
        // destroying session when pending user trying to checkout
        $boolean = false;
        $boolean = apply_filters( 'anuiwp_approve_new_user_woo_checkout_process_logout', $boolean );
        if( $boolean ) {
            if( is_user_logged_in() ) {
                $user_id = get_current_user_id();
                $user_status = get_user_meta($user_id, 'anuiwp_user_status', true);
                if ( $user_status == 'denied' || $user_status == 'pending') {
                    wp_destroy_current_session();
                    wp_clear_auth_cookie();
                    wp_set_current_user( 0 );
                }
            }
        }
    }
}