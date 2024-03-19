<?php

namespace WpAi\CodeWpHelper\Utils;

class CodewpaiFilesystem {
	/**
	 * Get the filesystem object.
	 *
	 * @return \WP_Filesystem_Direct
	 */
	public static function filesystem(): \WP_Filesystem_Direct {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem( false, null, true );
		}

		return $wp_filesystem;
	}
}
