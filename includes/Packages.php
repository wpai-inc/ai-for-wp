<?php

namespace WpAi\CodeWpHelper;

use Exception;
use WP_Error;
use WpAi\CodeWpHelper\Utils\CodewpaiFilesystem;
use WpAi\CodeWpHelper\Utils\PackagesManager;
use WpAi\CodeWpHelper\Utils\RegisterAjaxMethod;
use WpAi\CodeWpHelper\Utils\HelperFunctions;

class Packages {

	public function __construct() {

		new RegisterAjaxMethod( 'codewpai_packages', array( $this, 'packages' ) );
		new RegisterAjaxMethod( 'codewpai_install_package', array( $this, 'installPackage' ) );
		new RegisterAjaxMethod( 'codewpai_update_package', array( $this, 'updatePackage' ) );
		new RegisterAjaxMethod( 'codewpai_uninstall_package', array( $this, 'uninstallPackage' ) );
		new RegisterAjaxMethod( 'codewpai_toggle_all_snippets', array( $this, 'toggleAllSnippets' ) );
		new RegisterAjaxMethod( 'codewpai_toggle_snippet', array( $this, 'toggleSnippet' ) );
		new RegisterAjaxMethod( 'codewpai_get_snippet_code', array( $this, 'codewpaiGetSnippetCode' ) );
	}


	/**
	 * Returns a list of all the packages.
	 *
	 * @throws Exception If the API token is not set.
	 */
	public function packages(): array {
		$force_update = (bool) HelperFunctions::codewpai_get_request_string( 'force_update', false, false );

		return ( new PackagesManager( false, $force_update ) )->getAllPackages();
	}

	/**
	 * Install a package.
	 *
	 * @throws Exception If the request fails.
	 */
	public function installPackage(): array {

		$package_id = HelperFunctions::codewpai_get_request_string( 'package_id', true );

		return ( new PackagesManager() )->installPackage( $package_id )->getAllPackages();
	}

	/**
	 * Update a package.
	 *
	 * @throws Exception If the request fails.
	 */
	public function updatePackage(): array {

		$package_id = HelperFunctions::codewpai_get_request_string( 'package_id', true );

		return ( new PackagesManager() )->installPackage( $package_id )->getAllPackages();
	}

	/**
	 * Uninstall a package.
	 *
	 * @throws Exception If the API token is not set.
	 */
	public function uninstallPackage(): array {
		$package_id = HelperFunctions::codewpai_get_request_string( 'package_id', true );

		return ( new PackagesManager() )->uninstallPackage( $package_id )->getAllPackages();
	}


	/**
	 * Enable all the snippets.
	 *
	 * @throws Exception If the API token is not set.
	 */
	public function toggleAllSnippets(): array {
		$package_id = HelperFunctions::codewpai_get_request_string( 'package_id', true );
		$enabled    = (bool) HelperFunctions::codewpai_get_request_string( 'enabled', true );

		return ( new PackagesManager() )->toggleAllPackageSnippets( $package_id, $enabled )->getAllPackages();
	}

	/**
	 * Toggle snippet.
	 *
	 * @throws Exception If the API token is not set.
	 */
	public function toggleSnippet(): array {
		$package_id = HelperFunctions::codewpai_get_request_string( 'package_id', true );
		$snippet_id = HelperFunctions::codewpai_get_request_string( 'snippet_id', true );
		$enabled    = (bool) HelperFunctions::codewpai_get_request_string( 'enabled', true );

		return ( new PackagesManager() )->togglePackageSnippets( $package_id, $snippet_id, $enabled )->getAllPackages();
	}

	/**
	 * Get the snippet code.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function codewpaiGetSnippetCode(): array {
		$package_id = HelperFunctions::codewpai_get_request_string( 'package_id', true );
		$snippet_id = HelperFunctions::codewpai_get_request_string( 'snippet_id', true );

		return [
			'code' => ( new PackagesManager( true ) )->getSnippetCode( $package_id, $snippet_id ),
		];
	}
}
