<?php

namespace WpAi\CodeWpHelper;

class Filters
{
    private $plugin_file;

    public function __construct($plugin_file)
    {
        $this->plugin_file = $plugin_file;
        add_filter(
            'plugin_action_links_' . plugin_basename($this->plugin_file),
            [$this, 'pluginLinks']
        );
    }
    public function pluginLinks(array $links): array
    {
        $links[] = '<a href="' . esc_url(admin_url('options-general.php?page=ai-for-wp')) . '">'
                   . __('Settings', 'ai-for-wp')
                   . '</a>';
        $links[] = '<a href="https://codewp.ai/plugin-docs" target="_blank">'
                   . __('Docs', 'ai-for-wp')
                   . '</a>';
        $links[] = '<a style="font-weight:bold;" href="https://app.codewp.ai/dashboard" target="_blank">'
                   . __('App', 'ai-for-wp')
                   . '</a>';

        return $links;
    }
}
