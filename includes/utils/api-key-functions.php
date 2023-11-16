<?php
/**
 * Returns the debug data from the current WordPress installation
 *
 * @return array
 */
function cwpai_get_debug_data(): array {
	try {

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
	} catch ( Exception $error ) {
		return [ 'error' => $error->getMessage() ];
	}
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
	$api_token_settings = cwpai_get_token_settings();

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
 * @param string $method The method.
 * @param null   $token The token.
 *
 * @return mixed
 * @throws \Exception If the token is invalid.
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

//    $body['project'] = '';
	if ( isset( $api_key_settings['project_id'] ) && $api_key_settings['project_id'] ) {
		$body['project'] = $api_key_settings['project_id'];
	}

	$request = array(
		'method'  => $method,
		'headers' => array(
			'Authorization' => 'Bearer ' . ( $token ?: $api_key_settings['token'] ),
			'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'referer'       => null, // Older version of WP are automatically adding this
		),
		'body'    => json_encode($body),
	);

    $url = CWPAI_API_SERVER . '/api/' . 'wp-site-project-synchronize';
    if($method === 'POST') {
        $url = CWPAI_API_SERVER . '/api/' . 'wp-site-projects';
    }

	$response = wp_remote_request(
        $url,
		$request
	);

	if ( is_a( $response, 'WP_Error' ) ) {
		throw new \Exception( esc_html( $response->get_error_message() ) );
	} elseif ( 401 === $response['response']['code'] ) {
		throw new \Exception( esc_html( __( 'Your token is invalid. Please add a new one!', 'wp-cwpai-settings-page' ) ) );
	} elseif ( ! in_array( $response['response']['code'], [ 200, 201 ], true ) ) {
		$body = json_decode( $response['body'], true );
        error_log(print_r($body, true));
		if ( ! empty( $body['errors'] ) ) {
			$messages = array();
			array_walk_recursive(
                $body['errors'],
				function ( $a ) use ( &$messages ) {
					$messages[] = $a;
				}
			);
			throw new \Exception( esc_html( implode( ', ', $messages ) ) );
		}

        throw new \Exception( esc_html( $body['response']['message'] ?? $body['message'] ?? 'Error' ) );

	}

	return json_decode( $response['body'], true );
}


/**
 * Save settings
 *
 * @param array $data The data.
 *
 * @return void
 */
function cwpai_save_settings( array $data ) {
	$api_token_settings = cwpai_get_token_settings();;
	$data    = array_merge( $api_token_settings, $data );

	update_option(
		'cwpai-settings/api-token',
		$data,
		false
	);
}


function cwpai_get_token_settings(): array {
    $settings = get_option(
        'cwpai-settings/api-token'
    );

    if ( ! $settings ) {
        $settings = array(
            'token'             => '',
            'token_placeholder' => '',
            'project_id'        => '',
            'project_name'      => '',
            'auto_syncronize'   => true,
            'synchronized_at'   => '',
        );
    }

    return $settings;
}