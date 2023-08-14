<?php

namespace CodeWPHelper;

//add 'settings' link to plugin page
add_filter('plugin_action_links_' . plugin_basename(CWPAI_SETTINGS_PLUGIN_FILE), __NAMESPACE__ . '\\plugin_action_links');

function plugin_action_links($links)
{
    $links[] = '<a href="' . esc_url(admin_url('options-general.php?page=cwpai-helper')) . '">' . __('Settings', 'wp-cwpai-settings-page') . '</a>';
    //docs
    $links[] = '<a href="https://codewp.ai/docs/wordpress-plugin/" target="_blank">' . __('Docs', 'wp-cwpai-settings-page') . '</a>';
    //app
    $links[] = '<a style="font-weight:bold;" href="https://app.codewp.ai/" target="_blank">' . __('App', 'wp-cwpai-settings-page') . '</a>';

    return $links;
}
