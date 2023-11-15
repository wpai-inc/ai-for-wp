<?php
/**
 * Register ajax methods
 *
 * @package CodeWPHelper
 */
cwpai_register_ajax_method(
	'cwpai-settings/api-token-save',
	/**
	 * Save the api token
	 *
	 * @throws ImagickException
	 * @throws Exception
	 */
	function (): array {

		wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ?? '' ), NONCE_ACTION );
		$token = sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );

		$api_key_settings = cwpai_get_api_key_form_data( true );

		if ( empty( $api_key_settings['token'] ) && ( empty( $token ) || 48 !== strlen( $token ) ) ) {
			throw new \Exception( esc_html( __( 'Please enter a valid token', 'wp-cwpai-settings-page' ) ) );
		}

		$response = cwpai_send_data_to_codewp( 'POST', $token );

		if ( is_a( $response, 'WP_Error' ) ) {
			throw new \Exception( esc_html( $response->get_error_message() ) );
		} elseif ( 401 === $response['response']['code'] ) {
			throw new \Exception( esc_html( __( 'Your token is invalid. Please add a new one!', 'wp-cwpai-settings-page' ) ) );
		} elseif ( 200 === $response['response']['code'] || 201 === $response['response']['code'] ) {
			$body = json_decode( $response['body'], true );

			cwpai_save_settings(
				array(
					'token'           => $token,
					'project_id'      => $body['id'],
					'project_name'    => $body['name'],
					'auto_syncronize' => true,
					'synchronized_at' => gmdate( 'Y-m-d H:i:s' ),
				)
			);
			cwpai_add_cron_job();

			return cwpai_get_api_key_form_data();
		} else {
			$response_message = json_decode( $response['body'], true );
			if ( ! empty( $response_message['errors'] ) ) {
				$messages = array();
				array_walk_recursive(
					$response_message['errors'],
					function ( $a ) use ( &$messages ) {
						$messages[] = $a;
					}
				);
				throw new \Exception( esc_html( implode( ', ', $messages ) ) );
			} else {
				throw new \Exception( esc_html( $response['response']['message'] ?? 'Error' ) );
			}
		}
	}
);

cwpai_register_ajax_method(
	'cwpai-settings/api-auto-synchronize-save',
	/**
	 * Save the auto synchronize option
	 *
	 * @throws ImagickException
	 * @throws Exception
	 */
	function () {

		wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ?? '' ), NONCE_ACTION );

		$auto_syncronize  = 'true' === ( sanitize_text_field( wp_unslash( $_POST['autoSynchronize'] ?? '' ) ) );
		$api_key_settings = cwpai_get_api_key_form_data( true );

		if ( true === $auto_syncronize ) {

			$response = cwpai_send_data_to_codewp( 'PATCH' );

			if ( 200 === $response['response']['code'] || 201 === $response['response']['code'] ) {
				$body = json_decode( $response['body'], true );
				cwpai_save_settings(
					array(
						'project_id'      => $body['id'],
						'project_name'    => $body['name'],
						'auto_syncronize' => true,
						'synchronized_at' => date( 'Y-m-d H:i:s' ),
					)
				);

				cwpai_add_cron_job();

				return cwpai_get_api_key_form_data( false, 'Your project will be synchronized with CodeWP' );
			} elseif ( 401 === $response['response']['code'] ) {
				throw new \Exception( esc_html( __( 'Your saved token is invalid. Please add a new one!', 'wp-cwpai-settings-page' ) ) );
			} else {
				throw new \Exception( esc_html( $response['response']['message'] ?? 'Error' ) );
			}
		} else {
			cwpai_save_settings(
				array(
					'token'           => $api_key_settings['token'],
					'project_id'      => $api_key_settings['project_id'],
					'project_name'    => $api_key_settings['project_name'],
					'auto_syncronize' => false,
					'synchronized_at' => $api_key_settings['synchronized_at'],
				)
			);

			wp_clear_scheduled_hook( 'cwpai_cron_synchronizer' );

			return cwpai_get_api_key_form_data( false, 'Your project will not be synchronized with CodeWP anymore' );
		}
	}
);

cwpai_register_ajax_method(
	'cwpai-settings/notice-hide',
	/**
	 * Save the auto synchronize option
	 *
	 * @throws ImagickException
	 * @throws Exception
	 */
	function () {

		wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ?? '' ), NONCE_ACTION );

		update_option( 'cwpai-settings/notice_visible', 0, false );

		return [
			'notice_visible' => false,
		];
	}
);
