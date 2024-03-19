<?php

namespace WpAi\CodeWpHelper\Utils;

class ErrorLogger {

	private array $config;
	private \WP_Filesystem_Direct $filesystem;

	public function __construct() {
		$this->config     = CodewpaiConfig::all();
		$this->filesystem = CodewpaiFilesystem::filesystem();
	}

	/**
	 * Get the logged errors.
	 *
	 * @return array
	 */
	public function getErrors(): array {
		if ( defined( 'CWP_PLAYGROUND' ) && CWP_PLAYGROUND ) {
			$errors = $this->playgroundGetErrors();
		} else {
			// phpcs:ignore
			$errors = get_option( 'codewpai_errors', [] ) ?: [];
		}

		if ( $errors && count( $errors ) > 100 ) {
			$errors = array_slice( $errors, count( $errors ) - 100 );
		}

		return $errors;
	}

	/**
	 * Get the errors if in playground mode.
	 *
	 * @return array
	 */
	public function playgroundGetErrors(): array {
		$errors = [];
		if ( $this->filesystem->exists( $this->config['debug_file'] ) ) {
			$errors = json_decode( $this->filesystem->get_contents( $this->config['debug_file'] ), true );
		}

		return $errors;
	}

	/**
	 * Log the error.
	 *
	 * @param array $error The error to log.
	 *
	 * @return void
	 */
	public function logErrors( array $error ): void {
		$errors = $this->getErrors();
		if ( defined( 'CWP_PLAYGROUND' ) && CWP_PLAYGROUND ) {
			$this->playgroundLogError( $errors, $error );
		}
		$errors[] = $error;
		update_option( 'codewpai_errors', $errors );
	}

	/**
	 * Log the error if in playground mode.
	 *
	 * @param array $errors The errors.
	 * @param array $error The error to log.
	 *
	 * @return void
	 */
	public function playgroundLogError( array $errors, array $error ): void {
		$errors[] = $error;
		$this->filesystem->put_contents( $this->config['debug_file'], wp_json_encode( $errors ) );
	}
}
