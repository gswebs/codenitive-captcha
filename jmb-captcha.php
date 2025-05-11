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

define( 'JMB_CAPTCHA_VERSION', $plugin_data['version'] );

// Initialize the plugin
function jmb_wc_captcha_settings_load() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-captcha-utils.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-settings.php';
    JMB_Captcha_Settings::init();

    require_once plugin_dir_path(__FILE__) . 'includes/class-captcha-config.php';
}
add_action('plugins_loaded', 'jmb_wc_captcha_settings_load');

function jmb_captcha_init() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-forms.php';
    new JMB_Captcha_Render(); 

}
add_action('init', 'jmb_captcha_init');

function jmb_captcha_load(){
    require_once plugin_dir_path(__FILE__) . 'includes/class-comments-captcha.php';
    new JMB_Comments_Captcha_Render(); 
}
add_action('wp_loaded', 'jmb_captcha_load');
