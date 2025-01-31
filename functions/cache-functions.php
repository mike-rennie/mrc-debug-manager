<?php
if (!defined('ABSPATH')) exit;

function mrc_clear_litespeed_cache() {
    if (function_exists('do_action')) {
        do_action('litespeed_purge_all');
    }
}

function mrc_clear_transients() {
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    wp_cache_flush();
}

function mrc_clear_all_caches() {
    mrc_clear_litespeed_cache();
    mrc_clear_transients();
}
