<?php
if (!empty($base_url)) {
    ?>
    <!--  Ops Portal Start  -->
    <div appdev-opsportal="default" portal-theme="<?php echo $theme ?>"></div>
    <script
        async="async"
        defer="defer"
        type="text/javascript"
        src="<?php echo esc_url($base_url) ?>steal/steal.js?OpsPortal&ver=<?php echo urlencode(WPOP_PLUGIN_VER) ?>"
        config="<?php echo esc_url($base_url) ?>stealconfig.js">
    </script>
    <!-- Ops Portal Ends -->
    <?php
} else {
    ?>
    <!-- Ops Portal not configured properly -->
    <?php
} ?>