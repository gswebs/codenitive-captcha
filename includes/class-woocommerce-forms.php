<?php
namespace codenitcaptcha\includes;

if (!defined('ABSPATH')) {
    exit;
}

use codenitcaptcha\includes\config\CODENITCA_Recaptcha_Config;

class CODENITCA_WooCommerce_Forms_Integration {

    protected $config;
    protected $assets;
    protected $verifier;

    public function __construct() {
        $this->config   = CODENITCA_Recaptcha_Config::get_instance();
        $this->assets   = new CODENITCA_Captcha_Assets($this->config);
        $this->verifier = new CODENITCA_Captcha_Verifier($this->config);

        add_action('init', [$this, 'init']);
    }

    public function init(): void {
        if (!$this->config->is_captcha_enabled() || !$this->config->check_active_plugin('woocommerce/woocommerce.php')) {
            return;
        }

        $this->assets->maybe_enqueue_recaptcha();
        add_action('wp_enqueue_scripts', [$this->assets, 'enqueue_style']);

        if ($this->config->get_wcc_register() == 1) {
            add_action('woocommerce_register_form', [$this, 'display_register_captcha'], 20);
            add_filter('woocommerce_process_registration_errors', [$this, 'validate_registration_captcha'], 5, 4);
        }

        if ($this->config->get_wcc_login() == 1) {
            add_action('woocommerce_login_form', [$this, 'display_captcha'], 30);
            add_filter('woocommerce_process_login_errors', [$this, 'validate_login_captcha'], 30, 3);
        }

        if ($this->config->get_wcc_checkout() == 1 && $this->should_show_to_current_user()) {
            add_action('woocommerce_review_order_before_submit', [$this, 'display_checkout_captcha'], 20);
            add_action('woocommerce_checkout_process', [$this, 'validate_checkout_captcha'], 10);
            add_action('wp_enqueue_scripts', [$this->assets, 'enqueue_checkout_script']);
        }

        if ($this->config->get_wcc_forgetpass() == 1) {
            add_action('woocommerce_lostpassword_form', [$this, 'display_captcha'], 20);
            add_action('woocommerce_lostpassword_form', [$this, 'wc_forgot_password_hidden_field']);
            add_action('lostpassword_post', [$this, 'validate_forgetpass_captcha'], 21, 2);
        }
    }

    protected function should_show_to_current_user(): bool {
        return !is_user_logged_in() || ($this->config->get_show_login() == 1 && is_user_logged_in());
    }

    public function display_captcha(): void {
        if (!$this->config->is_captcha_enabled()) {
            return;
        }

        echo '<input type="hidden" name="codenitcaptcha_nonce" value="' . esc_attr(wp_create_nonce('codenitcaptcha_action')) . '" />';
        echo wp_kses_post($this->get_captcha_html());
    }

    public function display_checkout_captcha(): void {
        if (!$this->config->is_captcha_enabled()) {
            return;
        }

        echo '<input type="hidden" name="codenitcaptcha_nonce" value="' . esc_attr(wp_create_nonce('codenitcaptcha_action')) . '" />';
        echo '<div id="wccn-captcha-box">' . wp_kses_post($this->get_captcha_html()) . '</div>';
    }

    public function display_register_captcha(): void {
        echo '<input type="hidden" name="wooregister" value="_codenitcaptcha_nonce_wcc_register">';
        echo wp_kses_post(wp_nonce_field('codenitcaptcha_action_woo_register', 'codenitcaptcha_nonce_woo_register'));
        echo wp_kses_post($this->get_captcha_html());
    }

    protected function get_captcha_html(): string {
        if ('turnstile' === $this->config->get_active_provider()) {
            return '<div class="cf-turnstile codenitcaptcha-turnstile" data-sitekey="' . esc_attr($this->config->get_turnstile_site_key()) . '"></div>';
        }

        return '<div class="g-recaptcha codenitcaptcha-recaptcha" data-sitekey="' . esc_attr($this->config->get_site_key_v2()) . '"></div>';
    }

    public function wc_forgot_password_hidden_field(): void {
        echo '<input type="hidden" name="wc_forget" value="wc">';
    }

    public function validate_login_captcha($validation_error, $username, $password) {
        return $this->maybe_return_wc_error($validation_error, $this->verifier->verify_post());
    }

    public function validate_registration_captcha($validation_error, $username, $password, $email) {
        return $this->maybe_return_wc_error($validation_error, $this->verifier->verify_post('codenitcaptcha_action_woo_register', 'codenitcaptcha_nonce_woo_register'));
    }

    public function validate_forgetpass_captcha($validation_errors, $user_data = '') {
        if (!isset($_POST['wc_forget']) || 'wc' !== $_POST['wc_forget']) {
            return $validation_errors;
        }

        if (!isset($_POST['woocommerce-lost-password-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce-lost-password-nonce'])), 'lost_password')) {
            $validation_errors->add('invalid_nonce', $this->config->messages('nonce_invalid'));
        }

        $response = $this->verifier->verify_post();
        if (isset($response['status']) && 'error' === $response['status']) {
            $validation_errors->add('captcha_invalid', $this->config->messages($response['message']));
        }

        return $validation_errors;
    }

    public function validate_checkout_captcha(): void {
        $response = $this->verifier->verify_post();

        if (isset($response['status']) && 'error' === $response['status']) {
            wc_add_notice($this->config->messages($response['message']), 'error');
        }
    }

    private function maybe_return_wc_error($validation_error, array $response) {
        if (isset($response['status']) && 'error' === $response['status']) {
            return new \WP_Error('captcha_invalid', $this->config->messages($response['message']));
        }

        return $validation_error;
    }
}
