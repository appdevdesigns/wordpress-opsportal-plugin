<?php
namespace ITH\plugins\WP_Ops_Portal;

/**
 * Class User_List_Table
 *
 * This class is responsible for modification in WordPress inbuilt user list table 'wp-admin/users.php'
 * @package ITH\plugins\WP_Ops_Portal
 */
class User_List_Table
{
    //User_Sync class instance
    private $sync;

    function __construct()
    {
        add_filter('manage_users_columns', array($this, 'add_new_column'));
        add_action('manage_users_custom_column', array($this, 'show_column_value'), 10, 3);

        //http://wordpress.stackexchange.com/questions/121632/add-a-button-to-users-php
        add_action('admin_footer', array($this, 'add_sync_option'), 11);
        add_action('load-users.php', array($this, 'do_bulk_user_sync'));
        add_action('admin_notices', array($this, 'add_admin_notice'));
    }

    /**
     * Add a new column to user list table
     * @param $columns array
     * @return mixed
     */
    function add_new_column($columns)
    {
        $columns['op_synced'] = 'Synced';
        return $columns;
    }

    /**
     * Return the newly added column value
     * @param $value
     * @param $column_name
     * @param $user_id
     * @return string
     */
    function show_column_value($value, $column_name, $user_id)
    {
        $user = get_userdata($user_id);
        if ('op_synced' == $column_name)
            return ($user->op_synced == 1) ? '<span style="color: #00a000">' . __('Yes') . '</span>' : '<span style="color: #ac0404">' . __('No') . '</span>';

        return $value;
    }

    /**
     * Add a option in bulk user select option box via javascript
     */
    function add_sync_option()
    {
        if (!$this->is_user_screen())
            return;
        ?>
        <script type="text/javascript">
            jQuery(function ($) {
                $('<option>').val('op_bulk_sync').text('<?php _e('Sync to Ops Portal',WPOP_TEXT_DOMAIN) ?>').appendTo("select#bulk-action-selector-top");
            });
        </script>
        <?php
    }

    /**
     * Perform bulk use sync when requested
     */
    function do_bulk_user_sync()
    {
        if ($this->is_valid_request()) {
            $selected_users = $_GET['users'];

            $this->sync = new User_Sync();
            $this->sync->create_bulk_users($selected_users);
        }

    }

    /**
     * Add admin notice when bulk action is finished
     */
    function add_admin_notice()
    {
        if ($this->is_user_screen() && $this->is_valid_request()) {
            echo '<div class="updated notice notice-success is-dismissible"><p><b>' . __('Bulk User Sync Finished !', WPOP_TEXT_DOMAIN) . '</b></p></div>';
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

    /**
     * Check if $_GET params are correct
     * @return bool
     */
    private function is_valid_request()
    {
        return (isset($_GET['action']) && isset($_GET['users']) && $_GET['action'] === 'op_bulk_sync');
    }
}