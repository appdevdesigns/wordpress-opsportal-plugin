<?php
namespace ITH\plugins\WP_Ops_Portal;

/**
 * Class API
 * @package ITH\plugins\WP_Ops_Portal
 */
class API
{

    //Base URL for Ops-Portal instance
    private $baseURL = '';

    //CSRF Token to send with all requests
    private $csrfToken = null;

    //http class instance
    private $http;

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


    private function setBaseUrl()
    { 
        $db = get_option(WPOP_OPTION_NAME);
        if (false !== $db) {
            //notice: append a slash at end
            $this->baseURL = $db['baseURL'] . '/';
        }
    }


}//end class