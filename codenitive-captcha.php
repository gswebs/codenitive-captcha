<?php
/*
* Plugin Name: Codenitive Captcha
* Plugin URI:  https://wordpress.org/codenitive-captcha
* Description: Enhance your website’s security by integrating CAPTCHA verification into essential WordPress and WooCommerce forms. This plugin helps prevent spam, bots, and unauthorized access by adding CAPTCHA challenges to key areas such as login, registration, password reset, checkout, and more. With built-in support for Google reCAPTCHA (v2), this plugin provides a seamless way to protect both the WordPress core and WooCommerce without disrupting the user experience.
* Version: 1.0.0
* Requires at least: 5.6
* Requires PHP:      7.4
* Author:            Codenitive
* Author URI:        https://codenitive.com
* License:           GPL v2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:       codenitive-captcha
* Domain Path:       /languages
*
* @package codenitive-captcha
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');

define( 'JMB_CAPTCHA_TEXT_DOMAIN', 'codenitive-captcha-td' );

// Plugin version (retrieved from plugin header)
define( 'JMB_CAPTCHA_VERSION', $plugin_data['version'] );

// Full path to the main plugin file
define( 'JMB_CAPTCHA_PLUGIN_FILE_PATH', __FILE__ );

// Plugin basename (used for hooks, filters, etc.)
define( 'JMB_CAPTCHA_PLUGIN_BASENAME', plugin_basename( JMB_CAPTCHA_PLUGIN_FILE_PATH ) );

// Absolute directory path of the plugin
define( 'JMB_CAPTCHA_PLUGIN_DIR_PATH', plugin_dir_path( JMB_CAPTCHA_PLUGIN_FILE_PATH ) );

require_once JMB_CAPTCHA_PLUGIN_DIR_PATH . 'includes/class-settings.php';
require_once JMB_CAPTCHA_PLUGIN_DIR_PATH . 'includes/class-captcha-config.php';
// Load plugin settings and configuration files
// Initialize plugin settings
JMB_Captcha_Settings::init();

// Load and initialize CAPTCHA rendering for supported forms
require_once JMB_CAPTCHA_PLUGIN_DIR_PATH . 'includes/class-forms.php';
// Initialize CAPTCHA rendering logic for forms
new JMB_Captcha_Render();

// Load CAPTCHA for WordPress native comment forms
require_once JMB_CAPTCHA_PLUGIN_DIR_PATH . 'includes/class-comments-captcha.php';
// Initialize CAPTCHA rendering for comment forms
new JMB_Comments_Captcha_Render();
