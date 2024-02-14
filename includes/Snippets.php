<?php

namespace WpAi\CodeWpHelper;

class Snippets
{
    private mixed $plugin_dir;

    public function __construct($plugin_dir)
    {
        $this->plugin_dir = $plugin_dir;

        do_action('codewpai_snippets', [$this]);

        add_action('wp_ajax_codewpai_get_snippets', [$this, 'getSnippets']);
        add_action('wp_ajax_codewpai_enable_snippet', [$this, 'ajaxEnableSnippet']);
        add_action('wp_ajax_codewpai_disable_snippet', [$this, 'ajaxDisableSnippet']);

        add_action('plugins_loaded', [$this, 'includeEnabledSnippets'], PHP_INT_MAX);
    }

    public function availableSnippets(): array
    {
        // GLOB_BRACE is not available on @wp-playground
        $phpFiles = glob($this->plugin_dir . 'snippets/*.php');
        $cssFiles = glob($this->plugin_dir . 'snippets/*.css');
        $jsFiles = glob($this->plugin_dir . 'snippets/*.js');

        return array_merge($phpFiles, $cssFiles, $jsFiles);
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
            $snippet_filename = basename($snippet);
            return [
                'name' => $snippet_filename,
                'enabled' => isset($enabled_snippets[$snippet_filename]) && $enabled_snippets[$snippet_filename] === true,
                'code' => file_get_contents($snippet),
                'language' => pathinfo($snippet, PATHINFO_EXTENSION),
            ];
        }, $all_snippets);

        return $all_snippets;
    }

    public function ajaxEnableSnippet(): void
    {
        $snippet = $_GET['snippet_name'];
        if (!empty($_GET['snippet_name'])) {
            $this->enableSnippet($snippet);
            $all_snippets = $this->allSnippets();
            wp_send_json($all_snippets);
        }
        wp_send_json(['error' => 'No snippet name provided']);
    }

    public function enableSnippet(string $snippet): void
    {
        $enabled_snippets = $this->enabledSnippets();
        if (! file_exists($this->plugin_dir . 'snippets/' . $snippet)) {
            return;
        }
        $enabled_snippets[$snippet] = true;
        update_option('codewpai_enabled_snippets', $enabled_snippets);
    }

    public function ajaxDisableSnippet(): void
    {
        $snippet = $_GET['snippet_name'];
        if (!empty($_GET['snippet_name'])) {
            $this->disableSnippet($snippet);
            $all_snippets = $this->allSnippets();
            wp_send_json($all_snippets);
        }
        wp_send_json(['error' => 'No snippet name provided']);
    }

    public function disableSnippet(string $snippet): void
    {
        $enabled_snippets = $this->enabledSnippets();
        if (! file_exists($this->plugin_dir . 'snippets/' . $snippet)) {
            return;
        }
        $enabled_snippets[$snippet] = false;
        update_option('codewpai_enabled_snippets', $enabled_snippets);
    }

    public function enableAllSnippets(): void
    {
        $all_snippets = $this->availableSnippets();
        foreach ($all_snippets as $snippet) {
            $this->enableSnippet($snippet);
        }
    }

    public function includeEnabledSnippets(): void
    {
        $enabled_snippets = $this->enabledSnippets();
        foreach ($enabled_snippets as $snippet => $enabled) {
            $snippet_file = $this->plugin_dir . 'snippets/' . $snippet;
            if (! empty($enabled) && file_exists($snippet_file)) {
                $file_type = pathinfo($snippet_file, PATHINFO_EXTENSION);
                if ($file_type === 'php') {
                    include_once $snippet_file;
                } elseif ($file_type === 'js') {
                    add_action('wp_footer', function () use ($snippet_file) {
                        echo '<script>' . file_get_contents($snippet_file) . '</script>';
                    });
                } elseif ($file_type === 'css') {
                    add_action('wp_head', function () use ($snippet_file) {
                        echo '<style id="cwp-style">' . file_get_contents($snippet_file) . '</style>';
                    });
                }
            }
        }
    }

    public function addStringSnippet($snippet_name, $snippet_code)
    {
        $snippet_code = base64_decode($snippet_code);
        $snippet_path = $this->plugin_dir . 'snippets/' . $snippet_name;
        file_put_contents($snippet_path, $snippet_code);
        $this->enableSnippet($snippet_name);
    }

    public function getSnippets()
    {
        $snippets = $this->allSnippets();

        wp_send_json($snippets);
    }
}
