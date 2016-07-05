<?php
//only visible to logged in users
//todo also check if ticket cookie exists ?
if (is_user_logged_in()) {
    if (!empty($base_url)) {
        ?>
        <!--  Ops Portal Start  -->
        <div appdev-opsportal="default" portal-theme="<?php echo $theme ?>"></div>
        <script type="text/javascript" src="<?php echo esc_url($base_url) ?>begin"></script>
        <script
            type="text/javascript"
            src="<?php echo esc_url($base_url) ?>steal/steal.js?OpsPortal&ver=<?php echo urlencode(WPOP_PLUGIN_VER) ?>"
            config="<?php echo esc_url($base_url) ?>stealconfig.js">
        </script>
        <!-- Ops Portal Ends -->
        <?php
    } else {
        ?>
        <div class="ops-portal-error">
            <p><?php _e('Ops Portal plugin not configured properly', 'ops-portal'); ?></p>
        </div>
        <?php
    }
} else {
    ?>
    <div class="ops-portal-error ops-portal-not-logged-in">
        <p><?php _e('You must be logged-in to WordPress in order to see Ops Portal interface', 'ops-portal'); ?></p>
    </div>
    <?php
}