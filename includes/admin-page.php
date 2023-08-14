<?php

namespace CodeWPHelper;

add_action('admin_menu', __NAMESPACE__ . '\\admin_menu', 100);

function admin_menu()
{
	$hook_name = add_submenu_page(
		'options-general.php',
		__('CodeWP Helper', 'wp-cwpai-settings-page'),
		__('CodeWP Helper', 'wp-cwpai-settings-page'),
		'manage_options',
		'cwpai-helper',
		__NAMESPACE__ . '\\admin_page'
	);

	add_action("load-{$hook_name}", __NAMESPACE__ . '\\admin_page_load');
}


/**
 * Includes asset.
 *
 * @return void
 */
function wp_enqueue_scripts()
{
	enqueue_scripts_from_asset_file('settings', CWPAI_SETTINGS_PLUGIN_FILE);

	wp_localize_script('wp-cwpai-settings-page-settings', 'CWPAI_SETTINGS', apply_filters('cwpai_settings_variables', []));

	// wp_set_script_translations( 'wp-cwpai-settings-page-core', 'wp-cwpai-settings-page', plugin_dir_path( CWPAI_SETTINGS_PLUGIN_FILE ) . 'languages/' ); ?
}

function admin_page_load()
{
	add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\wp_enqueue_scripts');
	remove_all_filters('admin_footer_text');
	remove_filter('update_footer', 'core_update_footer');
	add_filter('update_footer', __NAMESPACE__ . '\\update_footer');
	add_filter('admin_footer_text', __NAMESPACE__ . '\\admin_footer_text');
	add_filter('admin_body_class', __NAMESPACE__ . '\\admin_body_class');

	$screen = get_current_screen();

	$screen->add_help_tab(
		[
			'id'      => 'cwpai_400_error_help_tab',
			'title'   => __('400 Error?', 'wp-cwpai-settings-page'),
			'content' => '<p>' . __('Ensure that the site url, <strong>' . get_site_url() . '</strong>, is equal to the Project URL in CodeWP.', 'wp-cwpai-settings-page') . '</p>',
		]
	);
	$screen->add_help_tab(
		[
			'id'      => 'cwpai_pro_user_help_tab',
			'title'   => __('CodeWP Pro User?', 'wp-cwpai-settings-page'),
			'content' => '<p>' . __('Contact us and we\'ll walk you through the setup of this plugin.', 'wp-cwpai-settings-page') . '</p>',
		]
	);
}

function admin_body_class($classes)
{
	$classes .= ' cwpai-settings';

	return $classes;
}

function update_footer()
{
	return '<div style="float: right;">Made with love 💚 by the <a href="https://codewp.ai/" target="_blank">CodeWP Team</a></div>';
}

function admin_footer_text()
{
	return '';
}

function admin_page()
{
?>
	<noscript>
		<div class="no-js"><?php echo esc_html__('Warning: This options panel will not work properly without JavaScript, please enable it.', 'wp-cwpai-settings-page'); ?></div>
	</noscript>
	<style>
		#ui-loading {
			height: calc(100vh - 100px);
			display: flex;
			align-items: center;
			justify-content: center;
		}
	</style>
	<div id="ui-loading"><?php echo esc_html__('Loading…', 'wp-cwpai-settings-page'); ?></div>
	<div id="ui-settings"></div>
<?php
}
