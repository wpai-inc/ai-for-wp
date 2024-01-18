<?php

namespace WpAi\CodeWpHelper;

class Filters
{
    public function __construct()
    {
        add_filter(
            'plugin_action_links_'.Main::TEXT_DOMAIN,
            [$this, 'pluginLinks']
        );
    }
    public function pluginLinks(array $links): array
    {
        $links[] = '<a href="'.esc_url(admin_url('options-general.php?page=codewpai')).'">'
                   .__('Settings', Main::TEXT_DOMAIN)
                   .'</a>';
        $links[] = '<a href="https://codewp.ai/plugin-docs" target="_blank">'
                   .__('Docs', Main::TEXT_DOMAIN)
                   .'</a>';
        $links[] = '<a style="font-weight:bold;" href="https://codewp.ai/dashboard" target="_blank">'
                   .__('App', Main::TEXT_DOMAIN)
                   .'</a>';

        return $links;
    }
}
