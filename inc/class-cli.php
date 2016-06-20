<?php
namespace ITH\Plugins\WP_Ops_Portal;

use WP_CLI;

/**
 * Class CLI
 *
 * @require WP CLI Version 0.24
 * @link https://wp-cli.org/
 * @package ITH\Plugins\WP_Ops_Portal
 */
class CLI
{
    function __construct()
    {
        //
    }

    /**
     * Show current Ops Portal sync status.
     *
     * ## OPTIONS
     *
     * [--all]
     * : Show a tabular formatted list of not synced users.
     *
     * [--format=<format>]
     * : Accepted values: table, json, csv. Default: table
     *
     * ---
     * ## EXAMPLES
     *
     *     wp ops-portal status --all --format=table
     * ---
     * @param $args
     * @param $assoc_args array
     */
    public function status($args, $assoc_args = array())
    {
        $users = Util::get_not_synced_users(array(), false);

        if (empty($users)) {
            WP_CLI::success('All users synced.');
            return;
        } else {
            WP_CLI::log('Status: ' . WP_CLI::colorize("%R" . count($users) . "%n") . ' users not synced !' . "\n");
        }

        if (isset($assoc_args['all'])) {
            $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');
            //@link https://wp-cli.org/docs/internal-api/wp-cli-utils-format-items/
            WP_CLI\Utils\format_items($format, $users, array('ID', 'user_login', 'user_email'));
        }
        exit;
    }

    /**
     * Sync WP Users to Ops Portal.
     *
     * ## OPTIONS
     *
     * ## EXAMPLES
     *
     *     wp ops-portal sync --debug
     * ---
     * @link http://wp-cli.org/docs/commands-cookbook/
     * @param $args
     * @param $assoc_args array
     */
    public function sync($args, $assoc_args = array())
    {
        //check if plugin has been configured or not
        $db = get_option(WPOP_OPTION_NAME);
        if (empty($db['baseURL'])) {
            WP_CLI::error('Base URL not set. Please login to admin dashboard and configure plugin options.');
        }

        //ask for confirmation before proceed
        WP_CLI::confirm(WP_CLI::colorize('%_Are you sure ?%n'), $assoc_args = array());

        $sync = new User_Sync();
        $users = Util::get_not_synced_users(array(), false);
        $count = count($users);

        if (empty($count)) {
            WP_CLI::success('There are no users to be synced.');
            return;
        }

        WP_CLI::log('Status: ' . WP_CLI::colorize('%R' . $count . '%n') . ' users not synced !' . "\n");
        //@link https://wp-cli.org/docs/internal-api/wp-cli-utils-make-progress-bar/
        $progress = WP_CLI\Utils\make_progress_bar('Syncing users...', count($users));
        $succeed = 0;

        foreach ($users as $user) {
            $response = $sync->send_create_user_call($user, $user->ID);
            if (isset($response['http_code']) && $response['http_code'] == 201) {
                $succeed = $succeed + 1;
            }
            //debug runs only when pass --debug parameter
            WP_CLI::debug('ID: ' . $user->ID . ', Name: ' . $user->user_login . ', Email: ' . $user->user_email);
            //increase progress bar
            $progress->tick();

        }
        $progress->finish();
        WP_CLI::success(WP_CLI::colorize("%B" . $succeed . "%n") . ' user(s) out of ' . WP_CLI::colorize("%C" . $count . "%n") . ' synced in last request');
        exit;
    }

}