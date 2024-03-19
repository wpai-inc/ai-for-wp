<?php

namespace WpAi\CodeWpHelper;

use WpAi\CodeWpHelper\Utils\CodewpaiConfig;
use WpAi\CodeWpHelper\Utils\CodewpaiFilesystem;

class AdminPage {

	private array $config;

	public function __construct() {
		$this->config = CodewpaiConfig::all();
		add_action( 'admin_menu', array( $this, 'adminMenu' ), 100 );
	}

	/**
     * Add the admin menu.
     *
	 * @return void
	 */
	public function adminMenu(): void {
		$hook_name = add_submenu_page(
			'options-general.php',
			__( 'CodeWP Helper', 'ai-for-wp' ),
			__( 'CodeWP Helper', 'ai-for-wp' ),
			'manage_options',
			'ai-for-wp',
			array( $this, 'adminPage' )
		);

		add_action( "load-$hook_name", array( $this, 'adminPageLoad' ) );
	}

	/**
     * The admin page.
     *
	 * @return void
	 */
	public function adminPage(): void {
		?>
		<noscript>
			<div class="no-js">
				<?php
				echo esc_html__(
					'Warning: This options panel will not work properly without JavaScript, please enable it.',
					'ai-for-wp'
				);
				?>
			</div>
		</noscript>
		<style>
			#codewpai-ui-loading {
				height: calc(100vh - 100px);
				display: flex;
				align-items: center;
				justify-content: center;
			}
		</style>
		<div id="codewpai-ui-loading"><?php echo esc_html__( 'Loading…', 'ai-for-wp' ); ?></div>
		<div id="codewpai-ui-settings"></div>
		<?php
	}

	/**
     * Load the admin page.
     *
	 * @return void
	 */
	public function adminPageLoad(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
		add_filter( 'update_footer', array( $this, 'updateFooter' ) );
		add_filter( 'admin_body_class', array( $this, 'bodyClass' ) );

		$screen = get_current_screen();

		$screen->add_help_tab(
			array(
				'id'      => 'codewpai_400_error_help_tab',
				'title'   => __( '400 Error?', 'ai-for-wp' ),
				'content' => '<p>'
							. sprintf(
							// translators: %s: will be replaced by current site URL.
								__(
									'Ensure that the site url, <strong>%s</strong>, is equal to the Project URL in CodeWP.',
									'ai-for-wp'
								),
								get_site_url()
							)
							. '</p>',
			)
		);
		$screen->add_help_tab(
			array(
				'id'      => 'codewpai_pro_user_help_tab',
				'title'   => __( 'CodeWP Pro User?', 'ai-for-wp' ),
				'content' => '<p>'
							. __(
								'Contact us and we\'ll walk you through the setup of this plugin.',
								'ai-for-wp'
							)
							. '</p>',
			)
		);
	}

	/**
     * Enqueue the scripts.
     *
	 * @return void
	 */
	public function enqueueScripts(): void {
		$this->enqueueScriptsFromAssetFile( 'settings' );
		$this->attachDataToSettings();
	}

	public function updateFooter(): string {
		return '<div style="float: right;">
Made with love 💚 by the <a href="https://codewp.ai/" target="_blank">CodeWP Team</a>
</div>';
	}

	/**
     * Add a class to the body.
     *
	 * @param string $classes
	 *
	 * @return string
	 */
	public function bodyClass( string $classes ): string {
		$classes .= ' codewpai';

		return $classes;
	}

	/**
     * Enqueue the scripts from the asset file.
     *
	 * @param string $name
	 *
	 * @return void
	 */
	public function enqueueScriptsFromAssetFile( string $name ): void {
		$script_asset_path = $this->config['plugin_dir'] . "/build/$name.asset.php";
		if ( file_exists( $script_asset_path ) ) {
			$script_asset        = include $script_asset_path;
			$script_dependencies = $script_asset['dependencies'] ?? array();

			if ( in_array( 'wp-media-utils', $script_dependencies, true ) ) {
				wp_enqueue_media();
			}

			if ( in_array( 'wp-react-refresh-runtime', $script_asset['dependencies'], true )
				&& ! constant( 'SCRIPT_DEBUG' )
			) {
				wp_die( esc_html( 'SCRIPT_DEBUG should be true. You use `hot` mode, it requires `wp-react-refresh-runtime` which available only when SCRIPT_DEBUG is enabled.' ) );
			}

			wp_enqueue_script(
				"codewpai-$name",
				plugins_url( "build/$name.js", $this->config['plugin_file'] ),
				$script_dependencies,
				$script_asset['version'],
				true
			);

			$style_dependencies = array();

			if ( in_array( 'wp-components', $script_dependencies, true ) ) {
				$style_dependencies[] = 'wp-components';
			}

			wp_enqueue_style(
				"codewpai-$name",
				plugins_url( "build/$name.css", $this->config['plugin_file'] ),
				$style_dependencies,
				$script_asset['version'],
			);
		}
	}

	/**
     * Get the settings variables.
     *
	 * @param array $variables
	 *
	 * @return array
	 */
	public function settingsVariables( array $variables ): array {
		$current_user = wp_get_current_user();

		$api_host = Settings::api_host();

		$variables['nonce']           = wp_create_nonce( Main::nonce() );
		$variables['codewp_server']   = $api_host;
		$variables['user']['name']    = $current_user->display_name;
		$variables['project']         = Settings::getSettingsFormData();
		$variables['notice_visible']  = get_option( 'codewpai_notice_visible', 1 );
		$variables['playground_mode'] = $this->config['in_playground'];
		$variables['plugin_url']      = plugins_url( '', $this->config['plugin_file'] );

		return $variables;
	}

	/**
	 * Attach data to the settings script.
	 *
	 * @return void
	 */
	public function attachDataToSettings(): void {
		$variables = $this->settingsVariables( array() );
		wp_localize_script(
			'codewpai-settings',
			'CODEWPAI_SETTINGS',
			$variables
		);
	}
}
