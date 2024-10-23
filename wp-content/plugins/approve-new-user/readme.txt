=== Approve New User ===
Contributors: rajkakadiya
Donate link: https://geekcodelab.com/
Tags: comments, spam
Requires PHP: 7.4
Requires at least: 6.3
Tested up to: 6.6.1
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Approve New User plugin automates the user registration process on your WordPress website.

== Description ==

The Approve New User plugin automates the user registration process on your WordPress website, adding a layer of approval to ensure better control over who can access your site.

Typically, registering users on a WordPress site is straightforward. When a new user registers, their information is added to the website’s database, and they receive an email with their login credentials. While simple, this process offers many opportunities for customization.

Introducing Approve New User – a new way to register users on your WordPress website. Here’s how it works:

<b>User Registration:</b> A new user registers on the site, and their ID is created.
<b>Admin Notification:</b> An email is sent to the site administrators.
<b>Approval Process:</b> An administrator can either approve or deny the registration request.
<b>User Notification:</b> An email is sent to the user, letting them know if their registration was approved or denied.
<b>Login Credentials:</b> If approved, the user receives an email with their login credentials.

Users will not be able to log in until they are approved. Only approved users will be allowed access, while those waiting for approval or who have been rejected will not be able to log in. This process is simple, straightforward, and effective.

Additionally, user status can be updated even after the initial approval or denial. Administrators can search for approved, denied, and pending users. Also, users created before the activation of Approve New User will automatically be treated as approved users.

**Compatibility**  
It works seamlessly with [WooCommerce](https://woocommerce.com/), [MemberPress](https://memberpress.com/).

**Custom Actions & Filters**  
For developers, **Approve New User** offers several actions and filters to modify messages and functionality. This makes it highly customizable.

**Filters**
- *anuiwp_user_status_update* - modify the list of users shown in the tables
- *anuiwp_approve_new_user_request_approval_message* - modify the request approval message
- *anuiwp_approve_new_user_request_approval_subject* - modify the request approval subject
- *anuiwp_approve_new_user_message* - modify the user approval message
- *anuiwp_approve_new_user_subject* - modify the user approval subject
- *anuiwp_approve_new_user_deny_user_message* - modify the user denial message
- *anuiwp_approve_new_user_deny_user_subject* - modify the user denial subject
- *anuiwp_approve_new_user_pending_message* - modify message user sees after registration
- *anuiwp_approve_new_user_registration_message* - modify message after a successful registration
- *anuiwp_approve_new_user_register_instructions* - modify message that appears on registration screen
- *anuiwp_approve_new_user_welcome_message* - modify welcome message that appears on login page
- *anuiwp_approve_new_user_pending_error* - error message shown to pending users when attempting to log in
- *anuiwp_approve_new_user_denied_error* - error message shown to denied users when attempting to log in
- *anuiwp_pass_create_new_user', $user_pass* - modify the password being assiged to newly created user

**Actions**
- *anuiwp_approve_new_user_after_approved* - after the user has been approved
- *anuiwp_approve_new_user_denied* - after the user has been denied
- *anuiwp_approve_user* - when the user has been approved
- *anuiwp_deny_user* - when the user has been denied

<h4>Features</h4>

* <b>Automated User Registration:</b> Simplifies the user registration process by adding a layer of approval.
* <b>Admin Notification:</b> Sends an email to site administrators whenever a new user registers.
* <b>Approval/Deny Option:</b> Allows administrators to approve or deny registration requests.
* <b>User Notification:</b> Sends an email to users informing them if their registration has been approved or denied.
* <b>Secure Login Credentials:</b> Provides login credentials to users only after approval.
* <b>Access Control:</b> Ensures that only approved users can log in to the site.
* <b>Status Updates:</b> Allows administrators to update user status (approve or deny) even after the initial decision.
* <b>User Search:</b> Enables administrators to search for approved, denied, and pending users.
* <b>Backward Compatibility:</b> Automatically treats users created before the plugin’s activation as approved users.
* <b>Remove Plugin Stats from Admin Dashboard</b>: Ability to hide plugin statistics from the WordPress admin dashboard.
* <b>Customize Login Form Welcome Message</b>: Personalize the welcome message shown above the WordPress login form.
* <b>Pending Error Message Customization</b>: Modify the error message shown to users when their account is pending approval.
* <b>Denied Error Message Customization</b>: Change the error message displayed when a user's account is denied approval.
* <b>Customize Registration Form Welcome Message</b>: Set a custom welcome message above the WordPress registration form.
* <b>Registration Complete Message Customization</b>: Edit the message shown to users after submitting their registration form.
* <b>Send Notification Emails to All Admins</b>: Ability to notify all site admins when a new user registers.
* <b>Notify Admins of Status Updates</b>: Admins receive notifications when a user's status is updated.
* <b>Disable Notification Emails to Site Admin</b>: Option to turn off notification emails for the current site admin.
* <b>Customize Admin Registration Emails</b>: Edit the email sent to admin(s) when a user registers on the site.
* <b>Customize Approval Email to User</b>: Personalize the email sent to users when their account is approved.
* <b>Customize Denial Email to User</b>: Modify the email sent to users when their account is denied.
* <b>Suppress Denial Notifications</b>: Option to disable email notifications when a user is denied.
* <b>Use Template Tags in Emails and Messages</b>: Implement different template tags for customizing notification emails and other messages.

== Installation ==

1. Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation.
2. Activate the plugin through the Plugins menu in WordPress
3. No configuration necessary.

After Plugin Active go to WooCommerce-> Donation.

== Screenshots ==

1. Welcome Message
2. Register Form Message
3. Successfull Registration Message
4. User List Page
5. Update User Access Status
6. Approve New User
7. General Settings
8. Registration Notifications
9. Admin Notifications
10. User Notifications


== Changelog ==
= 1.0.2 =
 New Features Added

= 1.0.1 =
 Prefix updated for security reasons
 Unused hooks enqueue script removed
 Fixed missing sanitize in verify nonce 

= 1.0.0 =
 Initial release