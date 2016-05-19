<?php
namespace ITH\plugins\WP_Ops_Portal;

/*
Plugin Name: WP Ops Portal
Plugin URI: https://github.com/ithands
Description: Ops Portal for WordPress
Version: 1.0.0
Author: Ithands
Author URI: http://ithands.com
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-ops-portal
Domain Path: /languages
*/

/* No direct access*/
if (!defined('ABSPATH')) die('Are you serious ?');

define('WPOP_PLUGIN_VER', '1.0.0');
define('WPOP_BASE_FILE', __FILE__);
define('WPOP_OPTION_NAME', 'ops_portal_options');
define('WPOP_TEXT_DOMAIN', 'wp-ops-portal');


/**
 * Registering class auto-loader
 * @requires php v5.3.0
 */
spl_autoload_register(__NAMESPACE__ . '\ops_class_autoloader');

/**
 * Auto-loader for our plugin classes
 * @param $class_name
 * @throws \Exception
 */
function ops_class_autoloader($class_name)
{
    //Make sure this loader work only for this plugin's related classes
    if (false !== strpos($class_name, __NAMESPACE__)) {
        //Find class name, remove namespace prefix
        $cls = str_replace(__NAMESPACE__ . "\\", '', $class_name);
        //Replace _ with - , so class names should be like: Fist_Second_Third and class file name should be like class-first-second-third.php
        $cls = strtolower(str_replace('_', '-', $cls));
        //Class file with full path
        $cls_file = __DIR__ . "/inc/class-" . $cls . ".php";

        if (is_readable($cls_file)) {
            require_once $cls_file;
        } else {
            throw new \Exception('Class file - ' . esc_html($cls_file) . ' not found or not readable');
        }
    }
}

/**
 * Initiate required classes
 * Note: We are not using AJAX anywhere in this plugin
 */
if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)) {
    new Admin();

} else {
    //short-code to be run only in front-end
    new Shortcode();
}

//Sync should be available for both wp-admin and public
new User_Sync();