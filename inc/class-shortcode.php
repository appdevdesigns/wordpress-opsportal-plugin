<?php
namespace ITH\plugins\WP_Ops_Portal;

/**
 * Class Shortcode
 *
 * Register and process short-code on front-end
 * @package ITH\plugins\WP_Ops_Portal
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
     * @return mixed
     */
    public function process_shortcode()
    {
        ob_start();// ob_start is here for a reason
        $db = get_option(WPOP_OPTION_NAME);

        if (!empty($db['baseURL'])) {
            ?> <!-- ==== Ops Portal Start ==== -->
            <div id="portal" class="wp-ops-portal"></div>
            <script
                async="async"
                defer="defer"
                type="text/javascript"
                src="<?php echo esc_url($db['baseURL']) ?>/steal/steal.js?OpsPortal&ver=<?php echo urlencode(WPOP_PLUGIN_VER) ?>"
                data-config="<?php echo esc_url($db['baseURL']) ?>/stealconfig.js">
            </script>
            <!-- ==== Ops Portal Ends ==== -->
            <?php
        } else {
            ?>
            <!-- Ops Portal not configured properly -->
            <?php
        }
        return ob_get_clean();
    }
}