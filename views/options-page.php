<div class="wrap">
    <h2>Ops Portal Configs</h2>

    <form action="<?php echo admin_url('options.php') ?>" method="post" id="wpop_form" novalidate>
        <?php
        settings_fields(self::WPOP_OPTION_GROUP);
        $db = $this->get_safe_options();
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">Base URL</th>
                <td>
                    <input type="text" size="25" name="ops_portal_options[baseURL]"
                           value="<?php echo esc_attr($db['baseURL']); ?>">
                    <p class="description">Example: <code>http://192.168.1.210:1337</code></p>
                </td>
            </tr>
        </table>
        <?php submit_button() ?>
    </form>
</div>