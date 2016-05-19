<?php
namespace ITH\plugins\WP_Ops_Portal;

/**
 * Class Shortcode
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
     * Process shortcode and generate html
     * @return mixed
     */
    public function process_shortcode()
    {
        ob_start();// ob_start is here for a reason
        $db = get_option(WPOP_OPTION_NAME);

        if (!empty($db['baseURL'])) {
            ?>
            <div id="portal" class="wp-ops-portal-"><!--dynamic content--></div>
            <script
                type="text/javascript"
                src="<?php echo esc_url($db['baseURL']) ?>/steal/steal.js?OpsPortal"
                config="<?php echo esc_url($db['baseURL']) ?>/stealconfig.js">
            </script>
            <?php
        } else {
            ?>
            <!-- Ops Portal not configured properly -->
            <?php
        }
        return ob_get_clean();
    }
}