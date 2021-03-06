<?php
namespace AppDev\Plugins\WP_Ops_Portal;
/**
 * Class Settings
 * @package AppDev\Plugins\WP_Ops_Portal
 */
class Settings
{
    const WPOP_OPTION_GROUP = 'ops_portal_options';
    const PLUGIN_SLUG = 'ops_portal';

    /**
     * API Class instance
     * @var API
     */
    private $api;

    function __construct()
    {
        // For register setting
        add_action('admin_init', array($this, 'register_plugin_settings'));

        // Check for database upgrades
        add_action('plugins_loaded', array($this, 'maybe_upgrade'));

        // To save default options upon activation
        register_activation_hook(plugin_basename(WPOP_BASE_FILE), array($this, 'do_upon_plugin_activation'));

        $this->api = new API();
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
            'authKey' => '',
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
        if (false == get_option(WPOP_OPTION_NAME)) {
            update_option(WPOP_OPTION_NAME, $this->get_default_options());
        }

    }

    /**
     * If you has updated the plugin this will update the database
     * Runs after 'plugins_loaded' hook
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

        //The data you want to pass on view
        $data = array(
            'option_group' => self::WPOP_OPTION_GROUP,
            'db' => $this->get_safe_options(),
            'roles' => $this->get_roles_array(),
            'scopes' => $this->get_scopes_array(),
            'themes' => $this->get_themes_array(),
            'curl_response' => Util::read_log_file('curl_response.log'),
            'curl_stderr' => Util::read_log_file('curl_stderr.log'),
        );

        Util::load_view('options-page', $data);
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
        $errors = array();

        //check for valid url
        if (filter_var(trim($in['baseURL']), FILTER_VALIDATE_URL) === false) {
            $out['baseURL'] = '';
            $errors[] = __('Base URL was not a valid URL.', 'ops-portal');
        } else {
            $out['baseURL'] = trailingslashit(sanitize_text_field($in['baseURL']));
        }

        if (empty($in['authKey'])) {
            $out['authKey'] = '';
            $errors[] = __('Auth Key is required.', 'ops-portal');
        } else {
            $out['authKey'] = sanitize_text_field($in['authKey']);
        }

        if (empty($in['defaultScopes'])) {
            $errors[] = __('At-least one scope should be selected.', 'ops-portal');
        }

        if (empty($in['defaultRole'])) {
            $errors[] = __('A role should be selected.', 'ops-portal');
        }

        if (isset($in['submit-flush'])) {
            //delete transients
            Util::delete_transients();
            add_settings_error(WPOP_OPTION_NAME, 'ops-portal-flushed', __('Cache has been cleared', 'ops-portal'), 'updated');
        }

        //show all form errors in a single notice
        if (!empty($errors)) {
            add_settings_error(WPOP_OPTION_NAME, 'ops-portal', implode('<br>', $errors));
        }

        $out['debugCURL'] = isset($in['debugCURL']);
        $out['defaultRole'] = isset($in['defaultRole']) ? intval($in['defaultRole']) : '';
        $out['defaultScopes'] = isset($in['defaultScopes']) ? (array)$in['defaultScopes'] : array();
        $out['defaultTheme'] = sanitize_text_field($in['defaultTheme']);
        return $out;

    }


    /**
     * The available roles array
     * @return array
     */
    private function get_roles_array()
    {
        $response = $this->api->getRolesList();

        return $this->check_and_return_response($response);

    }

    /**
     * The available scopes array
     * @return array
     */
    private function get_scopes_array()
    {
        $response = $this->api->getScopesList();

        return $this->check_and_return_response($response);

    }

    /**
     * The available themes array
     * @return array
     */
    private function get_themes_array()
    {
        $response = $this->api->getThemesList();

        $data = $this->check_and_return_response($response);
        //theme endpoint returns response in a different format
        if (isset($data['status']) && $data['status'] == 'success') {
            return $data['data'];
        }
        return array();

    }

    /** Check server response and always return array
     * @param $response
     * @return array
     */
    private function check_and_return_response($response)
    {
        if (isset($response['http_code']) && $response['http_code'] == 200) {
            return $response['data'];
        }
        return array();
    }

}
