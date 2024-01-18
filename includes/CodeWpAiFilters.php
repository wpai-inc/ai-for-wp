<?php

namespace CodeWpAi\CodewpHelper;

class CodeWpAiFilters
{
    public function __construct()
    {
        add_filter(
            'plugin_action_links_'.plugin_basename(CODEWPAI_HELPER_PLUGIN_FILE),
            [$this, 'pluginLinks']
        );
    }
    public function pluginLinks(array $links): array
    {
        $links[] = '<a href="'.esc_url(admin_url('options-general.php?page=codewpai')).'">'
                   .__('Settings', 'codewpai')
                   .'</a>';
        $links[] = '<a href="https://codewp.ai/plugin-docs" target="_blank">'
                   .__('Docs', 'codewpai')
                   .'</a>';
        $links[] = '<a style="font-weight:bold;" href="https://codewp.ai/dashboard" target="_blank">'
                   .__('App', 'codewpai')
                   .'</a>';

        return $links;
    }
}
