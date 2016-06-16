<?php
namespace ITH\Plugins\WP_Ops_Portal;

/**
 * Class Shortcode
 *
 * Register and process short-code on front-end
 * @package ITH\Plugins\WP_Ops_Portal
 */
class Shortcode
{
    function __construct()
    {
        // Register our short-code
        add_shortcode('ops_portal', array($this, 'process_shortcode'));
    }

    /**
     * Process short-code and outputs html
     *
     * @todo output valid html, prefix 'data' on custom attribute
     * @return mixed
     */
    public function process_shortcode()
    {
        ob_start();// ob_start is here for a reason
        $db = get_option(WPOP_OPTION_NAME);
        $theme = empty($db['defaultTheme']) ? '' : esc_attr($db['defaultTheme']);

        Util::load_view('short-code', array(
            'theme' => $theme,
            'base_url' => $db['baseURL']
        ));
        
        return ob_get_clean();
    }
}