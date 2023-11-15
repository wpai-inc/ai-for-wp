<?php
/**
 * Returns the debug data from the current WordPress installation
 *
 * @return array
 * @throws ImagickException If the Imagick extension is not loaded.
 */
function cwpai_get_debug_data(): array {
	if ( ! class_exists( '\WP_Debug_Data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-debug-data.php';
	}
	if ( ! class_exists( '\WP_Site_Health' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-site-health.php';
	}
	if ( ! function_exists( '\get_core_updates' ) ) {
		require_once ABSPATH . 'wp-admin/includes/update.php';
	}
	if ( ! function_exists( '\get_dropins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( ! function_exists( '\got_url_rewrite' ) ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
	}

	return \WP_Debug_Data::debug_data();
}

/**
 * Returns the data for the api key form
 *
 * @param bool   $show_token If true, the token will be shown.
 * @param string $message If not empty, the message will be shown.
 *
 * @return array
 */
function cwpai_get_api_key_form_data( bool $show_token = false, string $message = '' ): array {
	$api_token_settings = get_option(
		'cwpai-settings/api-token',
		array(
			'token'             => '',
			'token_placeholder' => '',
			'project_id'        => '',
			'project_name'      => '',
			'auto_syncronize'   => '',
			'synchronized_at'   => '',
		)
	);

	if ( ! empty( $api_token_settings['token'] ) ) {
		$api_token_settings['token_placeholder'] = '************************************************';
	}
	if ( ! empty( $api_token_settings['token'] ) && ! $show_token ) {
		$api_token_settings['token'] = '';
	}


	if ( ! empty( $api_token_settings['synchronized_at'] ) ) {
		$api_token_settings['synchronized_at'] = gmdate( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $api_token_settings['synchronized_at'] ) );
	}

	if ( ! empty( $message ) ) {
		$api_token_settings['message'] = $message;
	}

	return $api_token_settings;
}


/**
 * Send data to CodeWP
 *
 * @throws ImagickException If the Imagick extension is not loaded.
 */
function cwpai_send_data_to_codewp( $method = 'POST', $token = null ) {
	$api_key_settings = cwpai_get_api_key_form_data( true );

	$debug_data = cwpai_get_debug_data();
	$body       = array(
		'name'        => get_bloginfo( 'name' ),
		'description' => get_bloginfo( 'description' ),
		'debug_data'  => $debug_data,
		'url'         => home_url(),
	);

	if ( isset( $api_key_settings['project_id'] ) && $api_key_settings['project_id'] ) {
		$body['project'] = $api_key_settings['project_id'];
	}

	$request = array(
		'method'  => $method,
		'headers' => array(
			'Authorization' => 'Bearer ' . ( $token ?: $api_key_settings['token'] ),
			'Accept'        => 'application/json',
		),
		'body'    => $body,
	);

	$response = wp_remote_request(
		CWPAI_API_SERVER . '/api/' . ( 'POST' === $method ? 'wp-site-projects' : 'wp-site-project-synchronize' ),
		$request
	);

	@ray('cwpai_helper_cron_synchronizer_job', $response);


	return $response;
}


/**
 * Save settings
 *
 * @param array $data The data.
 *
 * @return void
 */
function cwpai_save_settings( array $data ) {
	$options = get_option( 'cwpai-settings/api-token' );
	$data    = array_merge( $options, $data );

	update_option(
		'cwpai-settings/api-token',
		$data,
		false
	);
}
