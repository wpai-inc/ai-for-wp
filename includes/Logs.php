<?php


namespace WpAi\CodeWpHelper;

use WpAi\CodeWpHelper\Utils\CodewpaiConfig;
use WpAi\CodeWpHelper\Utils\CodewpaiFilesystem;
use WpAi\CodeWpHelper\Utils\ErrorLogger;

class Logs {

	public function __construct() {
		add_action( 'wp_ajax_codewpai_logs', array( $this, 'getLogs' ) );
	}

	/**
	 * Get the logs.
	 *
	 * @return void
	 */
	public function getLogs(): void {
		$logs = ( new ErrorLogger() )->getErrors();

		wp_send_json_success( $logs );
	}
}
