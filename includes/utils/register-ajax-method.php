<?php
/**
 * Register ajax method.
 *
 * @param string   $action The action.
 * @param callable $method The method.
 *
 * @return void
 */
function cwpai_register_ajax_method( string $action, callable $method ) {
	add_action(
		'wp_ajax_' . $action,
		function () use ( $method ) {
			if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), NONCE_ACTION ) ) {
				die( esc_html__( 'Security check', 'wp-cwpai-settings-page' ) );
			}

			try {
				$result = call_user_func_array( $method, [] );
				wp_send_json_success( $result );
			} catch ( \Exception $e ) {
				wp_send_json_error(
					[
						'error' => $e->getMessage(),
					]
				);
			}
		}
	);
}
