<?php
class ANUIWP_User_List {

	/**
	 * Update the user status if the approve or deny link was clicked.
	 *
	 * @uses load-users.php
	 */
	public function update_action() {
		if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'approve', 'deny' ) ) && !isset( $_GET['new_role'] ) ) {

			check_admin_referer( 'approve-new-user' );

			$sendback = esc_url( remove_query_arg( array( 'approved', 'denied', 'deleted', 'ids', 'anuiwp-status-query-submit', 'new_role' ), wp_get_referer() ));
			if ( !$sendback )
				$sendback = admin_url( 'users.php' );

			$wp_list_table = _get_list_table( 'WP_Users_List_Table' );

			$pagenum = $wp_list_table->get_pagenum();
			$sendback = esc_url( add_query_arg( 'paged', $pagenum, $sendback ));

			$status = ( !empty( $_GET['action']) ) ? sanitize_key( $_GET['action'] ): '';
			$user   = ( !empty( $_GET['user']  ) ) ? absint( wp_unslash($_GET['user'] ) ) : '';

			anuiwp_approve_new_user()->update_user_status( $user, $status );

			if ( $_GET['action'] == 'approve' ) {
				$sendback = esc_url( add_query_arg( array( 'approved' => 1, 'ids' => $user ), $sendback )) ;

			} else {
				$sendback = esc_url( add_query_arg( array( 'denied' => 1, 'ids' => $user ), $sendback ));
			}

			wp_redirect( $sendback );

			exit;
		}
	}

	/**
	 * Add the approve or deny link where appropriate.
	 *
	 * @uses user_row_actions
	 * @param array $actions
	 * @param object $user
	 * @return array
	 */
	public function user_table_actions( $actions, $user ) {
		if ( $user->ID == get_current_user_id() ) {
			return $actions;
		}

		if ( is_super_admin( $user->ID ) ) {
			return $actions;
		}

		$user_status = anuiwp_approve_new_user()->get_user_status( $user->ID );

		$approve_link = add_query_arg( array( 'action' => 'approve', 'user' => $user->ID ) );
		$approve_link = remove_query_arg( array( 'new_role' ), $approve_link );
		$approve_link = wp_nonce_url( $approve_link, 'approve-new-user' );

		$deny_link = add_query_arg( array( 'action' => 'deny', 'user' => $user->ID ) );
		$deny_link = remove_query_arg( array( 'new_role' ), $deny_link );
		$deny_link = wp_nonce_url( $deny_link, 'approve-new-user' );

		$approve_action = '<a href="' . esc_url( $approve_link ) . '">' . __( 'Approve', 'approve-new-user' ) . '</a>';
		$deny_action = '<a href="' . esc_url( $deny_link ) . '">' . __( 'Deny', 'approve-new-user' ) . '</a>';

		if ( $user_status == 'pending' ) {
			$actions[] = $approve_action;
			$actions[] = $deny_action;
		} else if ( $user_status == 'approved' ) {
			$actions[] = $deny_action;
		} else if ( $user_status == 'denied' ) {
			$actions[] = $approve_action;
		}

		return $actions;
	}

	/**
	 * Add the status column to the user table
	 *
	 * @uses manage_users_columns
	 * @param array $columns
	 * @return array
	 */
	public function add_column( $columns ) {
		$the_columns['anuiwp_user_status'] = __( 'Status', 'approve-new-user' );

		$newcol = array_slice( $columns, 0, -1 );
		$newcol = array_merge( $newcol, $the_columns );
		$columns = array_merge( $newcol, array_slice( $columns, 1 ) );

		return $columns;
	}

	/**
	 * Show the status of the user in the status column
	 *
	 * @uses manage_users_custom_column
	 * @param string $val
	 * @param string $column_name
	 * @param int $user_id
	 * @return string
	 */
	public function status_column( $val, $column_name, $user_id ) {
		switch ( $column_name ) {
			case 'anuiwp_user_status' :
				$status = anuiwp_approve_new_user()->get_user_status( $user_id );
				if ( $status == 'approved' ) {
					$status_i18n = __( 'approved', 'approve-new-user' );
				} else if ( $status == 'denied' ) {
					$status_i18n = __( 'denied', 'approve-new-user' );
				} else if ( $status == 'pending' ) {
					$status_i18n = __( 'pending', 'approve-new-user' );
				}
				return $status_i18n;
				break;
			default:
		}

		return $val;
	}

	/**
	 * Add a filter to the user table to filter by user status
	 *
	 * @uses restrict_manage_users
	 */
	public function status_filter( $which ) {
		$id = 'approve_new_user_filter-' . $which;

		$filter_button = submit_button( __( 'Filter', 'approve-new-user' ), 'button', 'anuiwp-status-query-submit-'.$which, false, array( 'id' => 'anuiwp-status-query-submit-'.$which ) );
		$filtered_status = $this->selected_status();

		?>
		<label class="screen-reader-text" for="<?php echo esc_attr( $id) ?>"><?php esc_html_e( 'View all users', 'approve-new-user' ); ?></label>
		<select id="<?php echo esc_attr($id) ?>" name="<?php echo esc_attr( $id )?>" style="float: none; margin: 0 0 0 15px;">
			<option value="view_all"><?php esc_html_e( 'View all users', 'approve-new-user' ); ?></option>
		<?php foreach ( anuiwp_approve_new_user()->get_valid_statuses() as $status ) : ?>
			<option value="<?php echo esc_attr( $status ); ?>"<?php selected( $status, $filtered_status ); ?>><?php echo esc_html( ucfirst($status) ); ?></option>
		<?php endforeach; ?>
		</select>
		<?php
		if(!empty($filter_button))
		{
			echo wp_kses_post( apply_filters( 'approve_new_user_filter_button', $filter_button ));
		}
		?>
		<style>
			#anuiwp-status-query-submit-top,#anuiwp-status-query-submit-bottom {
				float: right;
				margin: 2px 0 0 5px;
			}
		</style>
		<?php
	}

	/**
	 * Modify the user query if the status filter is being used.
	 *
	 * @uses pre_user_query
	 * @param $query
	 */
    public function filter_by_status( $query ) {
		global $wpdb;

		if ( !is_admin() ) {
			return;
		}

		if( !function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( isset( $screen ) && 'users' != $screen->id ) {
			return;
		}

		if ( $this->selected_status() != null ) {
			$filter = $this->selected_status();
			//$sendback = admin_url( 'users.php' );

			if($filter == 'view_all'){
				//wp_redirect( $sendback );
				return;
			}
			$query->query_from .= " INNER JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = $wpdb->usermeta.user_id )";

			if ( 'approved' == $filter ) {
				$query->query_fields = "DISTINCT SQL_CALC_FOUND_ROWS {$wpdb->users}.ID";
				$query->query_from .= " LEFT JOIN {$wpdb->usermeta} AS mt1 ON ({$wpdb->users}.ID = mt1.user_id AND mt1.meta_key = 'anuiwp_user_status')";
				$query->query_where .= " AND ( ( $wpdb->usermeta.meta_key = 'anuiwp_user_status' AND CAST($wpdb->usermeta.meta_value AS CHAR) = 'approved' ) OR mt1.user_id IS NULL )";
			} else {
				$query->query_where .= " AND ( ($wpdb->usermeta.meta_key = 'anuiwp_user_status' AND CAST($wpdb->usermeta.meta_value AS CHAR) = '{$filter}') )";
			}
		}
	}

	private function selected_status() {
		if(isset($_REQUEST['anuiwp-status-query-submit-bottom']) && !empty($_REQUEST['anuiwp-status-query-submit-bottom']))
		{
			return esc_attr(
				isset($_REQUEST['approve_new_user_filter-bottom']) && !empty($_REQUEST['approve_new_user_filter-bottom'])
				? sanitize_text_field( wp_unslash( $_REQUEST['approve_new_user_filter-bottom']))
				: ''
			);
		}elseif( isset( $_REQUEST['approve_new_user_filter-top'] ) && !empty( $_REQUEST['approve_new_user_filter-bottom'] ) ) {
			return esc_attr(  sanitize_text_field( wp_unslash( $_REQUEST['approve_new_user_filter-top'])) );
		}

		return null;
	}

	/**
	 * Process the bulk status updates
	 *
	 * @uses load-users.php
	 */
	public function bulk_action() {
		$screen = get_current_screen();

		if ( $screen->id == 'users' ) {

			// get the action
			$wp_list_table = _get_list_table( 'WP_Users_List_Table' );
			$action = $wp_list_table->current_action();

			$allowed_actions = array( 'approve', 'deny' );
			if ( !in_array( $action, $allowed_actions ) ) {
				return;
			}

			// security check
			check_admin_referer( 'bulk-users' );

			// make sure ids are submitted
			if ( isset( $_REQUEST['users'] ) ) {
				$user_ids = array_map( 'intval', $_REQUEST['users'] );
			}

			if ( empty( $user_ids ) ) {
				return;
			}

			$sendback = remove_query_arg( array( 'approved', 'denied', 'deleted', 'ids', 'approve_new_user_filter', 'approve_new_user_filter2', 'anuiwp-status-query-submit', 'new_role' ), wp_get_referer() );
			if ( !$sendback ) {
				$sendback = admin_url( 'users.php' );
			}

			$pagenum = $wp_list_table->get_pagenum();
			$sendback = add_query_arg( 'paged', $pagenum, $sendback );

			switch ( $action ) {
				case 'approve':
					$approved = 0;
					foreach ( $user_ids as $user_id ) {
						anuiwp_approve_new_user()->update_user_status( $user_id, 'approve' );
						$approved++;
					}

					$sendback = add_query_arg( array( 'approved' => $approved, 'ids' => join( ',', $user_ids ) ), $sendback );
					break;

				case 'deny':
					$denied = 0;
					foreach ( $user_ids as $user_id ) {
						anuiwp_approve_new_user()->update_user_status( $user_id, 'deny' );
						$denied++;
					}

					$sendback = add_query_arg( array( 'denied' => $denied, 'ids' => join( ',', $user_ids ) ), $sendback );
					break;

				default:
					return;
			}

			$sendback = remove_query_arg( array( 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );

			wp_redirect( esc_url($sendback) );
			exit();
		}
	}

	/**
	 * Display the dropdown on the user profile page to allow an admin to update the user status.
	 *
	 * @uses show_user_profile
	 * @uses edit_user_profile
	 * @param object $user
	 */
	public function profile_status_field( $user ) {
		if ( $user->ID == get_current_user_id() ) {
			return;
		}
		$edit_user_nonce = wp_create_nonce('anuiwp-edit-user-nonce');
		$user_status = anuiwp_approve_new_user()->get_user_status( $user->ID );
		?>
		<table class="form-table">
			<tr>
				<th><label for="anuiwp_user_status"><?php esc_html_e( 'Access Status', 'approve-new-user' ); ?></label>
				</th>
				<td>
					<input type="hidden" id="anuiwp_edit_user_wpnonce" name="anuiwp_edit_user_wpnonce" value="<?php echo esc_attr($edit_user_nonce); ?>" />
					<input type="hidden" name="" value="<?php ?>">
					<select id="anuiwp_user_status" name="anuiwp_user_status">
						<?php if ( $user_status == 'pending' ) : ?>
							<option value=""><?php esc_html_e( '-- Status --', 'approve-new-user' ); ?></option>
						<?php endif; ?>
						<?php foreach ( array( 'approved', 'denied' ) as $status ) : ?>
							<option
								value="<?php echo esc_attr( $status ); ?>"<?php selected( $status, $user_status ); ?>><?php echo esc_html( $status ); ?></option>
						<?php endforeach; ?>
					</select>
					<span
						class="description"><?php esc_html_e( 'If user has access to sign in or not.', 'approve-new-user' ); ?></span>
					<?php if ( $user_status == 'pending' ) : ?>
						<br/><span
							class="description"><?php esc_html_e( 'Current user status is pending.', 'approve-new-user' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Save the user status when updating from the user profile.
	 *
	 * @uses edit_user_profile_update
	 * @param int $user_id
	 * @return bool
	 */
	public function save_profile_status_field( $user_id ) {

		if ( !current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

        // if ( wp_verify_nonce($nonce) ) {return;}
		if ( isset( $_POST['anuiwp_edit_user_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['anuiwp_edit_user_wpnonce'] ) ) , 'anuiwp-edit-user-nonce' ) ) {
			if ( !empty( $_POST['anuiwp_user_status'] ) ) {
				$new_status = sanitize_text_field( wp_unslash( $_POST['anuiwp_user_status'] ) );

				if ( $new_status == 'approved' )
					$new_status = 'approve'; else if ( $new_status == 'denied' )
					$new_status = 'deny';

				anuiwp_approve_new_user()->update_user_status( $user_id, $new_status );
			}
		}
	}

	/**
	 * Add bubble for number of users pending to the user menu
	 *
	 * @uses admin_menu
	 */
	public function pending_users_bubble() {
		global $menu;

		$users =get_option( 'anuiwp_user_statuses_count',array());
		if(empty($users))
		{
			$users = anuiwp_approve_new_user()->_get_user_statuses();
		}

		// Get the number of pending users
		$pending_users = $users['pending'] ;

		// Make sure there are pending members
		if ( $pending_users > 0 ) {
			// Locate the key of
			$key = $this->recursive_array_search( 'users.php', $menu );

			// Not found, just in case
			if ( ! $key ) {
				return;
			}

			// Modify menu item
			$menu[$key][0] .= sprintf( '<span class="update-plugins count-%1$s" style="background-color:white;color:black;margin-left:5px;"><span class="plugin-count">%1$s</span></span>', $pending_users );
		}
	}

	/**
	 * Recursively search the menu array to determine the key to place the bubble.
	 *
	 * @param $needle
	 * @param $haystack
	 * @return bool|int|string
	 */
	public function recursive_array_search( $needle, $haystack ) {
		foreach ( $haystack as $key => $value ) {
			$current_key = $key;
			if ( $needle === $value || ( is_array( $value ) && $this->recursive_array_search( $needle, $value ) !== false ) ) {
				return $current_key;
			}
		}
		return false;
	}

	// public function deniend_user(){
	// 	$current_user_id = get_current_user_id();

	// 	$anuiwp_user_status = get_user_meta($current_user_id, 'anuiwp_user_status', true);
	// 	if($anuiwp_user_status == 'denied'){
	// 		wp_logout();
	// 	}
	// }
}