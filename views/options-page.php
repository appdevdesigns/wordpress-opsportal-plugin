<div class="wrap">
    <h2>Ops Portal Configs</h2>

    <h2 class="nav-tab-wrapper" id="op-tabs">
        <a class="nav-tab" id="op-general-tab" href="#top#op-general"><?php _e('General', WPOP_TEXT_DOMAIN) ?></a>
        <a class="nav-tab" id="op-interface-tab" href="#top#op-interface"><?php _e('Interface', WPOP_TEXT_DOMAIN) ?></a>
        <a class="nav-tab" id="op-troubleshoot-tab"
           href="#top#op-troubleshoot"><?php _e('Troubleshoot', WPOP_TEXT_DOMAIN) ?></a>
    </h2>

    <form action="<?php echo admin_url('options.php') ?>" method="post" id="wpop_form" novalidate>
        <?php
        settings_fields(self::WPOP_OPTION_GROUP);
        $db = $this->get_safe_options();
        ?>
        <div class="tab-wrapper">
            <section id="op-general" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Base URL', WPOP_TEXT_DOMAIN); ?></th>
                        <td>
                            <input type="text" size="25" name="ops_portal_options[baseURL]"
                                   value="<?php echo esc_attr($db['baseURL']); ?>">
                            <p class="description">Example: <code>http://192.168.1.210:1337</code></p>
                        </td>
                    </tr>
                </table>
            </section>
            <section id="op-interface" class="tab-content">
                <p>Interface options goes here</p>
            </section>
            <section id="op-troubleshoot" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Debug CURL', WPOP_TEXT_DOMAIN) ?> :</th>
                        <td><label><input type="checkbox" name="ops_portal_options[debugCURL]"
                                          value="1" <?php checked($db['debugCURL'], 1) ?>><?php _e('Log CURL calls', WPOP_TEXT_DOMAIN) ?>
                            </label>
                            <p class="description"><?php _e("This should only be used temporarily or during development", WPOP_TEXT_DOMAIN) ?> </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Last CURL response', WPOP_TEXT_DOMAIN) ?> :</th>
                        <td>
                            <pre class="code-dump"><?php echo $this->read_log_file('curl_response.log') ?></pre>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Last CURL stderr log', WPOP_TEXT_DOMAIN) ?> :</th>
                        <td>
                            <pre class="code-dump"><?php echo $this->read_log_file('curl_stderr.log') ?></pre>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Database options dump', WPOP_TEXT_DOMAIN) ?> :</th>
                        <td>
                            <pre class="code-dump"><?php print_r($db); ?></pre>
                        </td>
                    </tr>
                </table>
            </section>
        </div>
        <?php submit_button() ?>
    </form>
</div>