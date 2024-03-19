<?php

namespace WpAi\CodeWpHelper;

use WpAi\CodeWpHelper\Utils\CodewpaiConfig;
use WpAi\CodeWpHelper\Utils\CodewpaiFilesystem;

class Filters {

	private array $config;

	public function __construct() {
		$this->config = CodewpaiConfig::all();
		add_filter(
			'plugin_action_links_' . plugin_basename( $this->config['plugin_file'] ),
			array( $this, 'pluginLinks' )
		);
	}

	/**
	 * Add the plugin links.
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function pluginLinks( array $links ): array {
		$links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=ai-for-wp' ) ) . '">'
					. __( 'Settings', 'ai-for-wp' )
					. '</a>';
		$links[] = '<a href="https://codewp.ai/plugin-docs" target="_blank">'
					. __( 'Docs', 'ai-for-wp' )
					. '</a>';
		$links[] = '<a style="font-weight:bold;" href="https://app.codewp.ai/dashboard" target="_blank">'
					. __( 'App', 'ai-for-wp' )
					. '</a>';

		return $links;
	}
}
