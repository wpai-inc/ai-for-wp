<?php

namespace WpAi\CodeWpHelper\Utils;

class CodewpRequest {

	/**
	 * Make a request to the CodeWP API.
	 *
	 * @param string $method The request method.
	 * @param string $url The request URL.
	 *
	 * @return array
	 * @throws \Exception If the request fails.
	 */
	public static function request( string $method, string $url = 'GET' ): array {
		$request = array(
			'method'  => $method,
			'headers' => array(
				'Authorization' => 'Bearer ' . CodewpaiConfig::get( 'api_token' )['token'],
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
				'referer'       => null, // Older version of WP are automatically adding this.
			),
		);

		$response = wp_remote_request(
			CodewpaiConfig::get( 'api_host' ) . $url,
			$request
		);

		if ( is_wp_error( $response ) ) {
			throw new \Exception( wp_kses( $response->get_error_message(), [] ) );
		}

		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		if ( 200 !== $response_code ) {
			throw new \Exception( wp_kses( $response_message, [] ) );

		}

		if ( ! $response['body'] ) {
			return [];
		}

		return json_decode( $response['body'], true );
	}

	/**
	 * Download a package from the CodeWP API.
	 *
	 * @param array $package The package to download.
	 * @param int   $timeout The timeout for the request.
	 *
	 * @throws \Exception If the request fails.
	 */
	public static function download( array $package, int $timeout = 300 ): string {
		$package_id      = $package['id'];
		$download_url    = CodewpaiConfig::get( 'api_host' ) . "/packages/{$package_id}/download";
		$download_folder = CodewpaiConfig::get( 'packages_dir' );
		$package_zip     = $download_folder . $package_id . '.zip';

		$tmp_file_name = wp_tempnam( "{$package_id}.zip" );

		if ( ! $tmp_file_name ) {
			throw new \Exception( wp_kses( __( 'Could not download the package. File already exists', 'ai-for-wp' ), [] ) );
		}

		$request = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . CodewpaiConfig::get( 'api_token' )['token'],
				'referer'       => null, // Older version of WP are automatically adding this.
				'timeout'       => $timeout,
				'stream'        => true,
				'filename'      => $tmp_file_name,
			),
		);

		$response = wp_safe_remote_get(
			$download_url,
			$request
		);

		if ( is_wp_error( $response ) ) {
			wp_delete_file( $tmp_file_name );
			throw new \Exception( wp_kses( $response->get_error_message(), [] ) );
		}

		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );

		if ( 200 !== $response_code ) {
			throw new \Exception( wp_kses( $response_message, [] ) );
		}

		// Add the content to the tmp file.
		CodewpaiFilesystem::filesystem()->put_contents( $tmp_file_name, $response['body'] );

		if ( ! file_exists( CodewpaiConfig::get( 'packages_dir' ) ) ) {
			if ( ! CodewpaiFilesystem::filesystem()->mkdir( CodewpaiConfig::get( 'packages_dir' ) ) ) {
				throw new \Exception( wp_kses( __( 'Could not create the packages directory', 'ai-for-wp' ), [] ) );
			}
		}

		// Move the file to the packages' directory.
		CodewpaiFilesystem::filesystem()->move( $tmp_file_name, $package_zip );
		if ( ! file_exists( $package_zip ) ) {
			throw new \Exception( wp_kses( __( 'Could not move the file to the packages directory', 'ai-for-wp' ), [] ) );
		}

		// Unzip $tmp_file_name to $download_folder.
		if ( 1 === $package['type'] ) {
			$unzip = \unzip_file( $package_zip, WP_PLUGIN_DIR . '/' . $package_id );
		} else {
			$unzip = \unzip_file( $package_zip, $download_folder . $package_id );
		}
		if ( is_wp_error( $unzip ) ) {
			throw new \Exception( wp_kses( $unzip->get_error_message(), [] ) );
		}

		// Delete $tmp_file_name.
		wp_delete_file( $tmp_file_name );
		wp_delete_file( $package_zip );

		return $download_folder . $package_id;
	}

	/**
	 * Unzip a package in playground mode.
	 *
	 * @param string $package_id  The package ID.
	 * @param string $package_zip The package zip file.
	 *
	 * @throws \Exception
	 */
	public static function playgroundUnzipPackage( $package_id, $package_zip ): void {
		$unzip = \unzip_file( $package_zip, CodewpaiConfig::get( 'packages_dir' ) . $package_id );
		if ( is_wp_error( $unzip ) ) {
			throw new \Exception( wp_kses( $unzip->get_error_message(), [] ) );
		}
		wp_delete_file( $package_zip );
	}
}
