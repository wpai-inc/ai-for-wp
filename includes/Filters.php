<?php

namespace WpaiInc\CodewpHelper;

class Filters
{
    public function __construct()
    {
        add_filter(
            'plugin_action_links_'.plugin_basename(CWPAI_HELPER_PLUGIN_FILE),
            [$this, 'pluginLinks']
        );
    }
    public function pluginLinks(array $links): array
    {
        $links[] = '<a href="'.esc_url(admin_url('options-general.php?page=cwpai-helper')).'">'
                   .__('Settings', 'cwpai-helper')
                   .'</a>';
        $links[] = '<a href="https://codewp.ai/plugin-docs" target="_blank">'
                   .__('Docs', 'cwpai-helper')
                   .'</a>';
        $links[] = '<a style="font-weight:bold;" href="https://codewp.ai/dashboard" target="_blank">'
                   .__('App', 'cwpai-helper')
                   .'</a>';

        return $links;
    }
}
