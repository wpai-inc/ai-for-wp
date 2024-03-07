<?php

namespace WpAi\CodeWpHelper\Utils;

/**
 * Class RegisterAjaxMethod
 *
 * This class is used to register a new AJAX method in WordPress.
 */
class RegisterAjaxMethod {

	/**
	 * RegisterAjaxMethod constructor.
	 *
	 * @param  string   $action  The action to register.
	 * @param  callable $method  The method to call when the action is triggered.
	 */
	public function __construct( string $action, callable $method ) {
		$this->register_action( $action, $method );
	}

	/**
	 * Register a new action.
	 *
	 * @param  string   $action  The action to register.
	 * @param  callable $method  The method to call when the action is triggered.
	 */
	private function register_action( string $action, callable $method ): void {
		add_action(
			'wp_ajax_' . $action,
			function () use ( $method ) {
				$this->registerAjaxMethod( $method );
			}
		);
	}

	/**
	 * Register a new AJAX method.
	 *
	 * @param  callable $method  The method to call when the action is triggered.
	 */
	private function registerAjaxMethod( callable $method ): void {

		try {
			$result = call_user_func_array( $method, array() );
			wp_send_json_success( $result );
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'error' => $e->getMessage(),
				)
			);
		}
	}
}
