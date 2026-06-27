<?php
namespace codenitcaptcha\includes;

if (!defined('ABSPATH')) {
    exit;
}

class CODENITCA_Plugin {

    private static $instance = null;

    public static function instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {}

    public function init(): void {
        $this->load_dependencies();
        $this->register_hooks();
    }

    private function load_dependencies(): void {
        require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-csrf-secret.php';
        require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-settings.php';
        require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-captcha-config.php';
        require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-assets.php';
        require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-captcha-verifier.php';
        require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-recaptcha-verifier.php';
        require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-wordpress-forms.php';
        require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-woocommerce-forms.php';
        require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-comments-captcha.php';
    }

    private function register_hooks(): void {
        \codenitcaptcha\includes\settings\CODENITCA_Captcha_Settings::init();

        new CODENITCA_WordPress_Forms_Integration();
        new CODENITCA_WooCommerce_Forms_Integration();
        new CODENITCA_Comments_Captcha_Render();

        \add_action('plugins_loaded', [$this, 'load_optional_integrations'], 20);
    }

    public function load_optional_integrations(): void {
        if (class_exists('WPCF7')) {
            require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-cf7-captcha.php';
            CODENITCA_Captcha_CF7_Render::get_instance();
        }
    }
}
