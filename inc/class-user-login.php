<?php
namespace AppDev\Plugins\WP_Ops_Portal;

/**
 * Class User_Login
 *
 * Class responsible for cookie creation/deletion for local auth
 * @package AppDev\Plugins\WP_Ops_Portal
 */
class User_Login
{
    /**
     * Cookie name that Ops Portal recognize
     * @var string
     */
    const cookieName = 'opsportal_ticket';

    /**
     * API class instance
     * @var API
     */
    private $api;

    function __construct()
    {
        //https://codex.wordpress.org/Plugin_API/Action_Reference/wp_login
        add_action('wp_login', array($this, 'do_after_login'), 10, 2);

        //https://codex.wordpress.org/Plugin_API/Action_Reference/wp_logout
        add_action('wp_logout', array($this, 'do_after_logout'));

        $this->api = new API();
    }

    /**
     * Hook runs after user logs in to WordPress
     * @param $user_login string Unique user name
     * @param $user object WP_User class object
     */
    public function do_after_login($user_login, $user)
    {
        //check if this user is synced with ops-portal or not
        if (false === $this->should_auto_login($user)) return;
        //generate a random string for ticket, make sure it don't cross the limit
        $ticket = Util::randomString();
        //save this ticket to ops portal db
        $this->api->setAuthTicket(array(
            'guid' => $this->get_user_guid($user),
            'ticket' => $ticket
        ));
        //create a cookie with this ticket
        $this->set_cookie($ticket, 0);
    }

    /**
     * Hook runs after user logs out from WordPress
     */
    public function do_after_logout()
    {
        if (isset($_COOKIE[self::cookieName])) {
            unset($_COOKIE[self::cookieName]);
            //delete cookie
            $this->set_cookie(null, time() - 3600);
        }
    }

    /**
     * Set a cookie
     * @param $value String
     * @param int $time Default is zero 0
     */
    private function set_cookie($value, $time = 0)
    {
        setcookie(
            self::cookieName,//name
            $value,         //value
            $time,          //expire time, 0 means when browser close
            '/',            //path
            $this->get_cookie_domain(),    //domain
            is_ssl(),        //secure
            true            //http only
        );

    }

    /**
     * @return string Current WordPress domain with '.' prefix
     */
    private function get_cookie_domain()
    {
        if (is_multisite()) {
            $url = get_blogaddress_by_id(get_current_blog_id());
        } else {
            $url = home_url();
        }
        //allow sub domain cookie sharing , remove 'https://' prefix
        //should not include port number
        return '.' . parse_url($url, PHP_URL_HOST);

    }

    /**
     * Get user's Ops Portal GUID
     * @param $user object WP_User class object
     * @return mixed
     */
    private function get_user_guid($user)
    {
        $user = get_userdata($user->ID);
        return $user->op_user_guid;

    }

    /**
     * Check if this user is synced to ops portal or not
     * @param $user
     * @return bool
     */
    private function should_auto_login($user)
    {
        $user = get_userdata($user->ID);
        return (!empty($user->op_synced) && !empty($user->op_user_guid));
    }
}