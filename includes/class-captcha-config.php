<?php
namespace codenitcaptcha\includes\config;

if (!defined('ABSPATH')) {
    exit;
}

class CODENITCA_Recaptcha_Config {

    private static $instance = null;

    private $enable_v2;
    private $enable_v3;
    private $enable_turnstile;
    private $site_key_v2;
    private $secret_key_v2;
    private $site_key_v3;
    private $secret_key_v3;
    private $turnstile_site_key;
    private $turnstile_secret_key;
    private $wp_login;
    private $wp_register;
    private $wp_forgetpass;
    private $wp_comments;
    private $register = 0;
    private $login = 0;
    private $checkout = 0;
    private $forgetpass = 0;
    private $comments = 0;
    private $login_show;
    private $cf7_forms = 0;

    private function __construct() {
        $this->enable_v2     = (int) get_option('codenitcaptcha_v2_status', 0);
        $this->enable_v3     = (int) get_option('codenitcaptcha_v3_status', 0);
        $this->enable_turnstile = (int) get_option('codenitcaptcha_turnstile_status', 0);
        $this->site_key_v2   = sanitize_text_field((string) get_option('codenitcaptcha_site_key', ''));
        $this->secret_key_v2 = sanitize_text_field((string) get_option('codenitcaptcha_secret_key', ''));
        $this->site_key_v3   = sanitize_text_field((string) get_option('codenitcaptcha_site_v3_key', ''));
        $this->secret_key_v3 = sanitize_text_field((string) get_option('codenitcaptcha_secret_v3_key', ''));
        $this->turnstile_site_key = sanitize_text_field((string) get_option('codenitcaptcha_turnstile_site_key', ''));
        $this->turnstile_secret_key = sanitize_text_field((string) get_option('codenitcaptcha_turnstile_secret_key', ''));

        $this->wp_login      = (int) get_option('codenitcaptcha_wp_login', 0);
        $this->wp_register   = (int) get_option('codenitcaptcha_wp_register', 0);
        $this->wp_forgetpass = (int) get_option('codenitcaptcha_wp_forget_pass', 0);
        $this->wp_comments   = (int) get_option('codenitcaptcha_wp_comments', 0);

        if ($this->check_active_plugin('woocommerce/woocommerce.php')) {
            $this->register   = (int) get_option('codenitcaptcha_woo_register', 0);
            $this->login      = (int) get_option('codenitcaptcha_woo_login', 0);
            $this->checkout   = (int) get_option('codenitcaptcha_woo_checkout', 0);
            $this->forgetpass = (int) get_option('codenitcaptcha_woo_forgetpass', 0);
            $this->comments   = (int) get_option('codenitcaptcha_woo_comments', 0);
        }

        if ($this->check_active_plugin('contact-form-7/wp-contact-form-7.php')) {
            $this->cf7_forms = (int) get_option('codenitcaptcha_cf7_forms', 0);
        }

        $this->login_show = (int) get_option('codenitcaptcha_hide_login', 0);
    }

    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function enable_v2(): int { return $this->enable_v2; }
    public function enable_v3(): int { return $this->enable_v3; }
    public function get_site_key_v2(): string { return $this->site_key_v2; }
    public function enable_turnstile(): int { return $this->enable_turnstile; }
    public function get_turnstile_site_key(): string { return $this->turnstile_site_key; }
    public function get_turnstile_secret_key(): string { return $this->turnstile_secret_key; }
    public function get_active_provider(): string { return ($this->enable_turnstile() === 1 && $this->get_turnstile_site_key() && $this->get_turnstile_secret_key()) ? 'turnstile' : (($this->enable_v2() === 1 && $this->get_site_key_v2() && $this->get_secret_key_v2()) ? 'recaptcha' : ''); }
    public function is_captcha_enabled(): bool { return $this->get_active_provider() !== ''; }
    public function get_secret_key_v2(): string { return $this->secret_key_v2; }
    public function get_site_key_v3(): string { return $this->site_key_v3; }
    public function get_secret_key_v3(): string { return $this->secret_key_v3; }
    public function get_wp_login(): int { return $this->wp_login; }
    public function get_wp_register(): int { return $this->wp_register; }
    public function get_wp_forgetpass(): int { return $this->wp_forgetpass; }
    public function get_wp_comments(): int { return $this->wp_comments; }
    public function get_wcc_login(): int { return $this->login; }
    public function get_wcc_register(): int { return $this->register; }
    public function get_wcc_forgetpass(): int { return $this->forgetpass; }
    public function get_wcc_comments(): int { return $this->comments; }
    public function get_wcc_checkout(): int { return $this->checkout; }
    public function get_show_login(): int { return $this->login_show; }
    public function get_cf7_option(): int { return $this->cf7_forms; }

    public function check_active_plugin( $plugin_file ): bool {

        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( is_multisite() ) {

            if ( is_plugin_active_for_network( $plugin_file ) ) {
                return true;
            }

            return is_plugin_active( $plugin_file );
        }

        return is_plugin_active( $plugin_file );
    }

    public function messages($message): string {
        switch ($message) {
            case 'captcha_required':
                $output = __('The CAPTCHA is required. Please try again.', 'codenitive-captcha');
                break;
            case 'captcha_invalid':
                $output = __('The CAPTCHA was invalid. Please try again.', 'codenitive-captcha');
                break;
            case 'nonce_invalid':
                $output = __('Security check failed. Please refresh the page and try again.', 'codenitive-captcha');
                break;
            case 'verify_invalid':
                $output = __('Verification check failed. Please refresh the page and try again.', 'codenitive-captcha');
                break;
            case 'config_invalid':
                $output = __('Something wrong try again later.', 'codenitive-captcha');
                break;
            default:
                $output = __('An unknown CAPTCHA error occurred.', 'codenitive-captcha');
                break;
        }

        return apply_filters('codenitcaptcha_messages', $output, $message);
    }
}
