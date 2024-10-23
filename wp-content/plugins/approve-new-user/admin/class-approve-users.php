<?php
/**Wordpress Table Listing  */
class ANUIWP_New_Users_List extends WP_List_Table {
    function __construct() {
        // Set parent defaults
        parent::__construct( array(   //singular name of the listed records
            'plural'    => 'approve_new_user',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    /**Set Table Column Value */
    function column_default($item, $column_name) {
        global $current_user;

        $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'pending_users';

        if($active_tab == 'pending_users') {
            $status = 'pending';
        }elseif($active_tab == 'denied_users') {
            $status = 'denied';
        }else{
            $status = 'approved';
        }

        $user_ID = $item->data->ID;

        switch($column_name){
            case 'user_login':
                $avatar = get_avatar($item->data->ID, 32);

                if ($current_user->ID == $user_ID) {
                    $edit_link = 'profile.php';
                } else {
                    $SERVER_URI  = get_admin_url();
                    if (isset($_SERVER['REQUEST_URI'])) {  $SERVER_URI = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])); }
                    $edit_link = add_query_arg('wp_http_referer', urlencode(esc_url($SERVER_URI)), "user-edit.php?user_id=$user_ID");
                }

                $edit = ($avatar == true) ? ('<strong style="position: relative;top: -17px;left: 10px;"><a class="anuiwp_users_edit_links" href="' . esc_url($edit_link) . '">' . esc_html($item->data->user_login) . '</a></strong>') : ('<strong><a href="' . esc_url($edit_link) . '">' . esc_html($item->data->user_login) . '</a></strong>');

                echo wp_kses_post($avatar . ' ' . $edit);
                break;
            case 'display_name':
                echo (esc_attr(get_user_meta($user_ID, 'first_name', true)) . ' ' . esc_attr(get_user_meta($user_ID, 'last_name', true)));
                break;
            case 'user_email':
                echo sprintf('<a href="mailto:%s" title="%s">%s</a>', esc_attr($item->data->user_email), esc_attr('email:', 'approve-new-user') . esc_attr($item->data->user_email), esc_attr($item->data->user_email));
                break;
            case 'action':
                $approve = ('denied' == $status || 'pending' == $status);
                $deny = ('approved' == $status || 'pending' == $status);

                if ($approve) {
                    $approve_link = get_option('siteurl') . '/wp-admin/admin.php?page=anuiwp-menu-page&user=' . $user_ID . '&status=approve';
                    if (isset($_REQUEST['tab'])) {
                        $approve_link = add_query_arg(array('tab' => sanitize_text_field(wp_unslash($_REQUEST['tab']))), $approve_link);
                    }

                    $approve_link = wp_nonce_url($approve_link, 'anuiwp_approve_new_user_action_anuiwp-menu-page');

                }
                if ($deny) {
                    $deny_link = get_option('siteurl') . '/wp-admin/admin.php?page=anuiwp-menu-page&user=' . $user_ID . '&status=deny';
                    if (isset($_REQUEST['tab'])) {
                        $deny_link = add_query_arg('tab', sanitize_text_field(wp_unslash($_REQUEST['tab'])), $deny_link);
                    }

                    $deny_link = wp_nonce_url($deny_link, 'anuiwp_approve_new_user_action_anuiwp-menu-page');
                }

                if ($approve && $user_ID != get_current_user_id()) {?>
                    <span><a class="button approve-btn" href= "<?php echo esc_url($approve_link) ?>" title="<?php esc_attr_e('Approve', 'approve-new-user');?> <?php esc_attr_e($item->data->user_login);?>"><?php esc_html_e('Approve', 'approve-new-user');?></a> </span>
                <?php } ?>

                <?php if ($deny && $user_ID != get_current_user_id()) {?>
                    <span><a class="button deny-btn" href="<?php echo esc_url($deny_link); ?>" title="<?php esc_attr_e('Deny', 'approve-new-user');?> <?php esc_attr_e($item->data->user_login);?>"><?php echo esc_html('Deny', 'approve-new-user'); ?></a></span>
                <?php }
                break;
            default:
                return print_r($item,true);
        }
    }

    /**Set Table Column */
    function get_columns(){
        $columns = array(
            'user_login'    => __('Username','approve-new-user'),
            'display_name'  => __('Name','approve-new-user'),
            'user_email'    => __('E-mail','approve-new-user'),
            'action'        => __('Action','approve-new-user')
        );
        return $columns;
    }

    /**Set Sort Column */
    function get_sortable_columns() {
        $sortable_columns = array();

        return $sortable_columns;
    }

    /**
     * Table that shows registered users grouped by status
     */
    public function users_list_data() {
        global $current_user;

        $users = [];
        $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'pending_users';

        if($active_tab == 'pending_users') {
            $status = 'pending';
        }elseif($active_tab == 'denied_users') {
            $status = 'denied';
        }else{
            $status = 'approved';
        }

        $approve = ('denied' == $status || 'pending' == $status);
        $deny = ('approved' == $status || 'pending' == $status);

        $user_status = anuiwp_approve_new_user()->_get_user_statuses($status);
        $users = $user_status[$status];

        //filter user by search
		if(isset($_GET['anuiwp_search_box']))
		{
			$searchTerm = sanitize_text_field($_GET['anuiwp_search_box']);

			$filterFunction = function ($users ) use ($searchTerm) {

				$usernameMatches = stripos($users->user_login, $searchTerm) !== false;
				$emailMatches = stripos($users->user_email, $searchTerm) !== false;
				$firstNameMatches = stripos($users->first_name, $searchTerm) !== false;
				$lastNameMatches = stripos($users->last_name, $searchTerm) !== false;
				return $usernameMatches || $emailMatches || $firstNameMatches || $lastNameMatches;
			};
			$users = array_filter($users , $filterFunction);
		}

        $anuiwp_users_transient = apply_filters( 'anuiwp_users_transient', true );
        if(!$anuiwp_users_transient)
        {
            $users = anuiwp_approve_new_user()->_get_users_by_status(false,$status);
        }
        return $users;
    }


    /** Wp Table Initialization */
    function prepare_items() {
        $per_page = 15;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $users_list_data = $this->users_list_data();

        $current_page = $this->get_pagenum();
        $total_items = count($users_list_data);

        $users_list_data = array_slice($users_list_data,(($current_page-1)*$per_page),$per_page);
        $this->items = $users_list_data;
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );
    }
}
$search_query = isset($_GET['anuiwp_search_box']) ? sanitize_text_field($_GET['anuiwp_search_box']) : '';
?>
<div class="wdpgk_list_box wrap">
    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="anuiwp-box">
                    <div class="anuiwp-option-section">
                        <div class="anuiwp-tabbing-box">
                            <?php
                            $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'pending_users';
                            $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
                            $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
                            $search_query = isset($_GET['anuiwp_search_box']) ? sanitize_text_field($_GET['anuiwp_search_box']) : '';
                            ?>
                            <form id="anuiwp_search_form" method="get" style="float: right;">
                                <input type="search" name="anuiwp_search_box" id="anuiwp_search_box" placeholder="Search" data-list=".anuiwp-user-list" value="<?php echo esc_attr($search_query); ?>">
                                <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>" />
                                <?php if (!empty($tab)) : ?>
                                    <input type="hidden" name="tab" value="<?php echo esc_attr($tab); ?>" />
                                <?php endif; ?>
                                <input type="submit" class="button-primary" value="Search" id="anuiwp-search-btn" name="anuiwp-search-btn" />
                            </form>
                            <ul class="anuiwp-tab-list">
                                <li><a href="<?php echo esc_url(admin_url('admin.php?page=anuiwp-menu-page&tab=pending_users')); ?>"
                                class="nav-tab<?php echo $active_tab == 'pending_users' ? ' nav-tab-active' : ''; ?>"><?php esc_html_e('Pending Users', 'approve-new-user');?></a></li>
                                <li><a href="<?php echo esc_url(admin_url('admin.php?page=anuiwp-menu-page&tab=approved_users')); ?>"
                                class="nav-tab<?php echo $active_tab == 'approved_users' ? ' nav-tab-active' : ''; ?>"><?php esc_html_e('Approved Users', 'approve-new-user');?></a></li>
                                <li><a href="<?php echo esc_url(admin_url('admin.php?page=anuiwp-menu-page&tab=denied_users')); ?>"
                                class="nav-tab<?php echo $active_tab == 'denied_users' ? ' nav-tab-active' : ''; ?>"><?php esc_html_e('Denied Users', 'approve-new-user');?></a></li>
                            </ul>
                        </div>
                    </div>

                    <form id="anuiwp-users-list" method="get">
                        <input type="hidden" name="page" value="<?php _e($_REQUEST['page']); ?>" />
                        <?php
                            $usersListTable = new ANUIWP_New_Users_List();
                            $usersListTable->prepare_items();
                            $usersListTable->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>