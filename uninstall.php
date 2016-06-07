<?php

/**
 * Uninstall file for this plugin
 * This file will be called to remove all traces of this plugin when uninstalled
 */


// If uninstall not called from WordPress do exit
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit;

//Delete database entry created by this plugin
delete_option('ops_portal_options');

/**
 * Purge all the transients associated with our plugin.
 * @source https://css-tricks.com/the-deal-with-wordpress-transients/
 */
function purge_transients()
{
    //https://codex.wordpress.org/Class_Reference/wpdb
    global $wpdb;

    $prefix = 'ops_portal_';
    $transient_name = esc_sql("_transient_timeout_$prefix%");

    $sql = $wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%s' ", $transient_name);
    $transients = $wpdb->get_col($sql);

    // For each transient...
    foreach ($transients as $transient) {

        // Strip away the WordPress prefix in order to arrive at the transient key.
        $key = str_replace('_transient_timeout_', '', $transient);

        // Now that we have the key, use WordPress core to the delete the transient.
        delete_transient($key);

    }

    // But guess what?  Sometimes transients are not in the DB, so we have to do this too:
    wp_cache_flush();

}

//Don't forget to call the function
purge_transients();
