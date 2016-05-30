<?php
namespace ITH\plugins\WP_Ops_Portal;

/**
 * Class User_List_Table
 * @package ITH\plugins\WP_Ops_Portal
 */
class User_List_Table
{
    function __construct()
    {
        add_filter('manage_users_columns', array($this, 'add_new_column'));
        add_action('manage_users_custom_column', array($this, 'show_column_value'), 10, 3);
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
            return ($user->op_synced == 1) ? '<span style="color: #00a000">Yes</span>' : '<span style="color: #ac0404">No</span>';

        return $value;
    }
}