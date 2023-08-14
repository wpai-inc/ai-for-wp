<?php

/**
 * Plugin Name:       CodeWP Helper
 * Plugin URI:        https://github.com/wpai/codewp-helper
 * Description:       Connect with the CodeWP platform for instant troubleshooting and personalized code generation.
 *
 * Text Domain:       codewp-helper
 * Domain Path:       /languages
 *
 * Author:            WPAI, Inc.
 * Author URI:        https://codewp.ai
 *
 * Version:           0.0.1
 * Requires at least: 5.8
 * Tested up to:      6.1.1
 * Requires PHP:      7.1
 */

namespace CodeWPHelper;

if (!defined('ABSPATH')) {
	exit;
}

define('CWPAI_SETTINGS_PATH', \plugin_dir_path(__FILE__));
define('CWPAI_SETTINGS_URL', \plugins_url('/', __FILE__));
define('CWPAI_SETTINGS_PLUGIN_FILE', __FILE__);
define('CWPAI_SETTINGS_PLUGIN_DIR', __DIR__);
define('CWPAI_SETTINGS_VERSION', '0.0.1');

require 'includes/utils/register-ajax-method.php';
require 'includes/utils/enqueue-scripts-from-asset-file.php';
require 'includes/admin-page.php';
require 'includes/plugin-links.php';
require 'includes/ajax.php';

add_filter(
	'cwpai_settings_variables',
	function ($variables) {
		$variables['notice_hidden'] = get_option('cwpai-settings/notice_hidden', false);
		$variables['simple_form'] = get_option('cwpai-settings/simple_form', []);
		$variables['repeated_form'] = get_option('cwpai-settings/repeated_form', []);

		return $variables;
	}
);
