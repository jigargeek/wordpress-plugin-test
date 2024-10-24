<?php
/**
 * The default email message that will be sent to users as they are approved.
 */
function anuiwp_default_approve_user_message() {
	$message = __( 'You have been approved to access {sitename}', 'approve-new-user' ) . "\r\n\r\n";
	$message .= "{username}\r\n\r\n";
	$message .= "{login_url}\r\n\r\n";
    $message .= __( 'To set or reset your password, visit the following address:', 'approve-new-user' ) . "\r\n\r\n";
    $message .= "{reset_password_url}";

	$message = apply_filters( 'anuiwp_approve_new_user_message_default', $message );

	return $message;
}

/**
 * The default message that will be shown to the user after registration has completed.
 */
function anuiwp_default_registration_complete_message() {
	$message = sprintf( __( 'An email has been sent to the site administrator. The administrator will review the information that has been submitted and either approve or deny your request.', 'approve-new-user' ) );
	$message .= ' ';
	$message .= sprintf( __( 'You will receive an email with instructions on what you will need to do next. Thanks for your patience.', 'approve-new-user' ) );

	$message = apply_filters( 'anuiwp_approve_new_user_pending_message_default', $message );

	return $message;
}

/**
 * The default pending error message
 */
function anuiwp_approve_new_user_pending_error() {
	$message = __( '<strong>ERROR</strong>: Your account is still pending approval.', 'approve-new-user' );
	$message = apply_filters( 'anuiwp_approve_new_user_pending_error', $message );

	return $message;
}

/**
 * The default reject error message
 */
function anuiwp_approve_new_user_denied_error() {
	$message = __( '<strong>ERROR</strong>: Your account has been denied access to this site.', 'approve-new-user' );
	$message = apply_filters( 'anuiwp_approve_new_user_denied_error', $message );

	return $message;
}

/**
 * The approve new user email subject
 */
function anuiwp_approve_new_user_subject() {
	$subject = sprintf( __( '[%s] Registration Approved', 'approve-new-user' ), get_option( 'blogname' ) );
    $subject = apply_filters( 'anuiwp_approve_new_user_subject', $subject );

	return $subject;
}

/**
 * The approve new user email message
 */
function anuiwp_auto_approve_message() {
	$message = sprintf( __( 'You have been approved to access {sitename}. You will receive an email with instructions on what you will need to do next. Thanks for your patience.
	', 'approve-new-user' ) );
	$message .= ' ';
	$message = apply_filters( 'anuiwp_approve_new_user_auto_approve_message', $message );

	return $message;
}

/**
 * The default email subject that will be sent to users as they are denied.
 */
function anuiwp_default_deny_user_subject() {
	$subject = sprintf( __( '[%s] Registration Denied', 'approve-new-user' ), get_option( 'blogname' ) );
    $subject = apply_filters( 'anuiwp_approve_new_user_deny_user_subject', $subject );

	return $subject;
}

/**
 * The default email message that will be sent to users as they are denied.
 */
function anuiwp_default_deny_user_message() {
	$message = __( 'You have been denied access to {sitename}.', 'approve-new-user' );

	$message = apply_filters( 'anuiwp_approve_new_user_deny_user_message_default', $message );

	return $message;
}

/**
 * The default welcome message that is shown to all users on the login page.
 */
function anuiwp_default_welcome_message() {
	$welcome = sprintf( __( 'Welcome to {sitename}. This site is accessible to approved users only. To be approved, you must first register.', 'approve-new-user' ), get_option( 'blogname' ) );

	$welcome = apply_filters( 'anuiwp_approve_new_user_welcome_message_default', $welcome );

	return $welcome;
}

/**
 * The default notification subject that is sent to site admin when requesting approval.
 */
function anuiwp_default_notification_subject() {
	$subject = sprintf( __( '[%s] User Approval', 'approve-new-user' ), wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );
    $subject = apply_filters( 'anuiwp_approve_new_user_request_approval_subject', $subject );

	return $subject;
}

/**
 * The default notification message that is sent to site admin when requesting approval.
 */
function anuiwp_default_notification_message() {
	$message = __( '{username} ({user_email}) has requested a username at {sitename}', 'approve-new-user' ) . "\n\n";
	$message .= "{site_url}\n\n";
	$message .= __( 'To approve or deny this user access to {sitename} go to', 'approve-new-user' ) . "\n\n";
	$message .= "{admin_approve_url}\n\n";

	$message = apply_filters( 'anuiwp_approve_new_user_request_approval_message_default', $message );

	return $message;
}

/**
 * The default message that is shown to the user on the registration page before any action
 * has been taken.
 */
function anuiwp_default_registration_message() {
	$message = __( 'After you register, your request will be sent to the site administrator for approval. You will then receive an email with further instructions.', 'approve-new-user' );

	$message = apply_filters( 'anuiwp_approve_new_user_registration_message_default', $message );

	return $message;
}

function anuiwp_default_registeration_welcome_email_subject() {
    $subject = sprintf( __( 'Your registration is pending for approval - [%s]', 'approve-new-user' ), get_option( 'blogname' ) );
    $subject = apply_filters( 'anuiwp_approve_new_user_welcome_user_subject', $subject );

    return $subject;
}

function anuiwp_default_registeration_welcome_email() {
    $message  = __('Hello,', "approve-new-user") . "\r\n\r\n";

    $message .= __("Thank you for registering on our site. We have successfully received your request and is currently pending for approval.", "approve-new-user") . "\r\n";

    $message .= __("The administrator will review the information that has been submitted after which they will either approve or deny your request. You will receive an email with the instructions on what you will need to do next.", "approve-new-user") . "\r\n\r\n";

    $message .= __("Thank You", "approve-new-user");

    return $message;
}
