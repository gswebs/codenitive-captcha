<?php
/*
* Plugin Name: JMB CAPTCHA
* Plugin URI:  https://github.com/gswebs/jmb-captcha
* Description: Enhance your website's security by integrating CAPTCHA verification into key forms across both WooCommerce and WordPress. This plugin helps prevent spam and unauthorized access by adding CAPTCHA challenges to the WordPress Core and Woocommerce Forms
* Version: 0.1.0
* Requires at least: 6.5
* Requires PHP:      7.4
* Author:            Gurjit Singh
* Author URI:        https://github.com/gswebs
* License:           GPL v2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:       jmb-captcha-domain
* Domain Path:       /languages
*
* @package jmb-captcha
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');

// Plugin version (retrieved from plugin header)
define( 'JMB_CAPTCHA_VERSION', $plugin_data['version'] );

// Full path to the main plugin file
define( 'JMB_CAPTCHA_PLUGIN_FILE_PATH', __FILE__ );

// Plugin basename (used for hooks, filters, etc.)
define( 'JMB_CAPTCHA_PLUGIN_BASENAME', plugin_basename( JMB_CAPTCHA_PLUGIN_FILE_PATH ) );

// Absolute directory path of the plugin
define( 'JMB_CAPTCHA_PLUGIN_DIR_PATH', plugin_dir_path( JMB_CAPTCHA_PLUGIN_FILE_PATH ) );

// Load plugin settings and configuration files on plugins_loaded
function jmb_wc_captcha_settings_load() {
    require_once JMB_CAPTCHA_PLUGIN_DIR_PATH . 'includes/class-captcha-utils.php';
    require_once JMB_CAPTCHA_PLUGIN_DIR_PATH . 'includes/class-settings.php';
    require_once JMB_CAPTCHA_PLUGIN_DIR_PATH . 'includes/class-captcha-config.php';

    // Initialize plugin settings
    JMB_Captcha_Settings::init();
}
add_action( 'plugins_loaded', 'jmb_wc_captcha_settings_load' );

// Load and initialize CAPTCHA rendering for supported forms
function jmb_captcha_init() {
    require_once JMB_CAPTCHA_PLUGIN_DIR_PATH . 'includes/class-forms.php';

    // Initialize CAPTCHA rendering logic for forms
    new JMB_Captcha_Render();
}
add_action( 'init', 'jmb_captcha_init' );

// Load CAPTCHA for WordPress native comment forms
function jmb_captcha_load() {
    require_once JMB_CAPTCHA_PLUGIN_DIR_PATH . 'includes/class-comments-captcha.php';

    // Initialize CAPTCHA rendering for comment forms
    new JMB_Comments_Captcha_Render();
}
add_action( 'wp_loaded', 'jmb_captcha_load' );