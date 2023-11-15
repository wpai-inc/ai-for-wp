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
 *
 * @package           CodeWP Helper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CWPAI_SETTINGS_PATH', \plugin_dir_path( __FILE__ ) );
define( 'CWPAI_SETTINGS_URL', \plugins_url( '/', __FILE__ ) );
define( 'CWPAI_SETTINGS_PLUGIN_FILE', __FILE__ );
define( 'CWPAI_SETTINGS_PLUGIN_DIR', __DIR__ );
define( 'CWPAI_SETTINGS_VERSION', '0.0.1' );
define( 'NONCE_ACTION', 'wp-cwpai-settings-page' );
if ( ! defined( 'CWPAI_API_SERVER' ) ) {
	define( 'CWPAI_API_SERVER', 'https://codewp.ai' );
}


require 'includes/utils/register-ajax-method.php';
require 'includes/utils/api-key-functions.php';
require 'includes/utils/enqueue-scripts-from-asset-file.php';
require 'includes/admin-page.php';
require 'includes/plugin-links.php';
require 'includes/ajax.php';
require 'includes/cron.php';
require 'includes/filters.php';

add_filter(
	'cwpai_settings_variables',
	function ( $variables ) {
		$current_user = wp_get_current_user();

		$variables['codewp_server']  = CWPAI_API_SERVER;
		$variables['user']['name']   = $current_user->display_name;
		$variables['project']        = cwpai_get_api_key_form_data();
		$variables['notice_visible'] = get_option( 'cwpai-settings/notice_visible', 1 );

		return $variables;
	}
);
