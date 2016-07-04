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

    /**
     * Base URL of Ops-Portal node-js instance
     * Note:: baseURL should be contain a trailing slash
     * @var string
     */
    private $baseURL = '';

    /**
     * Auth Key to be send with all API calls
     * @var string
     */
    private $authKey = '';

    /**
     * CSRF Token to send with all requests
     * @var null|String
     */
    private $csrfToken = null;

    /**
     * Http class instance
     * @var mixed
     */
    private $http;

    /**
     * How long server response should be cached in wp db
     * @var int  seconds
     */
    const cacheTime = 3600;

    /**
     * Keep same prefix for all transients
     * @var string
     */
    const transientsPrefix = 'ops_portal_';

    /**
     * Plugin options
     * @var array
     */
    private $db;

    function __construct()
    {
        //Set some required vars
        $this->db = get_option(WPOP_OPTION_NAME);
        $this->baseURL = $this->getBaseUrl();
        $this->authKey = $this->getAuthKey();

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
     * Get auth key form db for header
     * @return mixed|string
     */
    private function getAuthKey()
    {
        return (!empty($this->db) && isset($this->db['authKey'])) ? $this->db['authKey'] : '';
    }

    /**
     * Should debug CURL OR not
     * @return bool
     */
    private function curlDebug()
    {
        return (isset($this->db['debugCURL']) && $this->db['debugCURL'] == 1);

    }

    /**
     * Ping baseURL
     *
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
        $token = $this->getRequiredHeaders();

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
        $token = $this->getRequiredHeaders();

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

        $token = $this->getRequiredHeaders();
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

        $token = $this->getRequiredHeaders();
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

        $token = $this->getRequiredHeaders();
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
     * @return bool
     */
    private function checkAndSetTransient($name, $response)
    {
        if (isset($response['http_code']) && $response['http_code'] == 200) {
            //https://codex.wordpress.org/Transients_API
            return set_transient($name, $response, self::cacheTime);
        }
        return false;

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
     * Get required header for API calls
     * Note: Reusing CSRF token if have already
     * @return array
     */
    private function getRequiredHeaders()
    {
        if ($this->csrfToken === null) {
            $response = self::getCSRFToken();

            if (isset($response['data']['_csrf'])) {
                $this->csrfToken = $response['data']['_csrf'];
            }
        }

        return array(
            'X-CSRF-Token: ' . $this->csrfToken,
            'authorization: ' . $this->authKey
        );

    }


}//end class