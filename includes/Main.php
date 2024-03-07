<?php

namespace WpAi\CodeWpHelper;

class Main {
	const VERSION     = '0.1.0';
	const TEXT_DOMAIN = 'ai-for-wp';
	const API_HOST    = 'https://app.codewp.ai';

	private string $plugin_file;
	private string $plugin_dir;

	/**
	 * Main constructor.
	 *
	 * @param string $plugin_file The path to the plugin file.
	 */
	public function __construct( string $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->plugin_dir  = plugin_dir_path( $this->plugin_file );
		register_deactivation_hook( $this->plugin_file, array( $this, 'deactivate' ) );
		$this->bootstrap();
	}

	/**
	 * Nonce key.
	 *
	 * @return string
	 */
	public static function nonce(): string {
		return self::TEXT_DOMAIN;
	}

	/**
	 * Cleanup on deactivation.
	 *
	 * @return void
	 */
	public function deactivate(): void {
		delete_option( 'codewpai_api_token' );
		delete_option( 'codewpai_notice_visible' );
	}

	/**
	 * Bootstrap the plugin.
	 *
	 * @return void
	 */
	public function bootstrap(): void {
		new Filters( $this->plugin_file );
		new Ajax();
		new AdminPage( $this->plugin_dir, $this->plugin_file );
		new Cron();
	}
}
