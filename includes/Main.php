<?php

namespace WpAi\CodeWpHelper;

class Main
{
    public const VERSION = '0.2.2';

    public const TEXT_DOMAIN = 'ai-for-wp';

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
        delete_option('codewpai_api_token');
        delete_option('codewpai_notice_visible');
    }

    public function errorHandler()
    {
        register_shutdown_function(function () {
            $error = error_get_last();

            if (! defined('WP_CONTENT_DIR') && defined('CWP_PLAGROUND') && CWP_PLAGROUND === true) {
                define('WP_CONTENT_DIR', '/wordpress/wp-content');
            }

            if ($error) {
                $errors = file_exists(WP_CONTENT_DIR . '/debug.json')
                ? json_decode(file_get_contents(WP_CONTENT_DIR . '/debug.json'), true)
                : [];

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

                // If the error is from a snippet, disable it
                // TODO: only disable the snippet if it's a fatal error
                // TODO: display a message to the user that the snippet has been disabled
                if (strpos($error['file'], 'snippets') !== false) {
                    // get file name
                    $snippet_file = pathinfo($error['file'], PATHINFO_BASENAME);
                    $snippets = get_option('codewpai_enabled_snippets', []);
                    if (!empty($snippets[$snippet_file])) {
                        $snippets[$snippet_file] = false;
                        update_option('codewpai_enabled_snippets', $snippets);
                        // redirect to snippets page using JS
                        echo '<script>window.location.href = "' . admin_url('options-general.php?page=ai-for-wp&tab=snippets') . '";</script>';
                    }
                }
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
