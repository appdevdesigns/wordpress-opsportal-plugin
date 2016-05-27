<?php
namespace ITH\plugins\WP_Ops_Portal;

/**
 * Class User_Sync
 * @package ITH\plugins\WP_Ops_Portal
 */
class User_Sync
{
    //API class instance
    private $api;

    //user_meta key name
    const sync_flag = 'op_synced';

    function __construct()
    {
        //https://codex.wordpress.org/Plugin_API/Action_Reference/user_register
        add_action('user_register', array($this, 'create_ops_portal_user'), 10, 1);

        //https://codex.wordpress.org/Plugin_API/Action_Reference/delete_user
        //add_action('delete_user', array($this, 'delete_ops_portal_user'), 10, 1);

        $this->api = new API();
    }

    /**
     * Hook runs after new user is inserted into WP database
     * @param $user_id
     * @return array
     */
    public function create_ops_portal_user($user_id)
    {
        //https://codex.wordpress.org/Function_Reference/get_userdata
        $user_info = get_userdata($user_id);

        $request = array(
            'username' => $user_info->user_login
        );

        $response = $this->api->createUser($request);
        $this->add_user_meta($user_id, $response);
        return $response;

    }

    /**
     * Sync users in bulk
     */
    public function create_bulk_users()
    {
        $users = $this->get_not_synced_users();

        foreach ($users as $user) {

            $request = array(
                'username' => $user->user_login
            );

            $response = $this->api->createUser($request);
            $this->add_user_meta($user->ID, $response);

        }

    }


    /**
     * Add a flag along with user records if user has been synced or not
     * @param $user_id
     * @param $response array Server response
     */
    private function add_user_meta($user_id, $response)
    {
        $op_synced = 0;
        if (isset($response['http_code']) && $response['http_code'] == 201) {
            $op_synced = 1;
        }
        update_user_meta($user_id, self::sync_flag, $op_synced);
    }

    /**
     * Get a list of user those are not synced yet
     * @param $count bool Should return number of rows found or not
     * @return mixed
     */
    public function get_not_synced_users($count = false)
    {
        $args = array(
            'fields' => array('ID', 'user_login', 'user_email'),
            'meta_key' => self::sync_flag, 'meta_value' => 0
        );
        //https://codex.wordpress.org/Class_Reference/WP_User_Query
        $users = new \WP_User_Query($args);
        return ($count) ? $users->get_total() : $users->get_results();

    }


    /**
     * Hook runs before user get deleted from WP Database
     * @param $user_id
     */
    public function delete_ops_portal_user($user_id)
    {
        //delete this user from ops portal as well ?
    }
}