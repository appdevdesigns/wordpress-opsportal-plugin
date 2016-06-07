<?php
namespace ITH\plugins\WP_Ops_Portal;
/**
 * Class Settings
 * @package ITH\plugins\WP_Ops_Portal
 */
class Settings
{
    const WPOP_OPTION_GROUP = 'ops_portal_options';

    function __construct()
    {
        // For register setting
        add_action('admin_init', array($this, 'register_plugin_settings'));

        // Check for database upgrades
        add_action('plugins_loaded', array($this, 'maybe_upgrade'));

        // To save default options upon activation
        register_activation_hook(plugin_basename(WPOP_BASE_FILE), array($this, 'do_upon_plugin_activation'));


    }

    /**
     * Return default db options
     * @return array
     */
    public function get_default_options()
    {
        //Note:: CamelCase keys
        return array(
            'pluginVer' => WPOP_PLUGIN_VER,//always store plugin version in db, it will help in upgrades
            'baseURL' => '',
            'debugCURL' => 0,
            'defaultRole' => '',
            'defaultScopes' => array(),
            'defaultTheme' => 0
        );
    }

    /**
     * Anything you wants to do when user activate this plugin
     */
    public function do_upon_plugin_activation()
    {
        //If db options not exists then update with defaults
        if (get_option(WPOP_OPTION_NAME) == false) {
            update_option(WPOP_OPTION_NAME, $this->get_default_options());
        }

    }

    /**
     * If you has updated the plugin this will update the database
     */
    public function maybe_upgrade()
    {
        //Get fresh options from db
        $db_options = get_option(WPOP_OPTION_NAME);
        //Check if we need to proceed , if no return early
        if ($this->should_proceed_to_upgrade($db_options) === false) return;
        //Else get default options
        $default_options = $this->get_default_options();
        //Merge with db options , preserve old
        $new_options = (empty($db_options)) ? $default_options : array_merge($default_options, $db_options);
        //Update plugin version
        $new_options['plugin_ver'] = WPOP_PLUGIN_VER;
        //Write options back to db
        update_option(WPOP_OPTION_NAME, $new_options);
    }

    /**
     * Check if we need to upgrade database options or not
     * @param $db_options
     * @return bool
     */
    private function should_proceed_to_upgrade($db_options)
    {

        if (empty($db_options) || !is_array($db_options)) return true;

        if (!isset($db_options['plugin_ver'])) return true;

        return version_compare($db_options['plugin_ver'], WPOP_PLUGIN_VER, '<');

    }

    /**
     * Get fail safe options so that index not found never occurs
     * @return array
     */
    public function get_safe_options()
    {
        //Get fresh options from db
        $db_options = get_option(WPOP_OPTION_NAME);

        //Be fail safe, if not array then array_merge may fail
        if (is_array($db_options) === false) {
            $db_options = array();
        }

        //If options not exists in db then init with defaults , also always append default options to existing options
        $db_options = empty($db_options) ? $this->get_default_options() : array_merge($this->get_default_options(), $db_options);
        return $db_options;

    }

    /**
     * Function will print our option page form
     */
    public function load_options_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        require plugin_dir_path(WPOP_BASE_FILE) . '/views/options-page.php';

    }

    /**
     * Register plugin settings, using WP settings API
     */
    public function register_plugin_settings()
    {
        register_setting(self::WPOP_OPTION_GROUP, WPOP_OPTION_NAME, array($this, 'validate_form_post'));
    }

    /**
     * Validate posted form and return validated array
     * @param $in array $_POST array
     * @return array
     */
    public function validate_form_post($in)
    {
        $out = array();
        $out['pluginVer'] = WPOP_PLUGIN_VER; //always save plugin version to db
        $out['baseURL'] = rtrim(sanitize_text_field($in['baseURL']), '/');
        $out['debugCURL'] = isset($in['debugCURL']);
        $out['defaultRole'] = intval($in['defaultRole']);
        $out['defaultScopes'] = (array)$in['defaultScopes'];
        $out['defaultTheme'] = sanitize_text_field($in['defaultTheme']);
        return $out;

    }

    /**
     * Read file content from logs directory
     * @param $file string file name
     * @return string
     */
    private function read_log_file($file)
    {
        $log_dir = dirname(dirname(__FILE__)) . '/logs/';
        $file_path = $log_dir . sanitize_file_name($file);

        if (is_readable($file_path)) {
            $contents = file_get_contents($file_path);
            if (trim($contents) === '') {
                return __('File is empty', WPOP_TEXT_DOMAIN);
            } else {
                return $contents;
            }
        }

        return $file . ' ' . __('not readable or not found', WPOP_TEXT_DOMAIN);

    }

    private function get_roles_array()
    {
        $api = new API();
        $response = $api->getRolesList();

        return $this->check_and_return_response($response);

    }

    private function get_scopes_array()
    {
        $api = new API();
        $response = $api->getScopesList();

        return $this->check_and_return_response($response);

    }

    private function get_themes_array()
    {
        $api = new API();
        $response = $api->getThemesList();

        //default theme does not exist
        $themes[] = array(
            'path' => 0,
            'name' => __('Default', WPOP_TEXT_DOMAIN)
        );

        $data = $this->check_and_return_response($response);
        //theme endpoint returns response in a different format
        if (isset($data['status']) && $data['status'] == 'success') {
            return array_merge($themes, $data['data']);
        }
        return $themes;

    }

    private function check_and_return_response($response)
    {
        if (isset($response['http_code']) && $response['http_code'] == 200) {
            return $response['data'];
        }
        return array();
    }

    /**
     * Get WordPress' current language code
     * @param bool $short Return short version like 'en' when true
     * @return mixed
     */
    private function get_wp_lang_code($short = true)
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
    private function get_localized_label($translations)
    {
        $locale = $this->get_wp_lang_code(true);
        $found = array_filter($translations, function ($item) use ($locale) {
            return ($item['language_code'] === $locale);
        });
        if (!empty($found) && count($found)) {
            //array_filter may return array of array,but we want only first item in array
            $found = current($found);
            return $found['role_label'];
        } else {
            return $translations[0]['role_label'];
        }

    }

}