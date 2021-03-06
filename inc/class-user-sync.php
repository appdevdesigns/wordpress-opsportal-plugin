<?php
namespace AppDev\Plugins\WP_Ops_Portal;

/**
 * Class User_Sync
 *
 * Hook WordPress to sync WP Users to Ops Portal
 * @package AppDev\Plugins\WP_Ops_Portal
 */
class User_Sync
{
    /**
     * API class instance
     * @var API
     */
    private $api;

    /**
     * Store plugin db options
     * @var array
     */
    private $db;

    function __construct()
    {
        //@link https://codex.wordpress.org/Plugin_API/Action_Reference/user_register
        add_action('user_register', array($this, 'create_single_user'), 10, 1);

        //@link https://codex.wordpress.org/Plugin_API/Action_Reference/delete_user
        //add_action('delete_user', array($this, 'delete_ops_portal_user'), 10, 1);

        $this->api = new API();
        $this->db = get_option(WPOP_OPTION_NAME);
    }

    /**
     * Hook runs after new user is inserted into WP database
     * @param $user_id
     * @return array
     */
    public function create_single_user($user_id)
    {
        //@link https://codex.wordpress.org/Function_Reference/get_userdata
        $user = get_userdata($user_id);
        return $this->send_create_user_call($user, $user_id);

    }

    /**
     * Sync users in bulk
     * @param $user_ids array Array of user ids
     */
    public function create_bulk_users($user_ids)
    {
        $user_ids = array_unique($user_ids);
        $users = Util::get_not_synced_users($user_ids);

        //todo Can we create all users in a single request ?
        foreach ($users as $user) {
            $this->send_create_user_call($user, $user->ID);
        }

    }

    /**
     * The actual function that send payload to create user endpoint
     * @param $user array
     * @param $user_id int
     * @return array Server response
     */
    public function send_create_user_call($user, $user_id)
    {
        $response = $this->api->createUser($this->build_create_user_request($user));
        $this->add_user_meta($user_id, $response);
        $this->set_user_role_and_scopes($response);
        return $response;
    }

    /**
     * Create the request body for create user request
     * @param $user
     * @return array
     */
    private function build_create_user_request($user)
    {
        return array(
            'username' => $user->user_login,
            'email' => $user->user_email,
            'isActive' => 1 //Activate as soon as they register ?
        );
    }

    /**
     * Set role and scope for newly inserted user
     * @param $response array Server response from create user endpoint
     */
    private function set_user_role_and_scopes($response)
    {
        if (isset($response['http_code']) && $response['http_code'] == 201) {
            $db = $this->db;
            //The default role and scope should exist
            if (!empty($db['defaultRole']) && !empty($db['defaultScopes'])) {
                $args = array(
                    'user' => $response['data']['id'],
                    'role' => $db['defaultRole'],
                    'enabled' => 1
                );
                //API requires scope to be in this format
                //scope[]=1&scope[]=2&scope[]=3
                foreach ($db['defaultScopes'] as $id) {
                    $args['scope[]'] = $id;
                }
                $this->api->setRoleScopes($args);
            }

        }
    }

    /**
     * Keep some additional info along with user records if user has been synced or not
     * @param $user_id
     * @param $response array Server response
     */
    private function add_user_meta($user_id, $response)
    {
        $op_synced = 0;
        if (isset($response['http_code']) && $response['http_code'] == 201) {
            $op_synced = 1;
            update_user_meta($user_id, 'op_user_id', $response['data']['id']);
            update_user_meta($user_id, 'op_user_guid', $response['data']['guid']);
        }
        update_user_meta($user_id, 'op_synced', $op_synced);
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
