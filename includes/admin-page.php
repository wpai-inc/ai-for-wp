<?php

/**
 * Register admin menu
 *
 * @return void
 */
function cwpai_admin_menu() {
	$hook_name = add_submenu_page(
		'options-general.php',
		__( 'CodeWP Helper', 'wp-cwpai-settings-page' ),
		__( 'CodeWP Helper', 'wp-cwpai-settings-page' ),
		'manage_options',
		'cwpai-helper',
		'cwpai_admin_page'
	);

	add_action( "load-{$hook_name}", 'cwpai_admin_page_load' );
}
add_action( 'admin_menu', 'cwpai_admin_menu', 100 );


/**
 * Includes asset.
 *
 * @return void
 */
function cwpai_enqueue_scripts() {
	cwpai_enqueue_scripts_from_asset_file( 'settings', CWPAI_SETTINGS_PLUGIN_FILE );

	wp_localize_script( 'wp-cwpai-settings-page-settings', 'CWPAI_SETTINGS', apply_filters( 'cwpai_settings_variables', array() ) );
}

/**
 * Admin page load
 *
 * @return void
 */
function cwpai_admin_page_load() {
	add_action( 'admin_enqueue_scripts', 'cwpai_enqueue_scripts' );
	// remove_all_filters( 'admin_footer_text' );
	// remove_filter( 'update_footer', 'core_update_footer' );
	add_filter( 'update_footer', 'cwpai_update_footer' );
	// add_filter( 'admin_footer_text', 'cwpai_admin_footer_text' );
	add_filter( 'admin_body_class', 'cwpai_admin_body_class' );

	$screen = get_current_screen();

	$screen->add_help_tab(
		array(
			'id'      => 'cwpai_400_error_help_tab',
			'title'   => __( '400 Error?', 'wp-cwpai-settings-page' ),
			/* translators: %s: will be replaced by current site URL */
			'content' => '<p>' . sprintf( __( 'Ensure that the site url, <strong>%s</strong>, is equal to the Project URL in CodeWP.', 'wp-cwpai-settings-page' ), get_site_url() ) . '</p>',
		)
	);
	$screen->add_help_tab(
		array(
			'id'      => 'cwpai_pro_user_help_tab',
			'title'   => __( 'CodeWP Pro User?', 'wp-cwpai-settings-page' ),
			'content' => '<p>' . __( 'Contact us and we\'ll walk you through the setup of this plugin.', 'wp-cwpai-settings-page' ) . '</p>',
		)
	);
}

/**
 * Admin page body class
 *
 * @param string $classes Body class.
 *
 * @return string
 */
function cwpai_admin_body_class( string $classes ): string {
	$classes .= ' cwpai-settings';

	return $classes;
}

/**
 * Update footer
 *
 * @return string
 */
function cwpai_update_footer(): string {
	return '<div style="float: right;">Made with love 💚 by the <a href="https://codewp.ai/" target="_blank">CodeWP Team</a></div>';
}

/**
 * Admin footer text
 *
 * @return string
 */
function cwpai_admin_footer_text(): string {
	return '';
}

/**
 * Admin page
 *
 * @return void
 */
function cwpai_admin_page() {
	?>
	<noscript>
		<div class="no-js"><?php echo esc_html__( 'Warning: This options panel will not work properly without JavaScript, please enable it.', 'wp-cwpai-settings-page' ); ?></div>
	</noscript>
	<style>
		#cwpai-ui-loading {
			height: calc(100vh - 100px);
			display: flex;
			align-items: center;
			justify-content: center;
		}
	</style>
	<div id="cwpai-ui-loading"><?php echo esc_html__( 'Loading…', 'wp-cwpai-settings-page' ); ?></div>
	<div id="cwpai-ui-settings"></div>
	<?php
}
