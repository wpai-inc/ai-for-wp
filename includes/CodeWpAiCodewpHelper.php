<?php

namespace CodeWpAi\CodewpHelper;

class CodeWpAiCodewpHelper
{

    protected $version = '0.1.0';
    
    public function __construct()
    {
        $this->initConstants();
        register_activation_hook(CODEWPAI_HELPER_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(CODEWPAI_HELPER_PLUGIN_FILE, [$this, 'deactivate']);
        $this->bootstrap();
    }

    public function initConstants()
    {
        define('CODEWPAI_HELPER_PATH', \plugin_dir_path(__DIR__));
        define('CODEWPAI_HELPER_PLUGIN_FILE', CODEWPAI_HELPER_PATH . 'codewpai.php');
        define('CODEWPAI_NONCE_ACTION', 'codewpai');
        if (! defined('CODEWPAI_API_SERVER')) {
            define('CODEWPAI_API_SERVER', 'https://app.codewp.ai');
        }
    }

    public function activate()
    {
        // do something on plugin activation
    }

    public function deactivate()
    {
        // do something on plugin deactivation
        delete_option('codewpai/api-token');
        delete_option('codewpai/notice_visible');
    }

    public function bootstrap()
    {
        // do something on plugin bootstrap
        new CodeWpAiFilters();
        new CodeWpAiAjax();
        new CodeWpAiAdminPage();
        new CodeWpAiCron();
    }
}
