<?php
namespace AppDev\Plugins\WP_Ops_Portal;

/**
 * Class Shortcode
 *
 * Register and process short-code on front-end
 * @package AppDev\Plugins\WP_Ops_Portal
 */
class Shortcode
{
    function __construct()
    {
        // Register our short-code
        //@link https://codex.wordpress.org/Function_Reference/add_shortcode
        add_shortcode('ops_portal', array($this, 'process_shortcode'));
    }

    /**
     * Process short-code and outputs html
     *
     * @return string
     */
    public function process_shortcode()
    {
        ob_start();// ob_start is here for a reason
        $db = get_option(WPOP_OPTION_NAME);
        $theme = empty($db['defaultTheme']) ? '' : esc_attr($db['defaultTheme']);

        Util::load_view('short-code', array(
            'theme' => $theme,
            'base_url' => $db['baseURL'] //baseURL contains a slash '/' at end
        ));

        return ob_get_clean();
    }
}
