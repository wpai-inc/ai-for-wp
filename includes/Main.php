<?php

namespace WpAi\CodeWpHelper;

use WpAi\CodeWpHelper\Utils\PackagesRunner;

class Main {

	public const VERSION     = '0.2.5';
	public const TEXT_DOMAIN = 'ai-for-wp';
	public const API_HOST    = 'https://app.codewp.ai';
	public Packages $packages;

	public function __construct( $plugin_file ) {
		register_deactivation_hook( $plugin_file, array( $this, 'deactivate' ) );
		new ErrorHandler();
		new Filters();
		new Ajax();
		new AdminPage();
		new Cron();
		new Logs();
		new PackagesRunner();
		$this->packages = new Packages();
	}

	/**
	 * Get the plugin nonce domain.
	 *
	 * @return string
	 */
	public static function nonce(): string {
		return self::TEXT_DOMAIN;
	}

	/**
	 * Actions to be performed on plugin deactivation
	 *
	 * @return void
	 */
	public function deactivate(): void {
		delete_option( 'codewpai_api_token' );
		delete_option( 'codewpai_notice_visible' );
		delete_option( 'codewpai_packages' );
	}
}
