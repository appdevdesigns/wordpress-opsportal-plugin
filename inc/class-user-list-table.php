<?php
namespace AppDev\Plugins\WP_Ops_Portal;

/**
 * Class User_List_Table
 *
 * This class is responsible for modification in WordPress inbuilt user list table 'wp-admin/users.php'
 * @package AppDev\Plugins\WP_Ops_Portal
 */
class User_List_Table
{


    function __construct()
    {
        add_filter('manage_users_columns', array($this, 'add_new_columns'));
        add_action('manage_users_custom_column', array($this, 'show_column_value'), 10, 3);

        //@link http://wordpress.stackexchange.com/questions/121632/add-a-button-to-users-php
        add_action('admin_footer', array($this, 'add_sync_option'), 11);
        add_action('load-users.php', array($this, 'do_bulk_user_sync'));
        add_action('admin_notices', array($this, 'add_admin_notice'));
    }

    /**
     * Add a new column to user list table
     * @param $columns array
     * @return mixed
     */
    public function add_new_columns($columns)
    {
        $columns['op_synced'] = 'Synced';
        $columns['op_user_id'] = 'Ops Portal ID';
        $columns['op_user_guid'] = 'Ops Portal GUID';
        return $columns;
    }

    /**
     * Return the newly added column value
     * @param $value
     * @param $column_id
     * @param $user_id
     * @return string
     */
    public function show_column_value($value, $column_id, $user_id)
    {
        $user = get_userdata($user_id);
        if ('op_synced' == $column_id)
            return ($user->op_synced == 1) ? '<span style="color: #00a000">' . __('Yes') . '</span>' : '<span style="color: #ac0404">' . __('No') . '</span>';

        if ('op_user_id' == $column_id)
            return (empty($user->op_user_id)) ? __('NA', 'ops-portal') : $user->op_user_id;

        if ('op_user_guid' == $column_id)
            return (empty($user->op_user_guid)) ? __('NA', 'ops-portal') : $user->op_user_guid;

        return $value;
    }

    /**
     * Add a option in bulk user select option box via javascript
     */
    public function add_sync_option()
    {
        if (!$this->is_user_screen())
            return;
        //Is it good practice to put js inside a php file ?
        ?>
        <script type="text/javascript">
            (function (doc) {
                'use strict';
                doc.addEventListener('DOMContentLoaded', function (event) {
                    var select = doc.querySelector('#bulk-action-selector-top');
                    if (select === null) return;
                    var op = doc.createElement('option');
                    op.value = 'op_bulk_sync';
                    op.text = '<?php _e('Sync to Ops Portal', 'ops-portal') ?>';
                    select.appendChild(op);
                });
            })(document);
        </script>
        <?php
    }

    /**
     * Perform bulk use sync when requested
     */
    public function do_bulk_user_sync()
    {
        if (isset($_GET['action']) && $_GET['action'] === 'op_bulk_sync' && isset($_GET['users'])) {
            $selected_users = $_GET['users'];

            $sync = new User_Sync();
            $sync->create_bulk_users($selected_users);
            //It is necessary to redirect user to same page, this will remove any query string from page, prevent re-submission
            $paged = (isset($_GET['paged'])) ? '&paged=' . intval($_GET['paged']) : '';
            wp_redirect(admin_url('/users.php?op_synced=1' . $paged), 301);
            exit;
        }

    }

    /**
     * Add admin notice when bulk action is finished
     */
    public function add_admin_notice()
    {
        if ($this->is_user_screen() && isset($_GET['op_synced'])) {
            Util::load_view('admin-notice', array(
                    'type' => 'success',
                    'message' => __('Bulk User Sync Finished !', 'ops-portal')
                )
            );
        }
    }

    /**
     * Check if user is viewing users.php page
     * @return bool
     */
    private function is_user_screen()
    {
        $screen = get_current_screen();
        return ($screen->id == "users");

    }

}