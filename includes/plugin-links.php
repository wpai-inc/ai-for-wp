<?php
/**
 * Adding 'settings' link to plugin page.
 *
 * @package CodeWPHelper
 */

add_filter( 'plugin_action_links_' . plugin_basename( CWPAI_SETTINGS_PLUGIN_FILE ), 'cwpai_plugin_action_links' );

/**
 * Add settings link to plugin action links
 *
 * @param array $links Plugin action links.
 *
 * @return array
 */
function cwpai_plugin_action_links( array $links ): array {
	$links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=cwpai-helper' ) ) . '">' . __( 'Settings', 'wp-cwpai-settings-page' ) . '</a>';
	$links[] = '<a href="https://codewp.ai/docs/wordpress-plugin/" target="_blank">' . __( 'Docs', 'wp-cwpai-settings-page' ) . '</a>';
	$links[] = '<a style="font-weight:bold;" href="https://app.codewp.ai/" target="_blank">' . __( 'App', 'wp-cwpai-settings-page' ) . '</a>';

	return $links;
}
