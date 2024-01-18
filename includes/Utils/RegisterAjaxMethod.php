<?php

namespace WpAi\CodeWpHelper\Utils;

use WpAi\CodeWpHelper\Main;

/**
 * Class RegisterAjaxMethod
 *
 * This class is used to register a new AJAX method in WordPress.
 */
class RegisterAjaxMethod
{
    /**
     * RegisterAjaxMethod constructor.
     *
     * @param  string  $action  The action to register.
     * @param  callable  $method  The method to call when the action is triggered.
     */
    public function __construct(string $action, callable $method)
    {
        $this->registerAction($action, $method);
    }

    /**
     * Register a new action.
     *
     * @param  string  $action  The action to register.
     * @param  callable  $method  The method to call when the action is triggered.
     */
    private function registerAction(string $action, callable $method): void
    {
        add_action(
            'wp_ajax_'.$action,
            function () use ($method) {
                $this->registerAjaxMethod($method);
            }
        );
    }

    /**
     * Register a new AJAX method.
     *
     * @param  callable  $method  The method to call when the action is triggered.
     */
    private function registerAjaxMethod(callable $method): void
    {
        if (! isset($_REQUEST['_wpnonce'])
            || ! wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), Main::nonce())
        ) {
            die(esc_html__('Security check', Main::TEXT_DOMAIN));
        }

        try {
            $result = call_user_func_array($method, []);
            wp_send_json_success($result);
        } catch (\Exception $e) {
            wp_send_json_error(
                [
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
