<?php
/**
 * Register ajax methods
 *
 * @package CodeWPHelper
 */

namespace CodeWPHelper;

register_ajax_method(
	'cwpai-settings/hide-notice',
	function () {
		update_option( 'cwpai-settings/notice_hidden', true );

		return true;
	}
);

register_ajax_method(
	'cwpai-settings/simple_form-save',
	function () {
		$name        = sanitize_text_field( $_POST['name'] );
		$description = sanitize_text_field( $_POST['description'] );
		$throwError  = $_POST['throwError'] === 'true';

		if ( true === $throwError ) {
			throw new \Exception( __( 'You checked the checkbox. Server error', 'wp-cwpai-settings-page' ) );
		}

		sleep( 1 );

		$data = array(
			'name'        => $name,
			'description' => $description,
		);

		update_option( 'cwpai-settings/simple_form', $data );

		return $data;
	}
);

register_ajax_method(
	'cwpai-settings/repeated_form-save',
	function () {
		$name  = sanitize_text_field( $_POST['name'] );
		$items = $_POST['items'];

		if ( ! is_array( $items ) ) {
			$items = array();
		}

		foreach ( $items as &$item ) {
			$item['title']       = sanitize_text_field( $item['title'] );
			$item['description'] = sanitize_text_field( $item['description'] );
		}

		$data = array(
			'name'  => $name,
			'items' => $items,
		);

		update_option( 'cwpai-settings/repeated_form', $data );

		return $data;
	}
);

register_ajax_method(
	'cwpai-settings/api-token-save',
	function () {
		$token = sanitize_text_field( $_POST['token'] );

		$api_key_settings = wpai_get_api_key_form_data( true );

		if ( empty( $api_key_settings['token'] ) && ( empty( $token ) || 48 !== strlen( $token ) ) ) {
			wp_send_json_error( array( 'error' => __( 'Please enter a valid token', 'wp-cwpai-settings-page' ) ) );
		}

		$debug_data = wpai_get_debug_data();
		$body       = array(
			'name'        => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
			'url'         => get_site_url(),
			'debug_data'  => $debug_data,
		);

		$response = wp_remote_request(
			\CWPAI_API_SERVER . '/api/wp-site-projects',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Accept'        => 'application/json',
				),
				// TODO: ~probably~ let the user select which data to send to codewp.
				// TODO: determine data structure view app/Http/Controllers/Api/ProjectApiController.php:34.
				'body'    => $body,
			)
		);

		if ( 200 === $response['response']['code'] || 201 === $response['response']['code'] ) {
			$body = json_decode( $response['body'], true );
			update_option(
				'cwpai-settings/api-token',
				array(
					// TODO: should we base64_encode the token?
					'token'            => $token,
					'project_id'       => $body['id'],
					'project_name'     => $body['name'],
					'auto_synchronize' => true,
					'synchronized_at'  => date( 'Y-m-d H:i:s' ),
				)
			);

			// TODO: add a cron job to synchronize the project every 24 hours.

			return wpai_get_api_key_form_data();
		} elseif ( 401 === $response['response']['code'] ) {
			wp_send_json_error( array( 'error' => __( 'Invalid token', 'wp-cwpai-settings-page' ) ) );
		} else {
			$response_message = json_decode( $response['body'], true );
			if ( ! empty( $response_message['errors'] ) ) {
				// TODO: because this is not a 2 way communication, probably we should let the users add invalid url's ? By invalid I mean url's that are not accessible from the internet like http://localhost .

				// Extract all messages from a multilevel array and return them as a single string.
				$messages = array();
				array_walk_recursive(
					$response_message['errors'],
					function ( $a ) use ( &$messages ) {
						$messages[] = $a;
					}
				);

				wp_send_json_error( array( 'error' => $messages ) );
			} else {
				// TODO: improve this general error message.
				throw new \Exception( __( 'Error', 'wp-cwpai-settings-page' ) );
			}
		}

		return array();
	}
);
register_ajax_method(
	'cwpai-settings/api-auto-synchronize-save',
	function () {
		$auto_synchronize = $_POST['autoSynchronize'] === 'true';
		$api_key_settings = wpai_get_api_key_form_data( true );

		if ( true === $auto_synchronize ) {
			$debug_data = wpai_get_debug_data();
			$body       = array(
				'project'    => $api_key_settings['project_id'],
				'debug_data' => $debug_data,
			);

			$response = wp_remote_request(
				\CWPAI_API_SERVER . '/api/wp-site-project-synchronize',
				array(
					'method'  => 'PATCH',
					'headers' => array(
						'Authorization' => 'Bearer ' . $api_key_settings['token'],
						'Accept'        => 'application/json',
					),
					'body'    => $body,
				)
			);

			if ( 200 === $response['response']['code'] || 201 === $response['response']['code'] ) {
				$body = json_decode( $response['body'], true );
				update_option(
					'cwpai-settings/api-token',
					array(
						'token'            => $api_key_settings['token'],
						'project_id'       => $body['id'],
						'project_name'     => $body['name'],
						'auto_synchronize' => true,
						'synchronized_at'  => date( 'Y-m-d H:i:s' ),
					)
				);

				// TODO: add a cron job to synchronize the project every 24 hours.

				return wpai_get_api_key_form_data( false, 'Your project will be synchronized with CodeWP' );
			} elseif ( 401 === $response['response']['code'] ) {
				throw new \Exception( __( 'Your saved token is invalid. Please add a new one!', 'wp-cwpai-settings-page' ) );
			} else {
				throw new \Exception( $response['response']['message'] ?? 'Error' );
			}
		} else {
			update_option(
				'cwpai-settings/api-token',
				array(
					'token'            => $api_key_settings['token'],
					'project_id'       => $api_key_settings['project_id'],
					'project_name'     => $api_key_settings['project_name'],
					'auto_synchronize' => false,
					'synchronized_at'  => $api_key_settings['synchronized_at'],
				)
			);

			// TODO: remove the cron job to synchronize the project every 24 hours.

			return wpai_get_api_key_form_data( false, 'Your project will not be synchronized with CodeWP anymore' );
		}
	}
);
