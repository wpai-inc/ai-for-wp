<?php

namespace WpAi\CodeWpHelper\Utils;

use WpAi\CodeWpHelper\Main;

class HelperFunctions {
	public static function codewpai_verify_nonce(): void {
		if (
			! isset( $_REQUEST['_wpnonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), Main::nonce() )
		) {
			die( esc_html__( 'Security check', 'ai-for-wp' ) );
		}
	}

	/**
	 * Get a request string.
	 *
	 * @param string $key The key to get from the request.
	 * @param bool   $required Whether the key is required.
	 * @param string $default_value The default value.
	 *
	 * @return string
	 * @throws \Exception If the key is empty.
	 */
	public static function codewpai_get_request_string( string $key, bool $required = false, string $default_value = '' ): string {
		self::codewpai_verify_nonce();
		if ( $required && empty( $key ) ) {
			throw new \Exception( esc_html( $key . ' is required' ) );
		}

		return isset( $_REQUEST[ $key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ) : $default_value;
	}
}
