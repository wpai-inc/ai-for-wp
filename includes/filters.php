<?php
/**
 * Filters
 *
 * @package CodeWPHelper
 */

add_filter(
	'cwpai_settings_variables',
	function ( $variables ) {
		$current_user = wp_get_current_user();

		$variables['nonce']          = wp_create_nonce( NONCE_ACTION );
		$variables['codewp_server']  = CWPAI_API_SERVER;
		$variables['user']['name']   = $current_user->display_name;
		$variables['project']        = cwpai_get_api_key_form_data();
		$variables['notice_visible'] = get_option( 'cwpai-settings/notice_visible', 1 );

		return $variables;
	}
);


/**
 * Add settings link to plugin action links
 *
 * @param array $links Plugin action links.
 *
 * @return array
 */

add_filter(
	'plugin_action_links_' . plugin_basename( CWPAI_SETTINGS_PLUGIN_FILE ),
	function ( array $links ): array {
		$links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=cwpai-helper' ) ) . '">' . __( 'Settings', 'wp-cwpai-settings-page' ) . '</a>';
		$links[] = '<a href="https://codewp.ai/docs/wordpress-plugin/" target="_blank">' . __( 'Docs', 'wp-cwpai-settings-page' ) . '</a>';
		$links[] = '<a style="font-weight:bold;" href="https://app.codewp.ai/" target="_blank">' . __( 'App', 'wp-cwpai-settings-page' ) . '</a>';

		return $links;
	}
);
