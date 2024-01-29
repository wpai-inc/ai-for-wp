<?php


namespace WpAi\CodeWpHelper;

class Logs
{
    public function __construct()
    {
        add_action('wp_ajax_codewpai_logs', [ $this, 'getLogs' ]);
    }

    public function getLogs()
    {
        $logs = file_exists(WP_CONTENT_DIR . '/debug.json')
            ? json_decode(file_get_contents(WP_CONTENT_DIR . '/debug.json'), true)
            : [];

        wp_send_json($logs);
    }
}
