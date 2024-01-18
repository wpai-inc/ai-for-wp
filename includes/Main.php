<?php

namespace WpAi\CodeWpHelper;

class Main
{
    const VERSION = '0.1.0';
    const TEXT_DOMAIN = 'codewpai';
    const API_HOST = 'https://app.codewp.ai';
    
    private $plugin_file;
    private $plugin_dir;

    public function __construct($plugin_file)
    {
        $this->plugin_file = $plugin_file;
        $this->plugin_dir = plugin_dir_path($this->plugin_file);
        register_activation_hook($this->plugin_file, [$this, 'activate']);
        register_deactivation_hook($this->plugin_file, [$this, 'deactivate']);
        $this->bootstrap();
    }

    public static function nonce()
    {
        return self::TEXT_DOMAIN;
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
        new Filters();
        new Ajax();
        new AdminPage($this->plugin_dir);
        new Cron();
    }
}
