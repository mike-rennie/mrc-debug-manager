<?php
/**
 * Plugin Name: MRC Debug Manager
 * Plugin URI:  https://github.com/mike-rennie/mrc-debug-manager
 * Description: Debugging control panel for WordPress logs and troubleshooting.
 * Version:     1.4
 * Author:      Mike Rennie Creative (MRC)
 * Author URI:  https://mikerennie.com.au
 * License:     MIT License
 * License URI: https://choosealicense.com/licenses/mit/
 */


if (!defined('ABSPATH')) exit;

//--------------------------------------------------
// 1) DETECT ALL LOG FILES
//--------------------------------------------------
function mrc_get_debug_logs() {
    $logs = [];
    $log_dirs = [
        WP_CONTENT_DIR,                          // General WP logs
        WP_CONTENT_DIR . '/logs',                // Some plugins store here
        WP_CONTENT_DIR . '/mu-plugins',          // Must-use plugins
        WP_CONTENT_DIR . '/plugins',             // Regular plugins
        WP_CONTENT_DIR . '/themes',              // Theme logs
        WP_CONTENT_DIR . '/cache'                // Cache-related logs
    ];

    foreach ($log_dirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '/*.log');
            foreach ($files as $file) {
                $logs[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => filesize($file) ? round(filesize($file) / 1024, 2) . ' KB' : '0 KB'
                ];
            }
        }
    }
    return $logs;
}

//--------------------------------------------------
// 2) ADD ADMIN MENU UNDER SETTINGS
//--------------------------------------------------
function mrc_debug_manager_menu() {
    add_options_page(
        'MRC Debugging',
        'Debugging',
        'manage_options',
        'mrc-debugging',
        'mrc_debug_manager_page'
    );
}
add_action('admin_menu', 'mrc_debug_manager_menu');

//--------------------------------------------------
// 3) ADMIN PAGE CONTENT
//--------------------------------------------------
function mrc_debug_manager_page() {
    if (isset($_GET['mrc_download_log'])) {
        mrc_force_download_log($_GET['mrc_download_log']);
    }
    if (isset($_POST['mrc_clear_log'])) {
        unlink($_POST['mrc_clear_log']);
        echo '<div class="updated"><p>Log file cleared.</p></div>';
    }

    $debug_logs = mrc_get_debug_logs();

    ?>
    <div class="wrap">
        <h1>MRC Debugging Manager</h1>
        <table class="widefat fixed">
            <thead>
                <tr>
                    <th>Log File</th>
                    <th>Size</th>
                    <th>Download</th>
                    <th>Clear</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($debug_logs)) {
                    echo '<tr><td colspan="4"><em>No log files found.</em></td></tr>';
                } else {
                    foreach ($debug_logs as $log) {
                        $download_url = admin_url('options-general.php?page=mrc-debugging&mrc_download_log=' . urlencode($log['path']));
                        echo '<tr>';
                        echo '<td>' . esc_html($log['name']) . '</td>';
                        echo '<td>' . esc_html($log['size']) . '</td>';
                        echo '<td><a href="' . esc_url($download_url) . '" class="button">Download</a></td>';
                        echo '<td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="mrc_clear_log" value="' . esc_attr($log['path']) . '">
                                <button type="submit" class="button button-secondary">Clear</button>
                            </form>
                        </td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>

        <hr>
        <p style="color: #777;">MRC Debug Manager v1.4 - Developed by <a href="https://mikerennie.com.au" target="_blank">Mike Rennie Creative</a>.</p>
    </div>
    <?php
}

//--------------------------------------------------
// 4) FORCE LOG DOWNLOAD
//--------------------------------------------------
function mrc_force_download_log($log_path) {
    if (!file_exists($log_path)) {
        wp_die('Log file not found.');
    }
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($log_path) . '"');
    header('Content-Length: ' . filesize($log_path));
    readfile($log_path);
    exit;
}

//--------------------------------------------------
// 5) PLUGIN UPDATE CHECK SYSTEM
//--------------------------------------------------
function mrc_debug_update_check() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $plugin_file = plugin_basename(__FILE__);
    $current_version = get_plugin_data(__FILE__)['Version'];
    $repo_url = 'https://api.github.com/repos/MikeRennieCreative/mrc-debug-manager/releases/latest';

    $response = wp_remote_get($repo_url, ['headers' => ['User-Agent' => 'WordPress']]);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        $latest_version = $data['tag_name'];

        if (version_compare($latest_version, $current_version, '>')) {
            echo '<div class="notice notice-warning">
                <p><strong>MRC Debug Manager v' . esc_html($latest_version) . ' available.</strong> 
                <a href="' . esc_url($data['assets'][0]['browser_download_url']) . '" target="_blank">Download Update</a>.</p>
            </div>';
        }
    }
}
add_action('admin_notices', 'mrc_debug_update_check');

