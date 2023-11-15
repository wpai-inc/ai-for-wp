<?php
/**
 * Enqueue scripts from asset file
 *
 * @param string $name      The name of the asset file.
 * @param string $plugin_file The absolute path to the plugin file.
 *
 * @return void
 */
function cwpai_enqueue_scripts_from_asset_file( string $name, string $plugin_file ) {
	$script_asset_path = dirname( $plugin_file ) . "/build/$name.asset.php";
	if ( file_exists( $script_asset_path ) ) {
		$script_asset        = include $script_asset_path;
		$script_dependencies = $script_asset['dependencies'] ?? array();

		if ( in_array( 'wp-media-utils', $script_dependencies, true ) ) {
			wp_enqueue_media();
		}

		if ( in_array( 'wp-react-refresh-runtime', $script_asset['dependencies'], true ) && ! constant( 'SCRIPT_DEBUG' ) ) {
			wp_die( esc_html__( 'SCRIPT_DEBUG should be true. You use `hot` mode, it requires `wp-react-refresh-runtime` which availably only when SCRIPT_DEBUG is enabled.', 'wp-cwpai-settings-page' ) );
		}

		wp_enqueue_script( "wp-cwpai-settings-page-$name", plugins_url( "build/$name.js", $plugin_file ), $script_dependencies, $script_asset['version'], true );

		$style_dependencies = array();

		if ( in_array( 'wp-components', $script_dependencies, true ) ) {
			$style_dependencies[] = 'wp-components';
		}

		wp_enqueue_style( "wp-cwpai-settings-page-$name", plugins_url( "build/$name.css", $plugin_file ), $style_dependencies, $script_asset['version'], 'all' );
	}
}
