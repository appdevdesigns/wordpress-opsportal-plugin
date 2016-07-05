<?php
namespace AppDev\Plugins\WP_Ops_Portal;
/**
 * Class Util
 * @package AppDev\Plugins\WP_Ops_Portal
 */
class Util
{
    function __construct()
    {
        // do something
    }

    /**
     * Load a view from views folder
     * @param $file string php File name without extension
     * @param $vars array Variables to pass to view
     */
    public static function load_view($file, $vars = array())
    {
        $file_path = plugin_dir_path(WPOP_BASE_FILE) . 'views/' . sanitize_file_name($file) . '.php';
        if (is_readable($file_path)) {
            //WordPress discourage 'extract' function
            //Make array keys available as variables to view template
            extract($vars);
            unset($vars);
            require $file_path;
        } else {
            trigger_error(sprintf(__('Error locating view file %s for inclusion', 'ops-portal'), esc_html($file_path)), E_USER_ERROR);
        }

    }

    /**
     * Get WordPress' current language code
     * @param bool $short Return short version like 'en' when true
     * @return mixed
     */
    public static function get_wp_lang_code($short = true)
    {
        $code = get_bloginfo('language');
        if ($short == true) {
            return substr($code, 0, 2);
        }
        return $code;

    }

    /**
     * Search for current wp locale in given array; if not found return first
     * @param $translations array
     * @return string localized label
     */
    public static function get_localized_label($translations)
    {
        $locale = self::get_wp_lang_code(true);
        $found = array_filter($translations, function ($item) use ($locale) {
            return ($item['language_code'] === $locale);
        });
        if (!empty($found) && count($found)) {
            //array_filter may return array of array, but we want only first item in array
            $found = current($found);
            return $found['role_label'];
        } else {
            return $translations[0]['role_label'];
        }

    }

    /**
     * Read file content from logs directory
     * @param $file string file name
     * @return string
     */
    public static function read_log_file($file)
    {
        $log_dir = dirname(dirname(__FILE__)) . '/logs/';
        $file_path = $log_dir . sanitize_file_name($file);

        if (is_readable($file_path)) {
            $contents = file_get_contents($file_path);
            if (trim($contents) === '') {
                return __('File is empty', 'ops-portal');
            } else {
                return $contents;
            }
        }

        return $file . ' ' . __('not readable or not found', 'ops-portal');

    }

    /**
     * Get a list of user those are not synced yet
     * @param $count bool Should return number of rows found or not
     * @param $user_ids array Users ids
     * @return mixed
     */
    public static function get_not_synced_users($user_ids = array(), $count = false)
    {
        $args = array(
            'fields' => array('ID', 'user_login', 'user_email'),
            'meta_key' => 'op_synced', 'meta_value' => 0
        );

        if (count($user_ids)) {
            $args['include'] = (array)$user_ids;
        }
        //https://codex.wordpress.org/Class_Reference/WP_User_Query
        $users = new \WP_User_Query($args);
        return ($count) ? $users->get_total() : $users->get_results();

    }

    /**
     * Generate random string
     * @return mixed
     */
    public static function randomString()
    {
        //support php 7.0
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(30));
        }
        //php 5.3 compatibility
        return base64_encode(openssl_random_pseudo_bytes(30));
    }

    /**
     * Delete all transients created by this plugin
     */
    public static function delete_transients()
    {
        //Note:: All transients should prefixed with 'ops_portal_'
        $prefix = 'ops_portal_';

        /**
         * Purge all the transients associated with our plugin.
         * @link https://css-tricks.com/the-deal-with-wordpress-transients/
         */
        global $wpdb;

        $transient_name = esc_sql("_transient_timeout_$prefix%");
        //https://codex.wordpress.org/Class_Reference/wpdb
        $sql = $wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%s' ", $transient_name);
        $transients = $wpdb->get_col($sql);

        // For each transient...
        foreach ($transients as $transient) {
            // Strip away the WordPress prefix in order to arrive at the transient key.
            $key = str_replace('_transient_timeout_', '', $transient);

            // Now that we have the key, use WordPress core to the delete the transient.
            delete_transient($key);

        }

        // But guess what?  Sometimes transients are not in the DB, so we have to do this too:
        wp_cache_flush();
    }
}