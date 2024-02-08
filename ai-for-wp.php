<?php
/**
 * Plugin Name:       CodeWP helper
 * Plugin URI:        https://github.com/wpai/codewpai
 * Description:       Connect with the CodeWP platform for instant troubleshooting and personalized code generation.
 *
 * Text Domain:       ai-for-wp
 * Domain Path:       /languages
 *
 * Author:            WPAI, Inc.
 * Author URI:        https://codewp.ai
 *
 * Version:           0.2.2
 * Requires at least: 5.8.1
 * Tested up to:      6.4.1
 * Requires PHP:      7.4
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * License:           GPL v2 or later
 *
 * @package           CodeWP Helper
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use WpAi\CodeWpHelper\Main;

if (! class_exists(Main::class)) {
    $autoload_packages = __DIR__ . '/vendor/autoload_packages.php';
    if (is_file($autoload_packages)) {
        require_once $autoload_packages;
    }
}

$codewpai = new Main(__FILE__);
