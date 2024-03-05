<?php

namespace WpAi\CodeWpHelper;

class ErrorHandler
{
    public function __construct()
    {
        set_error_handler([$this, 'errorHandler']);
        register_shutdown_function([$this, 'fatalErrorHandler']);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $this->errorLogger([
            'type' => $errno,
            'message' => $errstr,
            'file_name' => $errfile,
            'line' => $errline,
        ]);
    }

    public function fatalErrorHandler()
    {
        $error = error_get_last();

        if (!defined('WP_CONTENT_DIR') && defined('CWP_PLAGROUND') && CWP_PLAGROUND === true) {
            define('WP_CONTENT_DIR', '/wordpress/wp-content');
        }

        if ($error) {
            $error['file_name'] = $error['file'];
            unset($error['file']);
            $this->errorLogger($error);
        }
    }

    public function errorLogger($error)
    {

        if ($error && $error['message']) {
            // Get the existing errors
            $errors = file_exists(WP_CONTENT_DIR . '/debug.json') ? json_decode(file_get_contents(WP_CONTENT_DIR . '/debug.json'), true) : [];

            // limit the number of errors saved to 100. If there are more than 100 errors, remove the oldest ones
            if ($errors && count($errors) > 100) {
                $errors = array_slice($errors, count($errors) - 100);
            }

            // Add the new error
            $errors[] = $error;

            // Save the errors
            file_put_contents(WP_CONTENT_DIR . '/debug.json', json_encode($errors));

            // If the error is from a snippet, disable it
            // TODO: only disable the snippet if it's a fatal error
            if (!empty($error['file_name']) && strpos($error['file_name'], 'snippets') !== false) {
                // get file name
                $snippet_file = pathinfo($error['file_name'], PATHINFO_BASENAME);
                $snippets     = get_option('codewpai_enabled_snippets', []);
                if (!empty($snippets[$snippet_file])) {
                    $snippets[$snippet_file] = [
                        'enabled' => false,
                        'error' => $error,
                    ];
                    update_option('codewpai_enabled_snippets', $snippets);
                    // redirect to snippets page using JS
                    $admin_url = admin_url('options-general.php?page=ai-for-wp&tab=snippets&snippet_error=' . $snippet_file);
                    echo '<script>console.log("Snippet has been disabled. Redirecting to ' . $admin_url . '"); window.location.href = "' . $admin_url . '";</script>';
                }
            }
        }
    }
}
