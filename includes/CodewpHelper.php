<?php

namespace WpaiInc\CodewpHelper;

class CodewpHelper
{

    protected $version = '0.1.0';
    
    public function __construct()
    {
        $this->initConstants();
        register_activation_hook(CWPAI_HELPER_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(CWPAI_HELPER_PLUGIN_FILE, [$this, 'deactivate']);
        $this->bootstrap();
    }

    public function initConstants()
    {
        define('CWPAI_HELPER_PATH', \plugin_dir_path(__DIR__));
        define('CWPAI_HELPER_PLUGIN_FILE', CWPAI_HELPER_PATH . 'codewp-helper.php');
        define('NONCE_ACTION', 'cwpai-helper');
        if (! defined('CWPAI_API_SERVER')) {
            define('CWPAI_API_SERVER', 'https://codewp.ai');
        }
    }

    public function activate()
    {
        // do something on plugin activation
    }

    public function deactivate()
    {
        // do something on plugin deactivation
        delete_option('cwpai-helper/api-token');
        delete_option('cwpai-helper/notice_visible');
    }

    public function bootstrap()
    {
        // do something on plugin bootstrap
        new Filters();
        new Ajax();
        new AdminPage();
        new Cron();
    }
}
