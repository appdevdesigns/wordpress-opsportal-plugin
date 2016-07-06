<div class="wrap">
    <h2><?php _e('Ops Portal Configs', 'ops-portal') ?></h2>

    <h2 class="nav-tab-wrapper" id="op-tabs">
        <a class="nav-tab" id="op-general-tab" href="#top#op-general"><?php _e('General', 'ops-portal') ?></a>
        <a class="nav-tab" id="op-troubleshoot-tab"
           href="#top#op-troubleshoot"><?php _e('Troubleshoot', 'ops-portal') ?></a>
    </h2>

    <form action="<?php echo admin_url('options.php') ?>" method="post" id="op-form" novalidate>
        <?php
        settings_fields($option_group);
        ?>
        <div class="tab-wrapper">
            <section id="op-general" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Base URL', 'ops-portal'); ?> :</th>
                        <td>
                            <input type="text" size="25" name="ops_portal_options[baseURL]"
                                   placeholder="<?php echo esc_attr(home_url()) ?>"
                                   value="<?php echo esc_attr($db['baseURL']); ?>">
                            <p class="description"><?php _e('Should be a valid URL', 'ops-portal') ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Auth Key', 'ops-portal'); ?> :</th>
                        <td>
                            <input id="op-input-key" type="password" size="25" name="ops_portal_options[authKey]"
                                   value="<?php echo esc_attr($db['authKey']); ?>">
                            <button id="op-btn-show-key" type="button" class="button button-secondary wp-hide-pw"
                                    title="Show / Hide"><i class="dashicons dashicons-visibility"></i></button>
                            <button id="op-btn-gen-key" type="button" class="button button-secondary wp-hide-pw"
                                    title="Generate"><i class="dashicons dashicons-admin-network"></i></button>
                            <p class="description"><?php _e('Paste your secret key here', 'ops-portal') ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Default Role', 'ops-portal'); ?> :</th>
                        <td><select name="ops_portal_options[defaultRole]">
                                <option disabled value=""><?php _e('Select a Role', 'ops-portal'); ?></option>
                                <?php
                                foreach ($roles as $role) {
                                    echo '<option value="' . $role['id'] . '"' . selected($db['defaultRole'], $role['id'], false) . '>' . self::get_localized_label($role['translations']) . '</option>';
                                }
                                ?>
                            </select>
                            <?php if (empty($roles)): ?>
                                <p class="description ops-error"><?php _e('Unable to fetch roles', 'ops-portal') ?></p>
                            <?php endif ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Default Scopes', 'ops-portal'); ?> :</th>
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
                            <?php if (empty($scopes)): ?>
                                <p class="description ops-error"><?php _e('Unable to fetch scopes', 'ops-portal') ?></p>
                            <?php endif ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Default Theme', 'ops-portal') ?> :</th>
                        <td><select name="ops_portal_options[defaultTheme]">
                                <option disabled value=""><?php _e('Select a Theme', 'ops-portal'); ?></option>
                                <option value=""><?php _e('Default Theme', 'ops-portal'); ?></option>
                                <?php
                                foreach ($themes as $theme) {
                                    echo '<option value="' . $theme['path'] . '"' . selected($db['defaultTheme'], $theme['path'], false) . '>' . ucwords($theme['name']) . '</option>';
                                }
                                ?>
                            </select>
                            <?php if (empty($themes)): ?>
                                <p class="description ops-error"><?php _e('Unable to fetch themes', 'ops-portal') ?></p>
                            <?php endif ?>
                        </td>
                    </tr>
                </table>
            </section>
            <section id="op-troubleshoot" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Debug CURL', 'ops-portal') ?> :</th>
                        <td><label><input type="checkbox" name="ops_portal_options[debugCURL]"
                                          value="1" <?php checked($db['debugCURL'], 1) ?>><?php _e('Log CURL calls', 'ops-portal') ?>
                            </label>
                            <p class="description"><?php _e("This should only be used temporarily or during development", 'ops-portal') ?> </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Last CURL response', 'ops-portal') ?> :</th>
                        <td>
                            <pre class="code-dump"><?php echo htmlentities2($curl_response); ?></pre>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Last CURL stderr log', 'ops-portal') ?> :</th>
                        <td>
                            <pre class="code-dump"><?php echo htmlentities2($curl_stderr); ?></pre>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Database options dump', 'ops-portal') ?> :</th>
                        <td>
                            <pre class="code-dump"><?php print_r($db); ?></pre>
                        </td>
                    </tr>
                </table>
            </section>
        </div>
        <p class="submit"><?php submit_button(__('Save Changes'), 'primary', 'submit', false); ?>
            &emsp; <?php submit_button('Flush Cache', 'large', 'ops_portal_options[submit-flush]', false) ?></p>
    </form>
    <hr>
    <p>Use <code>[ops_portal]</code> short-code to see Ops Portal interface</p>
</div>