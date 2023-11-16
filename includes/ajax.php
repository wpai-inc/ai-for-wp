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

		cwpai_save_settings(
			array(
				'token'           => $token,
				'project_id'      => $response['id'],
				'project_name'    => $response['name'],
				'auto_syncronize' => true,
				'synchronized_at' => gmdate( 'Y-m-d H:i:s' ),
			)
		);
		cwpai_add_cron_job();

		return cwpai_get_api_key_form_data();
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

		$auto_syncronize = 'true' === ( sanitize_text_field( wp_unslash( $_POST['autoSynchronize'] ?? '' ) ) );

		if ( true === $auto_syncronize ) {

			$response = cwpai_send_data_to_codewp( 'PATCH' );

			cwpai_save_settings(
				array(
					'project_id'      => $response['id'],
					'project_name'    => $response['name'],
					'auto_syncronize' => true,
					'synchronized_at' => gmdate( 'Y-m-d H:i:s' ),
				)
			);

			cwpai_add_cron_job();

			return cwpai_get_api_key_form_data( false, 'Your project will be synchronized with CodeWP' );
		} else {
			cwpai_save_settings(
				array(
					'auto_syncronize' => false,
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
	 */
	function () {

		wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ?? '' ), NONCE_ACTION );

		update_option( 'cwpai-settings/notice_visible', 0, false );

		return [
			'notice_visible' => false,
		];
	}
);
