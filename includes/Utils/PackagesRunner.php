<?php

namespace WpAi\CodeWpHelper\Utils;

class PackagesRunner {
	private array $packages;

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'run' ), PHP_INT_MAX );
	}

	public function run(): void {
		$this->packages       = ( new PackagesManager( true ) )->getAllPackages();
		$all_enabled_snippets = $this->allEnabledSnippets();
		if ( ! empty( $all_enabled_snippets ) ) {
			foreach ( $all_enabled_snippets as $snippet ) {
				$this->runSnippet( $snippet );
			}
		}
	}

	private function allEnabledSnippets(): array {
		$enabled_snippets = [];
		foreach ( $this->packages as $package ) {
			if ( ! empty( $package['files'] ) && 1 !== $package['type'] ) {
				foreach ( $package['files'] as $snippet ) {
					if ( ! empty( $snippet['enabled'] ) && true === $snippet['enabled'] ) {
						$snippet['package_id'] = $package['id'];
						$enabled_snippets[]    = $snippet;
					}
				}
			}
		}

		return $enabled_snippets;
	}

	/**
	 * Run the snippet.
	 *
	 * @param array $snippet
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function runSnippet( array $snippet ): void {
		if ( ! empty( $snippet ) ) {
			if ( 'php' === $snippet['extension'] && empty( $snippet['error'] ) ) {
				$snippet_file = $this->package_dir( $snippet['package_id'] ) . '/' . $snippet['path'];
				if ( CodewpaiFilesystem::filesystem()->exists( $snippet_file ) ) {
					$file_content = CodewpaiFilesystem::filesystem()->get_contents( $snippet_file );
					$tokens       = token_get_all( $file_content );
					// Check if the file is a PHP file; The first token should be the open tag.
					$is_php = T_OPEN_TAG === $tokens[0][0];
					if ( $is_php ) {
						require_once $snippet_file;
					} else {
						// Set the file error.
						$error = [
							'file_name'    => $snippet_file,
							'type'    => 4,
							'line'    => 1,
							'message' => 'The file does not start with the PHP open tag.',
						];

						( new PackagesManager( true ) )->setFileError(
							$snippet['package_id'],
							$snippet,
							$error
						);
						$error_logger = new ErrorLogger();
						$error_logger->logErrors(
							$error
						);
					}
				} else {
					( new PackagesManager( true ) )->setFileError(
						$snippet['package_id'],
						$snippet,
						[
							'file'    => $snippet['path'],
							'type'    => 0,
							'line'    => 0,
							'message' => 'The file does not exists on the filesystem.',
						]
					);
				}
			} elseif ( 'js' === $snippet['extension'] ) {
				$this->enqueueJs( $snippet );
			} elseif ( 'css' === $snippet['extension'] ) {
				$this->enqueueCss( $snippet );
			}
		}
	}

	/**
	 * Get the package directory.
	 *
	 * @param string $package_id The package id.
	 *
	 * @return string
	 */
	private function package_dir( string $package_id ): string {
		return CodewpaiConfig::get( 'packages_dir' ) . '/' . $package_id;
	}

	public function package_url( $package_id ): string {
		$package_dir = CodewpaiConfig::get( 'packages_dir' ) . '/' . $package_id;

		return trailingslashit( home_url() ) . str_replace( ABSPATH, '', $package_dir );
	}

	/**
	 * Enqueue the JS snippet.
	 *
	 * @param $snippet
	 *
	 * @return void
	 */
	public function enqueueJs( $snippet ): void {
		$snippet_file = $this->package_dir( $snippet['package_id'] ) . '/' . $snippet['path'];
		if ( CodewpaiFilesystem::filesystem()->exists( $snippet_file ) ) {
			if ( str_contains( $snippet_file, 'admin' ) ) {
				add_action( 'admin_enqueue_scripts', function () use ( $snippet ) {
					$in_footer = str_contains( $snippet['path'], 'footer' );
					wp_enqueue_script( 'codewpai-' . $snippet['id'], $this->package_url( $snippet['package_id'] ) . '/' . $snippet['path'], array(), $snippet['updated_at'], [ 'in_footer' => $in_footer ] );
				} );
			}else{
				add_action( 'wp_enqueue_scripts', function () use ( $snippet ) {
					$in_footer = str_contains( $snippet['path'], 'footer' );
					wp_enqueue_script( 'codewpai-' . $snippet['id'], $this->package_url( $snippet['package_id'] ) . '/' . $snippet['path'], array(), $snippet['updated_at'], [ 'in_footer' => $in_footer ] );
				} );
			}
		}
	}

	/**
	 * Enqueue the CSS snippet.
	 *
	 * @param $snippet
	 *
	 * @return void
	 */
	public function enqueueCss( $snippet ): void {
		$snippet_file = $this->package_dir( $snippet['package_id'] ) . '/' . $snippet['path'];
		if ( CodewpaiFilesystem::filesystem()->exists( $snippet_file ) ) {
			if ( str_contains( $snippet_file, 'admin' ) ) {
				add_action( 'admin_enqueue_scripts', function () use ( $snippet ) {
					wp_enqueue_style( 'codewpai-' . $snippet['id'], $this->package_url( $snippet['package_id'] ) . '/' . $snippet['path'], array(), $snippet['updated_at'] );
				} );
			}else{
				add_action( 'wp_enqueue_scripts', function () use ( $snippet ) {
					wp_enqueue_style( 'codewpai-' . $snippet['id'], $this->package_url( $snippet['package_id'] ) . '/' . $snippet['path'], array(), $snippet['updated_at'] );
				} );
			}
		}
	}
}
