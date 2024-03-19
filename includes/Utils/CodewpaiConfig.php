<?php

namespace WpAi\CodeWpHelper\Utils;

use WpAi\CodeWpHelper\Main;

class CodewpaiConfig {
	private static array $config;
	private static self $instance;

	private function __construct() {
		self::$config = array(
			'plugin_file'    => CODEWPAI_PLUGIN_FILE,
			'plugin_dir'     => plugin_dir_path( CODEWPAI_PLUGIN_FILE ),
			'plugin_url'     => admin_url( 'options-general.php?page=ai-for-wp' ),
			'packages_dir'   => apply_filters( 'ai_for_wp_packages_dir', WP_CONTENT_DIR . '/ai-for-wp-packages/' ),
			'debug_file'     => WP_CONTENT_DIR . '/ai-for-wp-debug.json', // This will only be used in playground.
			'api_host'       => defined( 'CODEWPAI_API_HOST' ) ? CODEWPAI_API_HOST : Main::API_HOST,
			'api_key'        => 'codewpai_api_token',
			'notice_visible' => 'codewpai_notice_visible',
			'in_playground'  => defined( 'CWP_PLAYGROUND' ) && true === CWP_PLAYGROUND,
			'api_token'      => get_option( 'codewpai_api_token' ),
		);
	}

	/**
	 * Get all the config values.
	 *
	 * @return array
	 */
	public static function all(): array {
		self::getInstance();

		return self::$config;
	}

	/**
	 * Get a specific config value.
	 *
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public static function get( string $key ) {
		self::getInstance();

		return self::$config[ $key ] ?? null;
	}

	/**
	 * Get the instance of the class.
	 *
	 * @return CodewpaiConfig
	 */
	public static function getInstance(): CodewpaiConfig {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
