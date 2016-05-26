<?php
namespace ITH\plugins\WP_Ops_Portal;

/**
 * Class Admin
 * @package ITH\plugins\WP_Ops_Portal
 */
class Admin
{

    const PLUGIN_SLUG = 'ops_portal';

    private $settings;

    function __construct()
    {
        /* Add settings link under admin->settings menu */
        add_action('admin_menu', array($this, 'add_to_settings_menu'));

        /* Add settings link to plugin list page */
        add_filter('plugin_action_links_' . plugin_basename(WPOP_BASE_FILE), array($this, 'add_plugin_actions_links'), 10, 2);

        //Be multilingual
        //add_action('plugins_loaded', array($this, 'load_text_domain'));

        $this->settings = new Settings();
        new User_List_Table();

    }


    /**
     * Adds link to Plugin Option page and do related stuff
     */
    function add_to_settings_menu()
    {
        $page_hook_suffix = add_submenu_page(
            'options-general.php',
            'Ops Portal Configs', //page title
            'Ops Portal', //menu text
            'manage_options', //capability
            self::PLUGIN_SLUG,
            array($this->settings, 'load_options_page'));

        add_action('admin_print_scripts-' . $page_hook_suffix, array($this, 'add_admin_assets'));
    }


    /**
     * Adds a 'Settings' link for this plugin on plugin listing page
     *
     * @param $links
     * @return array  Links array
     */
    function add_plugin_actions_links($links)
    {

        if (current_user_can('manage_options')) {
            $build_url = add_query_arg('page', self::PLUGIN_SLUG, 'options-general.php');
            array_unshift(
                $links,
                sprintf('<a href="%s">%s</a>', $build_url, __('Settings'))
            );
        }

        return $links;
    }

    function load_text_domain()
    {
        load_plugin_textdomain(WPOP_TEXT_DOMAIN, false, dirname(plugin_basename(WPOP_BASE_FILE)) . '/languages/');
    }

    /**
     * Use this function load any number of js or css on plugin option page
     */
    function add_admin_assets()
    {
        wp_enqueue_style('ops-admin', plugins_url('/assets/css/option-page.css', WPOP_BASE_FILE), array(), WPOP_PLUGIN_VER);
        wp_enqueue_script('ops-admin', plugins_url("/assets/js/option-page.js", WPOP_BASE_FILE), array('jquery'), WPOP_PLUGIN_VER, false);
    }

}