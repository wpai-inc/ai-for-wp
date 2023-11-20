<?php
/**
 * Plugin Name:       CodeWP Helper
 * Plugin URI:        https://github.com/wpai/codewp-helper
 * Description:       Connect with the CodeWP platform for instant troubleshooting and personalized code generation.
 *
 * Text Domain:       cwpai-helper
 * Domain Path:       /languages
 *
 * Author:            WPAI, Inc.
 * Author URI:        https://codewp.ai
 *
 * Version:           0.1.0
 * Requires at least: 5.8.1
 * Tested up to:      6.4.1
 * Requires PHP:      7.4
 *
 * @package           CodeWP Helper
 */

use WpaiInc\CodewpHelper\CodewpHelper;

if (! class_exists(CodewpHelper::class)) {
    $autoload_packages = __DIR__ . '/vendor/autoload_packages.php';
    if (is_file($autoload_packages)) {
        require_once $autoload_packages;
    }
}

new CodewpHelper();
