<?php
/**
 * Plugin Name: Codenitive CAPTCHA Security
 * Plugin URI:  https://wordpress.org/plugins/codenitive-captcha
 * Description: Enhance your website’s security by integrating CAPTCHA verification into essential WordPress, WooCommerce, and Contact Form 7 forms.
 * Version:     1.1.0
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * Author:      CodeNitive
 * Author URI:  https://codenitive.com
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: codenitive-captcha
 *
 * @package codenitive-captcha
 */

if (!defined('ABSPATH')) {
    exit;
}

$codenitcaptcha_plugin_data = get_file_data(__FILE__, ['version' => 'Version'], 'plugin');

define('CODENITCAPTCHA_TEXT_DOMAIN', 'codenitive-captcha');
define('CODENITCAPTCHA_VERSION', $codenitcaptcha_plugin_data['version']);
define('CODENITCAPTCHA_PLUGIN_FILE_PATH', __FILE__);
define('CODENITCAPTCHA_PLUGIN_BASENAME', plugin_basename(CODENITCAPTCHA_PLUGIN_FILE_PATH));
define('CODENITCAPTCHA_PLUGIN_DIR_PATH', plugin_dir_path(CODENITCAPTCHA_PLUGIN_FILE_PATH));
define('CODENITCAPTCHA_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
define('CODENITCAPTCHA_PLUGIN_DIR_ASSETS_URL', CODENITCAPTCHA_PLUGIN_DIR_URL . 'assets/');

require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-csrf-secret.php';
register_activation_hook(__FILE__, ['codenitcaptcha\includes\CODENITCA_Captcha_CSRF', 'activate']);

require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-plugin.php';

add_action('plugins_loaded', static function () {
    \codenitcaptcha\includes\CODENITCA_Plugin::instance()->init();
}, 5);
