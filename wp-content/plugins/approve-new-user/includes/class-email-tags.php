<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Email Tags API for creating Email template tags
 */

class ANUIWP_Email_Template_Tags {

	/**
	 * Container for storing all tags
	 */
	private $tags;

	/**
	 * Attributes
	 */
	private $attributes;

	/**
	 * Add an email tag
	 */
	public function add( $tag, $description, $func, $context ) {
		if ( is_callable( $func ) ) {
			$this->tags[$tag] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func,
				'context'     => $context,
			);
		}
	}

	/**
	 * Remove an email tag
	 */
	public function remove( $tag ) {
		unset( $this->tags[$tag] );
	}

	/**
	 * Check if $tag is a registered email tag
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}

	/**
	 * Returns a list of all email tags
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Search content for email tags and filter email tags through their hooks
	 */
	public function do_tags( $content, $attributes ) {

		// Check if there is atleast one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->attributes = $attributes;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		return $new_content;
	}

	/**
	 * Do a specific tag, this function should not be used. Please use edd_do_email_tags instead.
	 *
	 * @param $m message
	 *
	 * @return mixed
	 */
	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[$tag]['func'], $this->attributes, $tag );
	}

}

/**
 * Add an email tag
 */
function anuiwp_add_email_tag($tag, $description, $func, $context)
{
    $plugin_instance = anuiwp_approve_new_user();
    $plugin_instance->email_tags->add($tag, $description, $func, $context);
}

/**
 * Remove an email tag
 */
function anuiwp_remove_email_tag($tag)
{
    $plugin_instance = anuiwp_approve_new_user();
    $plugin_instance->email_tags->remove($tag);
}

/**
 * Check if $tag is a registered email tag
 */
function anuiwp_email_tag_exists( $tag ) {
	$plugin_instance = anuiwp_approve_new_user();
    return $plugin_instance->email_tags->email_tag_exists($tag);
}

/**
 * Get all email tags
 */
function anuiwp_get_email_tags() {
	$plugin_instance = anuiwp_approve_new_user();
    return $plugin_instance->email_tags->get_tags();
}

/**
 * Get a formatted HTML list of all available email tags
 */
function anuiwp_get_emails_tags_list( $context = 'email' ) {
	// The list
	$list = '';

	// Get all tags
	$email_tags = anuiwp_get_email_tags();

	// Check
	if ( count( $email_tags ) > 0 ) {

		// Loop
		foreach ( $email_tags as $email_tag ) {
			if ( in_array( $context, $email_tag['context'] ) ) {
				// Add email tag to list
				$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';
			}
		}

	}

	// Return the list
	return $list;
}

/**
 * Search content for email tags and filter email tags through their hooks
 */
function anuiwp_do_email_tags( $content, $attributes ) {

	$attributes = apply_filters('anuiwp_email_tags_attributes', $attributes);

    // Replace all tags
    $plugin_instance = anuiwp_approve_new_user();
    $content = $plugin_instance->email_tags->do_tags($content, $attributes);

    // Return content
    return $content;
}

/**
 * Load email tags
 */
function anuiwp_load_email_tags() {
	do_action( 'anuiwp_add_email_tags' );
}
add_action( 'init', 'anuiwp_load_email_tags', -999 );

/**
 * Add default anuiwp email template tags
 */
function anuiwp_setup_email_tags() {

	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'username',
			'description' => __( "The user's username on the site as well as the Username label", 'approve-new-user' ),
			'function'    => 'anuiwp_email_tag_username',
			'context'     => array( 'email' ),
		),
		array(
			'tag'         => 'user_email',
			'description' => __( "The user's email address", 'approve-new-user' ),
			'function'    => 'anuiwp_email_tag_user_email',
			'context'     => array( 'email' ),
		),
		array(
			'tag'         => 'sitename',
			'description' => __( 'Your site name', 'approve-new-user' ),
			'function'    => 'anuiwp_email_tag_sitename',
			'context'     => array( 'email', 'login' ),
		),
		array(
			'tag'         => 'site_url',
			'description' => __( 'Your site URL', 'approve-new-user' ),
			'function'    => 'anuiwp_email_tag_siteurl',
			'context'     => array( 'email' ),
		),
		array(
			'tag'         => 'admin_approve_url',
			'description' => __( 'The URL to approve/deny users', 'approve-new-user' ),
			'function'    => 'anuiwp_email_tag_adminurl',
			'context'     => array( 'email' ),
		),
		array(
			'tag'         => 'login_url',
			'description' => __( 'The URL to login to the site', 'approve-new-user' ),
			'function'    => 'anuiwp_email_tag_loginurl',
			'context'     => array( 'email' ),
		),
        array(
            'tag'         => 'reset_password_url',
            'description' => __( 'The URL for a user to set/reset their password', 'approve-new-user' ),
            'function'    => 'anuiwp_email_tag_reset_password_url',
            'context'     => array( 'email' ),
        ),
		array(
			'tag'         => 'password',
			'description' => __( 'Generates the password for the user to add to the email', 'approve-new-user' ),
			'function'    => 'anuiwp_email_tag_password',
			'context'     => array( 'email' ),
		),
	);

	// Apply anuiwp_email_tags filter
	$email_taged = apply_filters( 'anuiwp_email_tags', $email_tags );

	// Add email tags
	foreach ( $email_taged as $email_tag ) {
		anuiwp_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'], $email_tag['context'] );
	}

}
add_action( 'anuiwp_add_email_tags', 'anuiwp_setup_email_tags' );

/**
 * Email template tag: username
 * The user's user name on the site
 */
function anuiwp_email_tag_username( $attributes) {
	$username = $attributes['user_login'];
	return sprintf( __( 'Username: %s', 'approve-new-user' ), $username );
}

/**
 * Email template tag: user_email
 * The user's email address
 */
function anuiwp_email_tag_user_email( $attributes ) {
	return $attributes['user_email'];
}

/**
 * Email template tag: sitename
 * Your site name
 */
function anuiwp_email_tag_sitename( $attributes ) {
	return get_bloginfo( 'name' );
}

/**
 * Email template tag: site_url
 * Your site URL
 */
function anuiwp_email_tag_siteurl( $attributes ) {
	return home_url();
}

/**
 * Email template tag: admin_approve_url
 * Your site URL
 */
function anuiwp_email_tag_adminurl( $attributes ) {
	return $attributes['admin_url'];
}

/**
 * Email template tag: login_url
 * Your site URL
 */
function anuiwp_email_tag_loginurl( $attributes ) {
	return wp_login_url();
}

/**
 * Email template tag: password
 * Generates the password for the user to add to the email
 */
function anuiwp_email_tag_password( $attributes ) {
	$user = $attributes['user'];

	if ( anuiwp_approve_new_user()->do_password_reset( $user->ID ) ) {
		// reset password to know what to send the user
		$new_pass = wp_generate_password( 12, false );

		// store the password
		global $wpdb;
		$data = array( 'user_pass' => md5( $new_pass ), 'user_activation_key' => '', );
		$where = array( 'ID' => $user->ID, );
		$wpdb->update( $wpdb->users, $data, $where, array( '%s', '%s' ), array( '%d' ) );

		// Set up the Password change nag.
		update_user_option( $user->ID, 'default_password_nag', true, true );

		// Set this meta field to track that the password has been reset by
		// the plugin. Don't reset it again unless doing a password reset.
		update_user_meta( $user->ID, 'anuiwp_user_approve_password_reset', time() );
		/* translators: %s: search term */
		return sprintf( __( 'Password: %s', 'approve-new-user' ), $new_pass );
	} else {
		return '';
	}
}

/**
 * Email template tag: reset_password_url
 * Generates a link to set or reset the user's password
 */
function anuiwp_email_tag_reset_password_url( $attributes ) {
    global $wpdb;

    $username = $attributes['user_login'];

    // Generate something random for a password reset key.
    $key = wp_generate_password( 20, false );

    /** This action is documented in wp-login.php */
    do_action( 'anuiwp_retrieve_password_key', $username, $key );

    // Now insert the key, hashed, into the DB.
    if ( empty( $wp_hasher ) ) {
        require_once ABSPATH . WPINC . '/class-phpass.php';
        $wp_hasher = new PasswordHash( 8, true );
    }
    $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
    $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $username ) );

    $url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($username), 'login');

    return $url;
}