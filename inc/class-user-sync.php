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
    function create_ops_portal_user($user_id)
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
     * Add a flag along with user records if user has been synced or not
     * @param $user_id
     * @param $response array Server response
     */
    private function add_user_meta($user_id, $response)
    {
        $op_synced = ((false !== empty($response)) && $response['http_code'] == 201);
        update_user_meta($user_id, 'op_synced', $op_synced);
    }


    /**
     * Hook runs before user get deleted from WP Database
     * @param $user_id
     */
    function delete_ops_portal_user($user_id)
    {
        //
    }
}