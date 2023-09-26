<?php
/**
 * API Key functions
 *
 * @package CodeWPHelper
 */

namespace CodeWPHelper {

	/**
	 * Returns the debug data from the current WordPress installation
	 *
	 * @return array
	 * @throws \ImagickException If the Imagick extension is not loaded.
	 */
	function wpai_get_debug_data(): array {
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
	 * @param string $message    If not empty, the message will be shown.
	 *
	 * @return array
	 */
	function wpai_get_api_key_form_data( $show_token = false, $message = '' ): array {
		$api_token_settings = get_option(
			'cwpai-settings/api-token',
			array(
				'token'           => '',
				'project_id'      => '',
				'synchronized_at' => '',
			)
		);

		if ( ! empty( $api_token_settings['token'] ) && ! $show_token ) {
			$api_token_settings['token'] = '************************************************';
		}

		if ( ! empty( $api_token_settings['synchronized_at'] ) ) {
			$api_token_settings['synchronized_at'] = gmdate( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $api_token_settings['synchronized_at'] ) );
		}

		if ( ! empty( $message ) ) {
			$api_token_settings['message'] = $message;
		}

		return $api_token_settings;
	}

}
