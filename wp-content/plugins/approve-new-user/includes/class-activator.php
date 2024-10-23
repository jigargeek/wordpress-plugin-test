<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class ANUIWP_Activator {

	/**
	 * Require a minimum version of WordPress on activation.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wp_version;
        $min_wp_version = '3.5.1';

		/* translators: %s: search term */
        $exit_msg = sprintf( __( 'Approve New User requires WordPress %s or newer.', 'approve-new-user' ), $min_wp_version );
        if ( version_compare( $wp_version, $min_wp_version, '<' ) ) {
             exit( esc_html( $exit_msg ));
        }
        // since the right version of WordPress is being used, run a hook
        do_action( 'anuiwp_approve_new_user_activate' );
	}
}