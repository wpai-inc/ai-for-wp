<?php

namespace WpAi\CodeWpHelper;

class Snippets
{
    private mixed $plugin_dir;

    public function __construct($plugin_dir)
    {
        $this->plugin_dir = $plugin_dir;

        do_action('codewpai_snippets', [ $this ]);

        add_action('wp_ajax_codewpai_get_snippets', [ $this, 'getSnippets' ]);
        add_action('wp_ajax_codewpai_enable_snippet', [ $this, 'enableSnippet' ]);

        $this->includeEnabledSnippets();
    }

    public function availableSnippets(): array
    {
        return glob($this->plugin_dir . 'snippets/*.php');
    }

    public function enabledSnippets(): array
    {
        $enabled_snippets = get_option('codewpai_enabled_snippets');
        if (! $enabled_snippets) {
            return [];
        }

        return $enabled_snippets;
    }

    public function allSnippets(): array
    {
        $all_snippets = $this->availableSnippets();
        $enabled_snippets = $this->enabledSnippets();
        $all_snippets = array_map(function ($snippet) use ($enabled_snippets) {
            return [
                'name'    => basename($snippet),
                'enabled' => $enabled_snippets[ $snippet ] ?? false,
                'code'    => file_get_contents($snippet),
            ];
        }, $all_snippets);

        return $all_snippets;
    }

    public function enableSnippet(string $snippet): void
    {
        $enabled_snippets             = $this->enabledSnippets();
        $enabled_snippets[ $snippet ] = true;

        error_log('Enabled snippets: ' . print_r($enabled_snippets, true) . "\n");


        // update options codewpai_enabled_snippets
        update_option('codewpai_enabled_snippets', $enabled_snippets);
    }

    public function disableSnippet(string $snippet): void
    {
        $enabled_snippets             = $this->enabledSnippets();
        $enabled_snippets[ $snippet ] = false;
        // update options codewpai_enabled_snippets
        update_option('codewpai_enabled_snippets', $enabled_snippets);
    }

    public function enableAllSnippets(): void
    {
        $all_snippets = $this->availableSnippets();
        foreach ($all_snippets as $snippet) {
            $this->enableSnippet($snippet);
        }
    }

    private function includeEnabledSnippets(): void
    {
        $enabled_snippets = $this->enabledSnippets();
        foreach ($enabled_snippets as $snippet => $enabled) {
            if ($enabled) {
                include_once $snippet;
            }
        }
    }

    public function wpaiSnippetsShutdown()
    {
        // if the snippet throws an error we need to catch it here and disable it
        // otherwise the site will be broken
        $error = error_get_last();
        if ($error) {
            error_log(print_r($error, true));
//            $this->disableSnippet($error['file']);
        }
    }

    public function addStringSnippet($snippet_name, $snippet_code)
    {

        $snippet_code = json_decode($snippet_code, true);

        $snippet_path = $this->plugin_dir . 'snippets/' . $snippet_name;
        error_log('New snippet added: ' . $snippet_path . "\n");
        file_put_contents($snippet_path, $snippet_code['file_content']);
        $this->enableSnippet($snippet_path);
    }


    public function getSnippets()
    {
        $snippets = $this->allSnippets();

        wp_send_json($snippets);
    }
}
