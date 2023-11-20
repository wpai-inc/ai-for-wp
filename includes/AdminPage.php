<?php

namespace WpaiInc\CodewpHelper;

class AdminPage
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'adminMenu'], 100);
    }

    public function adminMenu()
    {
        $hook_name = add_submenu_page(
            'options-general.php',
            __('CodeWP Helper', 'codewp-helper'),
            __('CodeWP Helper', 'codewp-helper'),
            'manage_options',
            'cwpai-helper',
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
                    'codewp-helper'
                );
                ?>
            </div>
        </noscript>
        <style>
            #cwpai-ui-loading {
                height: calc(100vh - 100px);
                display: flex;
                align-items: center;
                justify-content: center;
            }
        </style>
        <div id="cwpai-ui-loading"><?php echo esc_html__('Loading…', 'codewp-helper'); ?></div>
        <div id="cwpai-ui-settings"></div>
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
                'id'      => 'cwpai_400_error_help_tab',
                'title'   => __('400 Error?', 'codewp-helper'),
                /* translators: %s: will be replaced by current site URL */
                'content' => '<p>'
                             .sprintf(
                                 __(
                                     'Ensure that the site url, <strong>%s</strong>, is equal to the Project URL in CodeWP.',
                                     'codewp-helper'
                                 ),
                                 get_site_url()
                             )
                             .'</p>',
            )
        );
        $screen->add_help_tab(
            array(
                'id'      => 'cwpai_pro_user_help_tab',
                'title'   => __('CodeWP Pro User?', 'codewp-helper'),
                'content' => '<p>'
                             .__(
                                 'Contact us and we\'ll walk you through the setup of this plugin.',
                                 'codewp-helper'
                             )
                             .'</p>',
            )
        );
    }

    public function enqueueScripts()
    {
        $this->enqueueScriptsFromAssetFile('settings', CWPAI_HELPER_PLUGIN_FILE);
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
        $classes .= ' cwpai-helper';

        return $classes;
    }

    public function enqueueScriptsFromAssetFile(string $name, string $plugin_file)
    {
        $script_asset_path = dirname($plugin_file)."/build/$name.asset.php";
        if (file_exists($script_asset_path)) {
            $script_asset        = include $script_asset_path;
            $script_dependencies = $script_asset['dependencies'] ?? array();

            if (in_array('wp-media-utils', $script_dependencies, true)) {
                wp_enqueue_media();
            }

            if (in_array('wp-react-refresh-runtime', $script_asset['dependencies'], true)
                && ! constant('SCRIPT_DEBUG')
            ) {
                wp_die(esc_html('SCRIPT_DEBUG should be true. You use `hot` mode, it requires `wp-react-refresh-runtime` which available only when SCRIPT_DEBUG is enabled.'));
            }

            wp_enqueue_script(
                "cwpai-helper-$name",
                plugins_url("build/$name.js", $plugin_file),
                $script_dependencies,
                $script_asset['version'],
                true
            );

            $style_dependencies = array();

            if (in_array('wp-components', $script_dependencies, true)) {
                $style_dependencies[] = 'wp-components';
            }

            wp_enqueue_style(
                "cwpai-helper-$name",
                plugins_url("build/$name.css", $plugin_file),
                $style_dependencies,
                $script_asset['version'],
                'all'
            );
        }
    }

    public function settingsVariables(array $variables): array
    {
        $current_user = wp_get_current_user();

        $variables['nonce']          = wp_create_nonce(NONCE_ACTION);
        $variables['codewp_server']  = CWPAI_API_SERVER;
        $variables['user']['name']   = $current_user->display_name;
        $variables['project']        = Settings::getSettingsFormData();
        $variables['notice_visible'] = get_option('cwpai-helper/notice_visible', 1);

        return $variables;
    }

    /**
     * @return void
     */
    public function attachDataToSettings(): void
    {
        $variables = $this->settingsVariables([]);
        wp_localize_script(
            'cwpai-helper-settings',
            'CWPAI_SETTINGS',
            $variables
        );
    }
}
