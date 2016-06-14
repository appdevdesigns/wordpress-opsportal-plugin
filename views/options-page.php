<div class="wrap">
    <h2><?php _e('Ops Portal Configs', WPOP_TEXT_DOMAIN) ?></h2>

    <h2 class="nav-tab-wrapper" id="op-tabs">
        <a class="nav-tab" id="op-general-tab" href="#top#op-general"><?php _e('General', WPOP_TEXT_DOMAIN) ?></a>
        <a class="nav-tab" id="op-interface-tab" href="#top#op-interface"><?php _e('Interface', WPOP_TEXT_DOMAIN) ?></a>
        <a class="nav-tab" id="op-troubleshoot-tab"
           href="#top#op-troubleshoot"><?php _e('Troubleshoot', WPOP_TEXT_DOMAIN) ?></a>
    </h2>

    <form action="<?php echo admin_url('options.php') ?>" method="post" id="op-form" novalidate>
        <?php
        settings_fields(self::WPOP_OPTION_GROUP);
        ?>
        <div class="tab-wrapper">
            <section id="op-general" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Base URL', WPOP_TEXT_DOMAIN); ?> :</th>
                        <td>
                            <input type="text" size="25" name="ops_portal_options[baseURL]"
                                   value="<?php echo esc_attr($db['baseURL']); ?>">
                            <p class="description"><?php _e('Example', WPOP_TEXT_DOMAIN) ?>: <code>http://192.168.1.210:1337</code>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Default Role', WPOP_TEXT_DOMAIN); ?> :</th>
                        <td><select name="ops_portal_options[defaultRole]">
                                <option disabled value=""><?php _e('Select a Role', WPOP_TEXT_DOMAIN); ?></option>
                                <?php
                                foreach ($roles as $role) {
                                    echo '<option value="' . $role['id'] . '"' . selected($db['defaultRole'], $role['id'], false) . '>' . $this->get_localized_label($role['translations']) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Default Scopes', WPOP_TEXT_DOMAIN); ?> :</th>
                        <td>
                            <fieldset>
                                <?php
                                foreach ($scopes as $scope) {
                                    echo '<label><input type="checkbox" name="ops_portal_options[defaultScopes][]" ';
                                    echo (in_array($scope['id'], $db['defaultScopes'])) ? ' checked ' : '';
                                    echo ' value="' . $scope['id'] . '">';
                                    echo $scope['label'] . '</label></option><br>';
                                }
                                ?>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </section>
            <section id="op-interface" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Default theme', WPOP_TEXT_DOMAIN) ?> :</th>
                        <td>
                            <fieldset>
                                <?php
                                foreach ($themes as $theme) {
                                    echo '<label>';
                                    echo '<input type="radio" name="ops_portal_options[defaultTheme]" ';
                                    echo 'value="' . $theme['path'] . '" ' . checked($db['defaultTheme'], $theme['path'], false);
                                    echo '>&ensp;' . ucwords($theme['name']);
                                    echo '</label><br>';
                                }
                                ?>
                            </fieldset>
                        </td>
                    </tr>
                </table>
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
                            <pre class="code-dump"><?php echo htmlentities2($curl_response); ?></pre>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Last CURL stderr log', WPOP_TEXT_DOMAIN) ?> :</th>
                        <td>
                            <pre class="code-dump"><?php echo htmlentities2($curl_stderr); ?></pre>
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