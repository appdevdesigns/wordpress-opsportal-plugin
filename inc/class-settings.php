<?php
namespace ITH\plugins\WP_Ops_Portal;
/**
 * Class Settings
 * @package ITH\plugins\WP_Ops_Portal
 */
class Settings
{
    const WPOP_OPTION_GROUP = 'wpop_plugin_options';

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
        );
    }

    /**
     * Any thing you wants to do when user activate this plugin
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
     * @param $in
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
                return 'File is empty';
            } else {
                return $contents;
            }
        }

        return $file . ' not readable or not found';

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

    private function check_and_return_response($response)
    {
        if (isset($response['http_code']) && $response['http_code'] == 200) {
            return $response['data'];
        }
        return array();
    }

}