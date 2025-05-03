<?php
/*
* Plugin Name: JMB CAPTCHA
* Description: Adds CAPTCHA to WooCommerce and WordPress.
* Version: 0.1.0
* Author: Gurjit Singh
* Text Domain: jmb-captcha
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');

define( 'JMB_CAPTCHA_VERSION', $plugin_data['version'] );

// Initialize the plugin
function jmb_wc_captcha_settings_load() {
    require_once plugin_dir_path(__FILE__) . 'includes/captcha-utils.php';
    require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
    JMB_Captcha_Settings::init();

    require_once plugin_dir_path(__FILE__) . 'includes/captcha-config.php';
}
add_action('plugins_loaded', 'jmb_wc_captcha_settings_load');

function jmb_captcha_init() {
    require_once plugin_dir_path(__FILE__) . 'includes/forms.php';
    new JMB_Captcha_Render(); 

}
add_action('init', 'jmb_captcha_init');

function jmb_captcha_load(){
    require_once plugin_dir_path(__FILE__) . 'includes/comments-captcha.php';
    new JMB_Comments_Captcha_Render(); 
}
add_action('wp_loaded', 'jmb_captcha_load');
