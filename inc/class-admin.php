<?php
namespace AppDev\Plugins\WP_Ops_Portal;

/**
 * Class Admin
 * @package AppDev\Plugins\WP_Ops_Portal
 */
class Admin
{

    /**
     * Plugin slug to be used across system
     * @var string
     */
    const PLUGIN_SLUG = 'ops_portal';

    /**
     * Settings class instance
     * @var Settings
     */
    private $settings;

    function __construct()
    {
        // Add settings link under admin->settings menu
        add_action('admin_menu', array($this, 'add_link_to_settings_menu'));

        // Add settings link to plugin list page
        add_filter('plugin_action_links_' . plugin_basename(WPOP_BASE_FILE), array($this, 'add_plugin_actions_links'), 10, 2);

        // Hook when plugin gets deactivated
        register_deactivation_hook(plugin_basename(WPOP_BASE_FILE), array($this, 'do_upon_plugin_deactivation'));

        // Be multilingual
        add_action('plugins_loaded', array($this, 'do_upon_plugins_loaded'));

        //Init required classes
        $this->settings = new Settings();
        new User_List_Table();

    }


    /**
     * Adds link to Plugin Option page and do related stuff
     */
    public function add_link_to_settings_menu()
    {
        $page_hook_suffix = add_submenu_page(
            'options-general.php',
            'Ops Portal Configs', //page title
            'Ops Portal', //menu text
            'manage_options', //capability
            self::PLUGIN_SLUG,
            array($this->settings, 'load_options_page'));

        //Add css,js only on this page
        add_action('admin_print_scripts-' . $page_hook_suffix, array($this, 'add_admin_assets'));
    }


    /**
     * Adds a 'Settings' link for this plugin on plugin listing page
     *
     * @param $links
     * @return array  Links array
     */
    public function add_plugin_actions_links($links)
    {
        //Only visible to admins
        if (current_user_can('manage_options')) {
            $build_url = add_query_arg('page', self::PLUGIN_SLUG, 'options-general.php');
            array_unshift(
                $links,
                sprintf('<a href="%s">%s</a>', $build_url, __('Settings'))
            );
        }

        return $links;
    }

    /**
     * Anything you wants to do when all plugins has been loaded
     */
    public function do_upon_plugins_loaded()
    {
        load_plugin_textdomain('ops-portal', false, dirname(plugin_basename(WPOP_BASE_FILE)) . '/languages/');
    }

    /**
     * Anything you wants to do when this plugin get deactivated
     */
    public function do_upon_plugin_deactivation()
    {
        Util::delete_transients();
    }

    /**
     * Use this function load any number of js or css on plugin option page
     */
    public function add_admin_assets()
    {
        wp_enqueue_style('ops-admin', plugins_url('/assets/css/option-page.css', WPOP_BASE_FILE), array(), WPOP_PLUGIN_VER);
        wp_enqueue_script('ops-admin', plugins_url("/assets/js/option-page.js", WPOP_BASE_FILE), array('jquery'), WPOP_PLUGIN_VER, false);
    }

}