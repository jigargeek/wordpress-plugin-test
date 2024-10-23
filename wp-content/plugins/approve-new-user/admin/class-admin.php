<?php
class ANUIWP_Admin_Hooks {
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
	 * Use javascript to add the ability to bulk modify the status of users.
	 *
	 * @uses admin_footer-users.php
	 */
	public function admin_footer() {
		$screen = get_current_screen();

		if ( $screen->id == 'users' ) : ?>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {

					$('<option>').val('approve').text('<?php esc_attr_e( 'Approve', 'approve-new-user' )?>').appendTo("select[name='action']");
					$('<option>').val('approve').text('<?php esc_attr_e( 'Approve', 'approve-new-user' )?>').appendTo("select[name='action2']");

					$('<option>').val('deny').text('<?php esc_attr_e( 'Deny', 'approve-new-user' )?>').appendTo("select[name='action']");
					$('<option>').val('deny').text('<?php esc_attr_e( 'Deny', 'approve-new-user' )?>').appendTo("select[name='action2']");
				});
			</script>
		<?php endif;
	}

    /**
	 * Show a message on the users page if a status has been updated.
	 *
	 * @uses admin_notices
	 */
	public function admin_notices() {
		$screen = get_current_screen();

		if ( $screen->id != 'users' ) {
			return;
		}

		$message = null;

		if ( isset( $_REQUEST['denied'] ) && (int) $_REQUEST['denied'] ) {
			$denied = sanitize_text_field(wp_unslash( $_REQUEST['denied'] ) );
			$message = sprintf( _n( 'User denied.', '%s users denied.', $denied, 'approve-new-user' ), number_format_i18n( $denied ) );
		}

		if ( isset( $_REQUEST['approved'] ) && (int) $_REQUEST['approved'] ) {
			$approved = sanitize_text_field( wp_unslash( $_REQUEST['approved'] ) );
			$message = sprintf( _n( 'User approved.', '%s users approved.', $approved, 'approve-new-user' ), number_format_i18n( $approved ) );
		}

		if ( !empty( $message ) ) {
			echo ( wp_kses_post( '<div class="updated"><p>' . $message . '</p></div>'));
		}
	}

    /**
	 * Manage dashboard states.
	 *
	 * @uses rightnow_end
	 */
    public function dashboard_stats()
    {
        $user_status = anuiwp_approve_new_user()->get_count_of_user_statuses();
		$general_options = anuiwp_general_options();
		$hide_dashboard_states = (isset($general_options["hide_dashboard_stats"]) && !empty($general_options["hide_dashboard_stats"])) ? $general_options["hide_dashboard_stats"] : "";
		if($hide_dashboard_states != "on") {
			?>
			<div>
				<p>
					<span style="font-weight:bold;">
						<a href="<?php
							echo  wp_kses_post (apply_filters( 'approve_new_user_dashboard_link', 'users.php' )) ;
							?>"><?php
							esc_html_e( 'Users', 'approve-new-user' );
							?>
						</a>
					</span>:
					<?php
					foreach ( $user_status as $status => $count ) {
						print esc_html_e( ucwords( $status ), 'approve-new-user' ) . "(" . esc_attr($count) . ")&nbsp;&nbsp;&nbsp;";
					}
					?>
				</p>
			</div>
			<?php
		}
    }

    /**
     * Admin enqueue scripts
    */
    public function admin_scripts() {
        $pages = array('anuiwp-menu-page','anuiwp-settings');

        if (isset($_GET['page']) && in_array($_GET['page'], $pages)) {
            wp_enqueue_style( 'anuiwp-admin-style', plugins_url( 'assets/css/admin-style.css', __FILE__ ), array(), ANUIWP_VERSION);
			wp_enqueue_editor();
			wp_enqueue_media();
			wp_enqueue_script( 'anuiwp-admin-script', plugins_url( 'assets/js/admin-script.js', __FILE__ ), array( 'jquery' ), ANUIWP_VERSION );
        }
    }

	/**
     * Plugin action links
     */
	public function plugin_add_settings_link($links)
	{
		$support_link = '<a href="https://geekcodelab.com/contact/"  target="_blank" >' . __('Support','approve-new-user') . '</a>';
		array_unshift($links, $support_link);

		$settings_link = '<a href="admin.php?page=anuiwp-settings">' . __('Settings','approve-new-user') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	/**
	 * Admin User list page init
	 */
	public function process_user_data() {
		if ((isset($_GET['page']) && $_GET['page'] == "anuiwp-menu-page") && isset($_GET['status'])&& isset($_GET['user'])) {
            $valid_request = check_admin_referer('anuiwp_approve_new_user_action_anuiwp-menu-page');

            if ($valid_request) {
                $status = sanitize_key($_GET['status']);
                $user_id = absint(sanitize_user(wp_unslash($_GET['user'])));

                anuiwp_approve_new_user()->update_user_status($user_id, $status);
            }
        }
	}
}