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
