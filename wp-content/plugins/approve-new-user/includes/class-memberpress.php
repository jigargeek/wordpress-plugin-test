<?php
add_action( 'mepr-product-registration-metabox', 'anuiwpmp_add_registration_field', 10, 3 );
function anuiwpmp_add_registration_field($product) {

    $anuiwp_approval = get_post_meta( $product->rec->ID,'_mepr_anuiwp_approval', true );
    ?>
    <div id="anuiwp-mp-require-approval" class="mepr-product-adv-item">
        <input type="checkbox" name="_mepr_anuiwp_approval" id="_mepr_anuiwp_approval" <?php echo $anuiwp_approval ? 'checked' : ''; ?> />
        <label for="_mepr_anuiwp_approval"><?php _e('Members Require anuiwp Approval', 'approve-new-user'); ?></label>
        <?php
        MeprAppHelper::info_tooltip('_mepr_anuiwp_approval',
            __('Members Require anuiwp Approval', 'approve-new-user'),
            __('Enable this option if you want membership to be only activated after user profile is approved using New User Approve settings, If users profile is denied then the membership will become inactive', 'approve-new-user')
        ); ?>
    </div>
    <?php
}

add_action( 'mepr-membership-save-meta', 'anuiwpmp_add_registration_field_save', 10, 3 );
function anuiwpmp_add_registration_field_save($product) {
    
    if (isset($_POST['post_ID'])) {
        $anuiwp_approval = 0;
        if(isset($_POST['_mepr_anuiwp_approval']) && $_POST['_mepr_anuiwp_approval'] == 'on') {
            $anuiwp_approval = 1;
        }

        update_post_meta(absint($_POST['post_ID']), '_mepr_anuiwp_approval', $anuiwp_approval);
    }

}

function memberpress_add_anuiwp_cloumn( $cols ) {
    
    $cols['col_anuiwp_approval'] = __('Approval', 'approve-new-user');
    return $cols;
}
add_filter( 'mepr-admin-members-cols', 'memberpress_add_anuiwp_cloumn' );



function memberpress_add_anuiwp_rows($attributes, $rec, $column_name, $column_display_name){
    if($column_name == 'col_anuiwp_approval'){
      
        $user_status = anuiwp_approve_new_user()->get_user_status( $rec->ID );

        $approve_link = add_query_arg( array( 'anuiwp-action' => 'approve', 'user' => $rec->ID ) );
		$approve_link = remove_query_arg( array( 'new_role' ), $approve_link );
		$approve_link = wp_nonce_url( $approve_link, 'approve-new-user-mempr' );

		$deny_link = add_query_arg( array( 'anuiwp-action' => 'deny', 'user' => $rec->ID) );
		$deny_link = remove_query_arg( array( 'new_role' ), $deny_link );
		$deny_link = wp_nonce_url( $deny_link, 'approve-new-user-mempr' );

		$approve_action = '<a style="color:green" href="' . esc_url( $approve_link ) . '">' . __( 'Approve', 'approve-new-user' ) . '</a>';
		$deny_action = '<a style="color:red" href="' . esc_url( $deny_link ) . '">' . __( 'Deny', 'approve-new-user' ) . '</a>';

		if ( $user_status == 'pending' ) {
            ?>
            <td> 
            <p><?php echo ucfirst($user_status); ?> </p>
            <?php
            if ( $rec->ID != get_current_user_id() && !is_super_admin( $rec->ID )) {
                ?>
                <p><?php echo $approve_action; ?> | <?php echo $deny_action; ?></p>
                </td>
                <?php 
            }
		} else if ( $user_status == 'approved' ) {
            ?>
            <td > 
            <p><?php echo ucfirst($user_status); ?> </p>
            <?php
            if ( $rec->ID != get_current_user_id() && !is_super_admin( $rec->ID )) {
                ?>
                <p><?php echo $deny_action; ?></p>
                </td>
                <?php
            }
		} else if ( $user_status == 'denied' ) {
            ?>
            <td > 
            <p><?php echo ucfirst($user_status); ?> </p>
            <?php
            if ( $rec->ID != get_current_user_id() && !is_super_admin( $rec->ID )) {
                ?>
                <p><?php echo $approve_action; ?></p>
                </td>
                <?php
            }
		}
        ?>
        
        <?php

    }
}
add_action( 'mepr_members_list_table_row', 'memberpress_add_anuiwp_rows', 10, 4 );

add_action( 'admin_head', 'memberpress_anuiwp_col_width');
function memberpress_anuiwp_col_width() {
  echo '<style>
    .column-col_anuiwp_approval {
        width: 10%;
    } 
  </style>';
}

add_action('memberpress_page_memberpress-members', 'update_user_status_from_memberpress_members_page');

function update_user_status_from_memberpress_members_page() {

    if ( isset( $_GET['anuiwp-action'] ) && in_array( $_GET['anuiwp-action'], array( 'approve', 'deny' ) ) && !isset( $_GET['new_role'] ) ) {        
        check_admin_referer( 'approve-new-user-mempr' );

        $status = sanitize_key( $_GET['anuiwp-action'] );
        $user = absint( $_GET['user'] );

        anuiwp_approve_new_user()->update_user_status( $user, $status );
    }
}

