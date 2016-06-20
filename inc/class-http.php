<?php

namespace ITH\Plugins\WP_Ops_Portal;

/**
 * Class Http
 *
 * A portable class that can be used anywhere to perform CURL calls
 * @package ITH\Plugins\WP_Ops_Portal
 * @todo How can we replace this with WordPress inbuilt HTTP API - https://codex.wordpress.org/HTTP_API
 */
class Http
{

    private static $instances = array();

    //Directory path; where to store logs
    private $logDir;

    //The cookie file will be created in system's temp folder
    //Cookie file name will be same for each single php request
    private $cookieFile;

    //Debug CURL
    private $debug;

    private function __construct()
    {
        if (false === $this->isCurlInstalled()) {
            trigger_error(__('CURL is not installed, Ops Portal Plugin requires CURL pre-installed', WPOP_TEXT_DOMAIN), E_USER_ERROR);
        }

        $this->logDir = dirname(dirname(__FILE__)) . '/logs/';
        $this->cookieFile = tempnam(sys_get_temp_dir(), "curl_cookie");
        $this->debug = false;
    }

    /**
     * Function to instantiate our class and make it a singleton
     */
    public static function instance()
    {
        $cls = get_called_class();
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static;
        }
        return self::$instances[$cls];
    }

    protected function __clone()
    {
        //don't not allow clones
    }

    public function __wakeup()
    {
        return new \Exception("Cannot unserialize singleton");
    }


    /**
     * Send http requests with curl.
     *
     * @param mixed $url The url to send data
     * @param array $params (default: array()) Array with key/value pairs to send
     * @param array $headers (default: array()) Headers array with key/value pairs to send
     * @param bool $post (default: false) True when sending with POST
     *
     * @return bool|array
     */
    public function curl($url, $params = array(), $headers = array(), $post = false)
    {
        if (empty($url)) {
            return false;
        }

        if (!$post && !empty($params)) {
            $url = $url . '?' . http_build_query($params);
        }

        $curl = self::createCurlObject($url, $params, $headers, $post);
        $data = curl_exec($curl);

        //Debug if u want
        /* if(false === $data){
            echo curl_errno($curl);
            echo curl_error($curl);
        } */

        $http_code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);

        //Save response to a file for debugging
        if ($this->debug === true) {
            $response = fopen($this->logDir . 'curl_response.log', 'w');
            fwrite($response, $data);
            fclose($response);
        }

        curl_close($curl);

        return array(
            'http_code' => $http_code,
            'data' => self::isJson($data) ? json_decode($data, true) : $data
        );

    }

    /**
     * Create CURL object
     *
     * @link http://php.net/manual/en/function.curl-setopt.php
     * @param $url string
     * @param $params array
     * @param $headers array
     * @param $post bool Is POST request ?
     * @return mixed
     */
    public function createCurlObject($url, $params, $headers, $post)
    {
        $curl = curl_init($url);

        $default_headers = array(
            'Accept: application/json',
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        );

        //Apply custom headers
        if (is_array($headers) && count($headers)) {
            $default_headers = array_merge($default_headers, $headers);
        }

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_HTTPHEADER => $default_headers,
            CURLOPT_USERAGENT => 'OpsPortal WordPress Plugin',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile
        );


        //Write debug info in a text file
        if ($this->debug === true) {
            $options += array(
                CURLOPT_VERBOSE => true,
                CURLOPT_STDERR => fopen($this->logDir . 'curl_stderr.log', 'w')
            );
        }

        //Only if it is a POST request
        if ($post) {
            $options += array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $params,
            );
        }

        curl_setopt_array($curl, $options);

        return $curl;
    }

    /**
     * Check if a string is a valid json
     *
     * @link http://stackoverflow.com/questions/6041741/fastest-way-to-check-if-a-string-is-json-in-php
     * @param $string
     * @return bool
     */
    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Enable CURL debugging
     * @param $val bool Enable when true
     */
    public function setDebug($val = false)
    {
        $this->debug = (bool)$val;
    }

    /**
     * Check if CURL is installed on server
     * @return bool
     */
    private function isCurlInstalled()
    {
        return (extension_loaded('curl') && function_exists('curl_version'));

    }


}