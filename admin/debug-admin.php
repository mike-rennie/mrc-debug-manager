<?php
if (!defined('ABSPATH')) exit;

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
    </div>
    <?php
}
