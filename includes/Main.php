<?php

namespace WpAi\CodeWpHelper;

class Main
{
    public const VERSION = '0.2.5';

    public const TEXT_DOMAIN = 'ai-for-wp';

    public const API_HOST = 'https://app.codewp.ai';

    private $plugin_file;

    private $plugin_dir;

    public $snippets;

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
        delete_option('codewpai_api_token');
        delete_option('codewpai_notice_visible');
    }

    public function bootstrap()
    {
        new ErrorHandler();

        new Filters($this->plugin_file);
        new Ajax();
        new AdminPage($this->plugin_dir, $this->plugin_file);
        new Cron();

        // This object can be used to manipulate snippets (playground functionality)
        $this->snippets = new Snippets($this->plugin_dir);

        new Logs();
    }
}
