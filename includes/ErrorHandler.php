<?php

namespace WpAi\CodeWpHelper;

use WpAi\CodeWpHelper\Utils\CodewpaiConfig;
use WpAi\CodeWpHelper\Utils\CodewpaiFilesystem;
use WpAi\CodeWpHelper\Utils\ErrorLogger;
use WpAi\CodeWpHelper\Utils\PackagesManager;

class ErrorHandler {
	private array $config;

	public function __construct() {
		//phpcs:ignore
		set_error_handler( array( $this, 'errorHandler' ) );
		register_shutdown_function( array( $this, 'fatalErrorHandler' ) );
		$this->config = CodewpaiConfig::all();
	}

	/**
	 * The error handler.
	 *
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 *
	 * @return void
	 */
	public function errorHandler( $errno, $errstr, $errfile, $errline ): void {
		$this->errorLogger(
			array(
				'type'      => $errno,
				'message'   => $errstr,
				'file_name' => $errfile,
				'line'      => $errline,
			)
		);
	}

	/**
	 * The fatal error handler.
	 *
	 * @return void
	 */
	public function fatalErrorHandler(): void {
		$error = error_get_last();

		if ( ! defined( 'WP_CONTENT_DIR' ) && $this->config['in_playground'] ) {
			define( 'WP_CONTENT_DIR', '/wordpress/wp-content' );
		}

		if ( $error ) {
			$error['file_name'] = $error['file'];
			unset( $error['file'] );
			$this->errorLogger( $error );
		}
	}

	/**
	 * Log the error to a file.
	 * If the error is from a snippet, disable it.
	 * Redirect to the snippets page if the error is from a snippet.
	 *
	 * @param array $error The error to log.
	 *
	 * @return void
	 */
	public function errorLogger( array $error ): void {

		if ( $error && $error['message'] ) {

			( new ErrorLogger() )->logErrors( $error );

			// If the error is from a snippet, disable it.
			// @todo: only disable the snippet if it's a fatal error?!.
			if ( ! empty( $error['file_name'] ) && $this->filenameIsInPackagesDir( $error['file_name'] ) ) {
				$packages_manager = new PackagesManager( true );
				$snippet          = $packages_manager->getSnippetByPath( $error['file_name'] );
				if ( $snippet ) {
					$packages_manager->setFileError(
						$snippet['package_id'],
						$snippet,
						$error
					);
					$admin_url = $this->config['plugin_url'] . '&tab=packages&snippet_error=' . rawurlencode( $snippet['location'] );

					// Redirect to the snippets page.
					// phpcs:ignore
					echo '<script>console.log("Snippet has been disabled. Redirecting to ' . $admin_url . '"); window.location.href = "' . $admin_url . '";</script>';
				}
			}
		}
	}

	/**
	 * Check if the filename is in the packages' directory.
	 *
	 * @param string $filename The filename to check.
	 *
	 * @return bool
	 */
	private function filenameIsInPackagesDir( string $filename ): bool {
		return str_contains( $filename, $this->config['packages_dir'] );
	}
}
