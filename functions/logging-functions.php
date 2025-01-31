<?php
if (!defined('ABSPATH')) exit;

function mrc_get_debug_logs() {
    $logs = [];
    $log_dirs = [
        WP_CONTENT_DIR . '/logs',
        WP_CONTENT_DIR . '/plugins',
        WP_CONTENT_DIR . '/themes',
        WP_CONTENT_DIR . '/cache'
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
