<?php

namespace WpAi\CodeWpHelper\Utils;

class PackagesManager {

	private array $default_packages = [
		'packages'   => [],
		'updated_at' => 0,
	];
	private array $packages;
	private array $codewpai_packages;

	/**
	 * Packages constructor.
	 *
	 * @param bool $disable_check_for_updates If true, it will disable the check for updates.
	 * @param bool $force_check_for_updates If true, it will force the check for updates.
	 *
	 * @throws \Exception If the API token is not set.
	 */
	public function __construct( bool $disable_check_for_updates = false, bool $force_check_for_updates = false ) {
		$packages = get_option( 'codewpai_packages' );
		if ( empty( $packages ) ) {
			$this->packages = $this->default_packages;
		} else {
			$this->packages = $packages;
		}

		if ( ! $disable_check_for_updates && ( ! defined( 'CWP_PLAYGROUND' ) || ! CWP_PLAYGROUND ) ) {
			$this->checkForUpdates( $force_check_for_updates );
		}
	}

	/**
	 * Check for updates for the packages.
	 *
	 * @param bool $force_check_for_updates If true, it will force the check for updates.
	 *
	 * @throws \Exception If the API token is not set.
	 */
	private function checkForUpdates( bool $force_check_for_updates = false ): void {
		// return the data if it was updated in the last hour, and we don't force the check.
		if ( ! $force_check_for_updates && ! empty( $this->packages['updated_at'] ) && $this->packages['updated_at'] > strtotime( '-1 hour' ) ) {
			return;
		}

		$this->getAllCodewpaiPackages();

		// merge the data.
		$codewpai_packages = $this->codewpai_packages['packages'];
		$packages          = $this->packages['packages'];

		foreach ( $codewpai_packages as $codewpai_package ) {
			if ( empty( $packages[ $codewpai_package['id'] ] ) ) {
				$packages[ $codewpai_package['id'] ] = $this->preparePackageStructure( $codewpai_package );
			} else {
				$packages[ $codewpai_package['id'] ] = $this->checkForPackageUpdates( $codewpai_package, $packages[ $codewpai_package['id'] ] );
			}
		}

		$this->packages = [
			'packages'   => $packages,
			'updated_at' => time(),
		];

		update_option( 'codewpai_packages', $this->packages );
	}

	/**
	 * Get the packages from the API.
	 *
	 * @throws \Exception If the API token is not set.
	 */
	public function getAllCodewpaiPackages(): void {

		if ( ! empty( $this->codewpai_packages ) ) {
			return;
		}
		if ( empty( CodewpaiConfig::get( 'api_token' )['token'] ) ) {
			throw new \Exception( 'The API token is not set' );
		}
		$response = CodewpRequest::request( 'GET', '/api/packages/' . CodewpaiConfig::get( 'api_token' )['project_id'] );

		$codewpai_packages = [];

		if ( empty( $response ) ) {
			// There are no available packages.
			$this->codewpai_packages = [
				'packages'   => [],
				'updated_at' => time(),
			];

			return;
		}

		foreach ( $response as $package ) {
			// prepare packages structure.
			$codewpai_packages['packages'][ $package['id'] ] = $this->prepareCodewpaiPackagesData( $package );
		}
		$codewpai_packages['updated_at'] = time();
		$this->codewpai_packages         = $codewpai_packages;
	}

	/**
	 * Prepare the packages data from the API.
	 *
	 * @param array $package
	 *
	 * @return array
	 */
	public function prepareCodewpaiPackagesData( array $package, bool $snippets_enabled = false ): array {
		$files = [];
		foreach ( $package['files'] as $file ) {
			$files[] = [
				'id'          => $file['id'],
				'name'        => $file['name'],
				'path'        => $file['path'],
				'location'    => $file['location'],
				'extension'   => $file['extension'],
				'description' => $file['description'],
				'updated_at'  => $file['updated_at'],
			];
		}

		return [
			'id'          => $package['id'],
			'name'        => $package['name'],
			'description' => $package['description'],
			'type'        => $package['type'],
			'files'       => $files,
			'updated_at'  => $package['updated_at'],
		];
	}


	/**
	 * Check if there are updates for the packages.
	 *
	 * @param array $codewpai_package The package from the API.
	 * @param array $package The local package.
	 *
	 * @return array
	 */
	private function checkForPackageUpdates( array $codewpai_package, array $package ): array {

		// if the updated_at is different, then there is an update.
		if ( $codewpai_package['updated_at'] !== $package['updated_at'] ) {
			$package['update_available'] = true;

			return $package;
		}

		// @todo: if the package is not installed always update local package with package from the API.

		foreach ( $codewpai_package['files'] as $file ) {
			$packages_file = null;
			foreach ( $package['files'] as $local_file ) {
				if ( $file['id'] === $local_file['id'] ) {
					$packages_file = $local_file;
					break;
				}
			}

			// if the file is not found, then there is an update.
			if ( ! $packages_file ) {
				$package['update_available'] = true;

				return $package;
			}

			// if the file is found, but the updated_at is different, then there is an update.
			if ( $file['updated_at'] !== $packages_file['updated_at'] ) {
				$package['update_available'] = true;

				return $package;
			}
		}

		return $package;
	}

	/**
	 * Prepare the package structure.
	 *
	 * @param array $codewpai_package The package from the API.
	 * @param array $packages The local packages.
	 *
	 * @return mixed
	 */
	private function preparePackageStructure( array $codewpai_package ): array {
		$package                         = $codewpai_package;
		$package['installed']            = false;
		$package['has_enabled_snippets'] = false;
		$package['update_available']     = false;

		if ( ! empty( $package['files'] ) ) {
			foreach ( $package['files'] as $file_key => $file ) {
				$package['files'][ $file_key ]['enabled'] = false;
			}
		}

		return $package;
	}

	public function getSnippetCode( string $package_id, string $snippet_id ): string {
		$package = $this->packages['packages'][ $package_id ];
		foreach ( $this->packages['packages'][ $package_id ]['files'] as $file ) {
			if ( $file['id'] === $snippet_id ) {
				$snippet = $file;
				break;
			}
		}

		return CodewpaiFilesystem::filesystem()->get_contents( $this->package_dir( $package ) . '/' . $snippet['path'] );
	}

	/**
	 * Install a package by its ID.
	 *
	 * @param string $package_id The package ID.
	 *
	 * @throws \Exception If the request fails.
	 */
	public function installPackage( string $package_id ): static {

		$this->getAllCodewpaiPackages();
		$package     = $this->packages['packages'][ $package_id ];
		$package_dir = $this->package_dir( $package );

		if ( CodewpaiFilesystem::filesystem()->exists( $package_dir ) ) {
			CodewpaiFilesystem::filesystem()->delete( $package_dir, true );
		}

		CodewpRequest::download( $package );

		$this->markPackageAsInstalled( $package );

		return $this;
	}

	/**
	 * Mark a package as installed.
	 *
	 * @param array $package The package.
	 *
	 * @throws \Exception If the API token is not set.
	 */
	private function markPackageAsInstalled( array $package ): void {

		$original_package = $package;
		$package          = array_merge( $package, $this->codewpai_packages['packages'][ $package['id'] ] );

		$data[ $package['id'] ]                     = $package;
		$data[ $package['id'] ]['installed']        = true;
		$data[ $package['id'] ]['update_available'] = false;

		foreach ( $package['files'] as $file_key => $file ) {
			foreach ( $original_package['files'] as $original_file ) {
				if (
					$original_file['id'] === $file['id'] &&
					$original_file['enabled']
				) {
					$data[ $package['id'] ]['files'][ $file_key ]['enabled'] = true;
					$data[ $package['id'] ]['has_enabled_snippets']          = true;
				}
			}
		}

		$this->updatePackage( $package, $data );
	}


	/**
	 * Uninstall a package by its ID.
	 *
	 * @param string $package_id The package ID.
	 *
	 * @throws \Exception If the request fails.
	 */
	public function uninstallPackage( string $package_id ): static {

		$package     = $this->packages['packages'][ $package_id ];
		$package_dir = $this->package_dir( $package );

		// if it is a plugin, deactivate the plugin.
		if ( 1 === $package['type'] ) {
			deactivate_plugins( $package_id . '/index.php' );
		}

		// check the package on the folder. If it exists remove it.
		if ( CodewpaiFilesystem::filesystem()->exists( $package_dir ) ) {
			CodewpaiFilesystem::filesystem()->delete( $package_dir, true );
		}

		$this->markPackageAsUninstalled( $package );

		return $this;
	}

	/**
	 * Mark a package as installed.
	 *
	 * @param array $package The package.
	 *
	 * @throws \Exception If the API token is not set.
	 */
	private function markPackageAsUninstalled( array $package ): void {

		$data[ $package['id'] ]                         = $package;
		$data[ $package['id'] ]['installed']            = false;
		$data[ $package['id'] ]['update_available']     = false;
		$data[ $package['id'] ]['has_enabled_snippets'] = false;

		foreach ( $package['files'] as $file_key => $file ) {
			$data[ $package['id'] ]['files'][ $file_key ]['enabled'] = false;
		}

		$this->updatePackage( $package, $data );
	}

	public function toggleAllPackageSnippets( $package_id, $enabled ): static {

		$package = $this->packages['packages'][ $package_id ];

		$data[ $package_id ]                         = $package;
		$data[ $package_id ]['has_enabled_snippets'] = $enabled;
		foreach ( $package['files'] as $file_key => $file ) {
			$data[ $package_id ]['files'][ $file_key ]['enabled'] = $enabled;
		}

		$this->updatePackage( $package, $data );

		return $this;
	}

	public function togglePackageSnippets( $package_id, $snippet_id, $enabled ): static {
		$package = $this->packages['packages'][ $package_id ];

		$data[ $package_id ]  = $package;
		$has_enabled_snippets = $enabled;
		foreach ( $package['files'] as $file_key => $file ) {
			if ( $file['id'] === $snippet_id ) {
				$data[ $package_id ]['files'][ $file_key ]['enabled'] = $enabled;
			} elseif ( ! $has_enabled_snippets && true === $file['enabled'] ) {
				$has_enabled_snippets = true;
			}
		}
		$data[ $package_id ]['has_enabled_snippets'] = $has_enabled_snippets;

		$this->updatePackage( $package, $data );

		return $this;
	}

	/**
	 * Get the package directory.
	 *
	 * @param array $package The package.
	 *
	 * @return string
	 */
	private function package_dir( array $package ): string {
		if ( 1 === $package['type'] ) {
			return WP_PLUGIN_DIR . '/' . $package['id'];
		}

		return CodewpaiConfig::get( 'packages_dir' ) . '/' . $package['id'];
	}

	/**
	 * Get the packages.
	 *
	 * @return array
	 */
	public function getAllPackages(): array {
		if ( empty( $this->packages['packages'] ) ) {
			return [];
		}

		return array_values( $this->packages['packages'] );
	}

	private function updatePackage( array $package, array $data ): void {
		$this->packages['packages'][ $package['id'] ] = $data[ $package['id'] ];
		$this->packages['updated_at']                 = time();

		update_option( 'codewpai_packages', $this->packages );
	}

	public function setFileError( string $package_id, array $snippet, array $error ): void {
		$package             = $this->packages['packages'][ $package_id ];
		$data[ $package_id ] = $package;
		foreach ( $package['files'] as $file_key => $file ) {
			if ( $file['id'] === $snippet['id'] ) {
				$data[ $package_id ]['files'][ $file_key ]['enabled'] = false;
				$data[ $package_id ]['files'][ $file_key ]['error']   = $error;
			}
		}

		$this->updatePackage( $package, $data );
	}

	public function getSnippetByPath( $path ) {
		foreach ( $this->packages['packages'] as $package ) {
			foreach ( $package['files'] as $file ) {
				if ( str_contains($path, $package['id']) && str_ends_with( $path, $file['path'] ) ) {
					$file['package_id'] = $package['id'];

					return $file;
				}
			}
		}

		return null;
	}

	/**
	 * Enable a package in the playground.
	 *
	 * @param string $package_data The package data.
	 * @param string $file_id The file ID.
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function playgroundEnablePackage( string $package_data, string $file_id ): string {

		$package_data = json_decode( base64_decode( $package_data ), true );
		$package_data = $this->prepareCodewpaiPackagesData( $package_data );

		foreach ( $package_data['files'] as $file_key => $file ) {
			if ( $file_id === $file['id'] ) {
				$package_data['files'][ $file_key ]['enabled'] = true;
			}
		}
		$package_data['installed']            = true;
		$package_data['has_enabled_snippets'] = true;


		$data = [
			'packages'   => [
				$package_data['id'] => $package_data
			],
			'updated_at' => time(),
		];

		update_option( 'codewpai_packages', $data );

		$packages_dir = CodewpaiConfig::get( 'packages_dir' );
		$package_zip  = $packages_dir . $package_data['id'] . '.zip';
		$files_list   = glob( $packages_dir . '*' );
		$zip_size     = filesize( $package_zip );

		$unzip = unzip_file( $package_zip, $packages_dir . $package_data['id'] );
		if ( is_wp_error( $unzip ) ) {
			throw new \Exception( wp_kses( $unzip->get_error_message(), [] ) );
		}

		wp_delete_file( $package_zip );

		return json_encode( [
			'packages_data'         => $package_data,
			'packages_data_1'       => $data,
			'package_folder_exists' => CodewpaiFilesystem::filesystem()->exists( $packages_dir . $package_data['id'] ),
			'zip_size'              => $zip_size,
			'files_list_1'          => $files_list,
			'files_list'            => glob( $packages_dir . $package_data['id'] . '/*' ),
		] );

	}

}
