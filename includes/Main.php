<?php

namespace WpAi\CodeWpHelper;

class Main
{
    public const VERSION = '0.2.0';

    public const TEXT_DOMAIN = 'codewpai';

    public const API_HOST = 'https://app.codewp.ai';

    private $plugin_file;

    private $plugin_dir;

    public $snippets;

    public function __construct($plugin_file)
    {
        $this->plugin_file = $plugin_file;
        $this->plugin_dir = plugin_dir_path($this->plugin_file);
        register_activation_hook($this->plugin_file, [$this, 'activate']);
        register_deactivation_hook($this->plugin_file, [$this, 'deactivate']);
        $this->bootstrap();
    }

    public static function nonce()
    {
        return self::TEXT_DOMAIN;
    }

    public function activate()
    {
        // do something on plugin activation
    }

    public function deactivate()
    {
        // do something on plugin deactivation
        delete_option('codewpai/api-token');
        delete_option('codewpai/notice_visible');
    }

    public function errorHandler()
    {
        register_shutdown_function(function () {
            $error = error_get_last();

            if (! defined('WP_CONTENT_DIR')) {
                define('WP_CONTENT_DIR', '/wordpress/wp-content');
                error_log(WP_CONTENT_DIR);
            }

            $errors = file_exists(WP_CONTENT_DIR . '/debug.json')
                ? json_decode(file_get_contents(WP_CONTENT_DIR . '/debug.json'), true)
                : [];
            if ($error) {
                // Get the existing errors
                $errors = file_exists(WP_CONTENT_DIR . '/debug.json') ? json_decode(file_get_contents(WP_CONTENT_DIR . '/debug.json'), true) : [];

                // Add the new error
                $errors[] = [
                    'type' => $error['type'],
                    'message' => $error['message'],
                    'file_name' => $error['file'],
                    'line' => $error['line'],
                ];

                // Save the errors
                file_put_contents(WP_CONTENT_DIR . '/debug.json', json_encode($errors));
            }
        });
    }

    public function bootstrap()
    {
        $this->errorHandler();

        // do something on plugin bootstrap
        new Filters($this->plugin_file);
        new Ajax();
        new AdminPage($this->plugin_dir, $this->plugin_file);
        new Cron();

        // This object can be used to manipulate snippets (playground functionality)
        $this->snippets = new Snippets($this->plugin_dir);

        new Logs();
    }
}
