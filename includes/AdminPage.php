<?php

namespace WpAi\CodeWpHelper;

class AdminPage
{
    private $plugin_dir;
    private $plugin_file;
    private $plugin_url;

    public function __construct($plugin_dir, $plugin_file)
    {
        $this->plugin_dir = $plugin_dir;
        $this->plugin_file = $plugin_file;
        $this->plugin_url = plugins_url('', $this->plugin_file);
        add_action('admin_menu', [$this, 'adminMenu'], 100);
    }

    public function adminMenu()
    {
        $hook_name = add_submenu_page(
            'options-general.php',
            __('CodeWP Helper', 'ai-for-wp'),
            __('CodeWP Helper', 'ai-for-wp'),
            'manage_options',
            'ai-for-wp',
            [$this, 'adminPage']
        );

        add_action("load-{$hook_name}", [$this, 'adminPageLoad']);
    }

    public function adminPage()
    {
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
        <div id="codewpai-ui-loading"><?php echo esc_html__('Loading…', 'ai-for-wp'); ?></div>
        <div id="codewpai-ui-settings"></div>
        <?php
    }

    public function adminPageLoad()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_filter('update_footer', [$this, 'updateFooter']);
        add_filter('admin_body_class', [$this, 'bodyClass']);

        $screen = get_current_screen();

        $screen->add_help_tab(
            array(
                'id'      => 'codewpai_400_error_help_tab',
                'title'   => __('400 Error?', 'ai-for-wp'),
                // translators: %s: will be replaced by current site URL
                'content' => '<p>'
                             . sprintf(
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
                'title'   => __('CodeWP Pro User?', 'ai-for-wp'),
                'content' => '<p>'
                             . __(
                                 'Contact us and we\'ll walk you through the setup of this plugin.',
                                 'ai-for-wp'
                             )
                             . '</p>',
            )
        );
    }

    public function enqueueScripts()
    {
        $this->enqueueScriptsFromAssetFile('settings');
        $this->attachDataToSettings();
    }

    public function updateFooter(): string
    {
        return '<div style="float: right;">
Made with love 💚 by the <a href="https://codewp.ai/" target="_blank">CodeWP Team</a>
</div>';
    }

    public function bodyClass(string $classes)
    {
        $classes .= ' codewpai';

        return $classes;
    }

    public function enqueueScriptsFromAssetFile(string $name)
    {
        $script_asset_path = $this->plugin_dir . "/build/$name.asset.php";
        if (file_exists($script_asset_path)) {
            $script_asset        = include $script_asset_path;
            $script_dependencies = $script_asset['dependencies'] ?? array();

            if (in_array('wp-media-utils', $script_dependencies, true)) {
                wp_enqueue_media();
            }

            if (            in_array('wp-react-refresh-runtime', $script_asset['dependencies'], true)
                && ! constant('SCRIPT_DEBUG')
            ) {
                wp_die(esc_html('SCRIPT_DEBUG should be true. You use `hot` mode, it requires `wp-react-refresh-runtime` which available only when SCRIPT_DEBUG is enabled.'));
            }

            wp_enqueue_script(
                "codewpai-$name",
                plugins_url("build/$name.js", $this->plugin_file),
                $script_dependencies,
                $script_asset['version'],
                true
            );

            $style_dependencies = array();

            if (in_array('wp-components', $script_dependencies, true)) {
                $style_dependencies[] = 'wp-components';
            }

            wp_enqueue_style(
                "codewpai-$name",
                plugins_url("build/$name.css", $this->plugin_file),
                $style_dependencies,
                $script_asset['version'],
                'all'
            );
        }
    }

    public function settingsVariables(array $variables): array
    {
        $current_user = wp_get_current_user();

        $api_host = defined('CODEWPAI_API_HOST') ? CODEWPAI_API_HOST : Main::API_HOST;
        $api_host = rtrim($api_host, '/');

        $variables['nonce']          = wp_create_nonce(Main::nonce());
        $variables['codewp_server']  = $api_host;
        $variables['user']['name']   = $current_user->display_name;
        $variables['project']        = Settings::getSettingsFormData();
        $variables['notice_visible'] = get_option('codewpai_notice_visible', 1);
        $variables['playground_mode'] = defined('CWP_PLAYGROUND') && CWP_PLAYGROUND === true;
        $variables['plugin_url'] = plugins_url('', $this->plugin_file);

        return $variables;
    }

    /**
     * @return void
     */
    public function attachDataToSettings(): void
    {
        $variables = $this->settingsVariables([]);
        wp_localize_script(
            'codewpai-settings',
            'CODEWPAI_SETTINGS',
            $variables
        );
    }
}
