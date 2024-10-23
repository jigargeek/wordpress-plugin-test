<?php
if( !function_exists('anuiwp_general_options') ) {
    function anuiwp_general_options() {
        $anuiwp_general_options = get_option('anuiwp_general_options');

        return $anuiwp_general_options;
    }
}

if( !function_exists('anuiwp_registration_options') ) {
    function anuiwp_registration_options() {
        $anuiwp_registration_options = get_option('anuiwp_registration_options',array(
            "welcome_message" => anuiwp_default_welcome_message(),
            "registration_message" => anuiwp_default_registration_message(),
            "registration_complete_message" => anuiwp_default_registration_complete_message(),
            "pending_error_message" => anuiwp_approve_new_user_pending_error(),
            "reject_error_message" => anuiwp_approve_new_user_denied_error()
        ));

        return $anuiwp_registration_options;
    }
}

if( !function_exists('anuiwp_admin_notifications_options') ) {
    function anuiwp_admin_notifications_options() {
        $anuiwp_admin_notifications_options = get_option('anuiwp_admin_notifications_options', array(
            "admin_notification_subject" => anuiwp_default_notification_subject(),
            "admin_notification_message" => anuiwp_default_notification_message()
        ));

        return $anuiwp_admin_notifications_options;
    }
}

if( !function_exists('anuiwp_user_notifications_options') ) {
    function anuiwp_user_notifications_options() {
        $anuiwp_user_notifications_options = get_option('anuiwp_user_notifications_options', array(
            "user_approve_notification_subject" => anuiwp_approve_new_user_subject(),
            "user_approve_notification_message" => anuiwp_default_approve_user_message(),
            "user_deny_notification_subject"    => anuiwp_default_deny_user_subject(),
            "user_deny_notification_message"    => anuiwp_default_deny_user_message(),
            "user_welcome_notification_subject" => anuiwp_default_registeration_welcome_email_subject(),
            "user_welcome_notification_message" => anuiwp_default_registeration_welcome_email()
        ));

        return $anuiwp_user_notifications_options;
    }
}