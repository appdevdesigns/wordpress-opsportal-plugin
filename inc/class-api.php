<?php
namespace AppDev\Plugins\WP_Ops_Portal;

/**
 * Class API
 *
 * Main class that interacts with Ops Portal APIs with help of Http class
 * @package AppDev\Plugins\WP_Ops_Portal
 */
class API
{

    //Base URL of Ops-Portal node-js instance
    //Note:: baseURL should be contain a trailing slash
    private $baseURL = '';

    //CSRF Token to send with all requests
    private $csrfToken = null;

    //Http class instance
    private $http;

    //How long server response should be cached in wp db
    const cacheTime = 3600; //seconds

    //Keep same prefix for all transients
    const transientsPrefix = 'ops_portal_';

    //Plugin options
    private $db;

    function __construct()
    {
        //Set some required vars
        $this->db = get_option(WPOP_OPTION_NAME);
        $this->baseURL = $this->getBaseUrl();

        //HTTP Class instance
        $this->http = Http::instance();
        $this->http->setDebug($this->curlDebug());

    }

    /**
     * Get BaseURL for all CURL calls
     * @return string
     */
    private function getBaseUrl()
    {
        return (!empty($this->db) && isset($this->db['baseURL'])) ? $this->db['baseURL'] : '';
    }

    /**
     * Should debug CURL Or not
     * @return bool
     */
    private function curlDebug()
    {
        return (isset($this->db['debugCURL']) && $this->db['debugCURL'] == 1);

    }

    /**
     * Ping baseURL
     * @GET
     * @return mixed
     */
    public function callHome()
    {
        return $this->http->curl(
            $this->baseURL,
            array(), //no token
            array(), //no payload
            false  //is GET Request
        );
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
     * @todo DRY
     *
     * @GET
     * @param $cached bool Check for saved response first
     * @return array
     */
    public function getRolesList($cached = true)
    {
        if ($cached == true) {
            $saved = get_transient(self::transientsPrefix . 'rolesList');
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

        $this->checkAndSetTransient(self::transientsPrefix . 'rolesList', $response);
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
            $saved = get_transient(self::transientsPrefix . 'scopesList');
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

        $this->checkAndSetTransient(self::transientsPrefix . 'scopesList', $response);
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
            $saved = get_transient(self::transientsPrefix . 'themesList');
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

        $this->checkAndSetTransient(self::transientsPrefix . 'themesList', $response);
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
            set_transient($name, $response, self::cacheTime);
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


}//end class