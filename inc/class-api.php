<?php
namespace ITH\plugins\WP_Ops_Portal;

/**
 * Class API
 *
 * Main class that interacts with Ops Portal APIs with help of Http class
 * @package ITH\plugins\WP_Ops_Portal
 */
class API
{

    //Base URL of Ops-Portal node-js instance
    //Note:: baseURL will contains a trailing slash
    private $baseURL = '';

    //CSRF Token to send with all requests
    private $csrfToken = null;

    //Http class instance
    private $http;

    //How long server response should be cached in wp db
    const cache_time = 3600; //seconds

    function __construct()
    {
        $this->http = Http::get_instance();
        $this->setBaseUrl();

    }

    /**
     * Create a new user
     *
     * @POST
     * @param $data array
     * @return array Response array
     */
    public function createUser($data)
    {
        $token = $this->getCSRFTokenHeader();

        return $this->http->curl(
            $this->baseURL . 'appdev-core/siteuser/create',
            $data,
            $token,
            true  //is POST Request
        );
    }

    /**
     * Set role and scope for a user
     *
     * @POST
     * @param $data array
     * @return array
     */
    public function setRoleScopes($data)
    {
        $token = $this->getCSRFTokenHeader();

        return $this->http->curl(
            $this->baseURL . 'appdev-core/permission',
            $data,
            $token,
            true
        );

    }

    /**
     * Get a list of roles available
     *
     * @GET
     * @param $cached bool Check for saved response first
     * @return array
     */
    public function getRolesList($cached = true)
    {
        if ($cached == true) {
            $saved = get_transient('ops_portal_rolesList');
            if (!empty($saved)) {
                return $saved;
            }
        }

        $token = $this->getCSRFTokenHeader();
        $response = $this->http->curl(
            $this->baseURL . 'appdev-core/permissionrole',
            array(),
            $token,
            false
        );

        $this->checkAndSetTransient('ops_portal_rolesList', $response);
        return $response;
    }


    /**
     * Get a list of scopes available
     *
     * @GET
     * @param $cached bool Check for saved response first
     * @return array
     */
    public function getScopesList($cached = true)
    {
        if ($cached == true) {
            $saved = get_transient('ops_portal_scopesList');
            if (!empty($saved)) {
                return $saved;
            }
        }

        $token = $this->getCSRFTokenHeader();
        $response = $this->http->curl(
            $this->baseURL . 'appdev-core/permissionscope',
            array(),
            $token,
            false
        );

        $this->checkAndSetTransient('ops_portal_scopesList', $response);
        return $response;
    }

    /**
     * Get a list of themes available
     *
     * @GET
     * @param $cached bool Check for saved response first
     * @return array
     */
    public function getThemesList($cached = true)
    {
        if ($cached == true) {
            $saved = get_transient('ops_portal_themesList');
            if (!empty($saved)) {
                return $saved;
            }
        }

        $token = $this->getCSRFTokenHeader();
        $response = $this->http->curl(
            $this->baseURL . 'opstool-wordpress-plugin/theme',
            array(),
            $token,
            false
        );

        $this->checkAndSetTransient('ops_portal_themesList', $response);
        return $response;
    }

    /**
     * Check server response and store it into persistent storage
     * @param $name  string Transient name
     * @param $response array Server response
     */
    private function checkAndSetTransient($name, $response)
    {
        if (isset($response['http_code']) && $response['http_code'] == 200) {
            //https://codex.wordpress.org/Transients_API
            set_transient($name, $response, self::cache_time);
        }

    }

    /**
     * Get CSRF token response array
     *
     * @GET
     * @return array Response array
     */
    public function getCSRFToken()
    {
        return $this->http->curl($this->baseURL . 'csrfToken');
    }

    /**
     * Reuse CSRF token if have already
     * @return array
     */
    private function getCSRFTokenHeader()
    {
        if ($this->csrfToken === null) {
            $response = self::getCSRFToken();

            if (isset($response['data']['_csrf'])) {
                $this->csrfToken = $response['data']['_csrf'];
            }
        }
        return array('X-CSRF-Token: ' . $this->csrfToken);

    }

    /**
     * Set base URL for all future CURL calls
     */
    private function setBaseUrl()
    {
        $db = get_option(WPOP_OPTION_NAME);

        if (!empty($db) && isset($db['baseURL'])) {
            //notice: append a slash at end
            $this->baseURL = $db['baseURL'] . '/';
        }
    }


}//end class