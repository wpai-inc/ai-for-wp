<?php

namespace WpAi\CodeWpHelper;

use WpAi\CodeWpHelper\Main;
use WpAi\CodeWpHelper\Utils\RegisterAjaxMethod;

/**
 * Class Ajax
 */
class Ajax {

	/**
	 * Ajax constructor.
	 */
	public function __construct() {
		new RegisterAjaxMethod(
			'codewpai_save_api_token',
			array( $this, 'saveApiToken' )
		);

		new RegisterAjaxMethod(
			'codewpai_api_auto_synchronize_save',
			array( $this, 'saveAutoSynchronize' )
		);

		new RegisterAjaxMethod(
			'codewpai_notice_hide',
			/**
			 * Save the auto synchronize option
			 */
			array( $this, 'hideNotice' )
		);
	}

	/**
	 * Save the API token
	 *
	 * @return array
	 * @throws \Exception If the token is not valid.
	 */
	public function saveApiToken(): array {

		if ( ! isset( $_REQUEST['_wpnonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), Main::nonce() )
		) {
			die( esc_html__( 'Security check', 'ai-for-wp' ) );
		}

		$token = sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );

		$api_key_settings = Settings::getSettingsFormData( true );

		if ( empty( $api_key_settings['token'] ) && ( empty( $token ) || 48 !== strlen( $token ) ) ) {
			throw new \Exception( esc_html( __( 'Please enter a valid token', 'ai-for-wp' ) ) );
		}

		$response = Settings::sendDataToCodewp( 'POST', $token );

		Settings::save(
			array(
				'token'            => $token,
				'project_id'       => $response['id'],
				'project_name'     => $response['name'],
				'auto_synchronize' => true,
				'synchronized_at'  => gmdate( 'Y-m-d H:i:s' ),
			)
		);
		Cron::addCronJob();

		return Settings::getSettingsFormData();
	}

	/**
	 * Save the auto synchronize option
	 *
	 * @throws \Exception If the token is not valid.
	 */
	public function saveAutoSynchronize(): array {

		if ( ! isset( $_REQUEST['_wpnonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), Main::nonce() )
		) {
			die( esc_html__( 'Security check', 'ai-for-wp' ) );
		}

		$auto_synchronize = 'true' === ( sanitize_text_field( wp_unslash( $_POST['autoSynchronize'] ?? '' ) ) );

		if ( true === $auto_synchronize ) {
			$response = Settings::sendDataToCodewp( 'PATCH' );

			Settings::save(
				array(
					'project_id'       => $response['id'],
					'project_name'     => $response['name'],
					'auto_synchronize' => true,
					'synchronized_at'  => gmdate( 'Y-m-d H:i:s' ),
				)
			);

			Cron::addCronJob();

			return Settings::getSettingsFormData( false, 'Your project will be synchronized with CodeWP' );
		}
		Settings::save(
			array(
				'auto_synchronize' => false,
			)
		);

		Cron::removeCronJob();

		return Settings::getSettingsFormData( false, 'Your project will not be synchronized with CodeWP anymore' );
	}


	/**
	 * Hide the notice
	 *
	 * @return false[]
	 */
	public function hideNotice(): array {

		if ( ! isset( $_REQUEST['_wpnonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), Main::nonce() )
		) {
			die( esc_html__( 'Security check', 'ai-for-wp' ) );
		}

		update_option( 'codewpai_notice_visible', 0, false );

		return array(
			'notice_visible' => false,
		);
	}
}
