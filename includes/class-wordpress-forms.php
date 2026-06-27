<?php
namespace codenitcaptcha\includes;

if (!defined('ABSPATH')) {
    exit;
}

use codenitcaptcha\includes\config\CODENITCA_Recaptcha_Config;

class CODENITCA_WordPress_Forms_Integration {

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
        if (!$this->config->is_captcha_enabled()) {
            return;
        }

        $this->assets->maybe_enqueue_recaptcha();
        add_action('login_enqueue_scripts', [$this->assets, 'enqueue_login_recaptcha']);
        add_action('login_enqueue_scripts', [$this->assets, 'enqueue_style']);
        add_action('wp_enqueue_scripts', [$this->assets, 'enqueue_style']);

        if ($this->config->get_wp_login() == 1) {
            add_action('login_form', [$this, 'display_captcha'], 20);
            add_action('login_form', [$this, 'wp_login_hidden_field'], 21);
            add_action('authenticate', [$this, 'validate_login_captcha'], 21, 3);
        }

        if ($this->config->get_wp_register() == 1) {
            add_action('register_form', [$this, 'display_captcha'], 20);
            add_filter('registration_errors', [$this, 'validate_register_captcha'], 21, 3);
        }

        if ($this->config->get_wp_forgetpass() == 1) {
            add_action('lostpassword_form', [$this, 'display_captcha'], 20);
            add_action('lostpassword_form', [$this, 'wp_forgot_password_hidden_field']);
            add_action('lostpassword_post', [$this, 'validate_forgetpass_captcha'], 21, 2);
        }
    }

    public function display_captcha(): void {
        if (!$this->config->is_captcha_enabled()) {
            return;
        }

        echo '<input type="hidden" name="codenitcaptcha_nonce" value="' . esc_attr(wp_create_nonce('codenitcaptcha_action')) . '" />';
        echo wp_kses_post($this->get_captcha_html());
    }

    protected function get_captcha_html(): string {
        if ('turnstile' === $this->config->get_active_provider()) {
            return '<div class="cf-turnstile codenitcaptcha-turnstile" data-sitekey="' . esc_attr($this->config->get_turnstile_site_key()) . '"></div>';
        }

        return '<div class="g-recaptcha codenitcaptcha-recaptcha" data-sitekey="' . esc_attr($this->config->get_site_key_v2()) . '"></div>';
    }

    public function wp_forgot_password_hidden_field(): void {
        echo '<input type="hidden" name="wp_forget" value="wp">';
    }

    public function wp_login_hidden_field(): void {
        echo '<input type="hidden" name="codenit_wp_login" value="codenit-wp-login">';
    }

    private function verify_request_nonce(): bool {
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- This reads the nonce field itself for verification.
        $nonce = isset( $_POST['codenitcaptcha_nonce'] )
            ? sanitize_text_field( wp_unslash( $_POST['codenitcaptcha_nonce'] ) )
            : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if ( empty( $nonce ) ) {
            return false;
        }

        return (bool) wp_verify_nonce( $nonce, 'codenitcaptcha_action' );
    }

    public function validate_login_captcha( $user, $username, $password ) {
        
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Checked after confirming this is our login form.
        $codenit_wp_login = isset( $_POST['codenit_wp_login'] )
            ? sanitize_text_field( wp_unslash( $_POST['codenit_wp_login'] ) )
            : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if ( 'codenit-wp-login' !== $codenit_wp_login ) {
            return $user;
        }

        if ( ! $this->verify_request_nonce() ) {
            return new \WP_Error(
                'captcha_nonce_invalid',
                $this->config->messages( 'nonce_invalid' )
            );
        }

        $response = $this->verifier->verify_post();

        if ( isset( $response['status'] ) && 'error' === $response['status'] ) {
            return new \WP_Error(
                'captcha_invalid',
                $this->config->messages( $response['message'] )
            );
        }

        return $user;
    }

    public function validate_register_captcha( $validation_error, $username, $password ) {

        if ( ! $this->verify_request_nonce() ) {
            $validation_error->add(
                'captcha_nonce_invalid',
                $this->config->messages( 'nonce_invalid' )
            );

            return $validation_error;
        }

        $response = $this->verifier->verify_post();

        if ( isset( $response['status'] ) && 'error' === $response['status'] ) {
            $validation_error->add(
                'captcha_invalid',
                $this->config->messages( $response['message'] )
            );
        }

        return $validation_error;
    }

    public function validate_forgetpass_captcha( $validation_errors, $user_data = '' ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Checked before CAPTCHA verification.
        $wp_forget = isset( $_POST['wp_forget'] )
            ? sanitize_text_field( wp_unslash( $_POST['wp_forget'] ) )
            : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if ( 'wp' !== $wp_forget ) {
            return $validation_errors;
        }

        if ( ! $this->verify_request_nonce() ) {
            $validation_errors->add(
                'captcha_nonce_invalid',
                $this->config->messages( 'nonce_invalid' )
            );

            return $validation_errors;
        }

        $response = $this->verifier->verify_post();

        if ( isset( $response['status'] ) && 'error' === $response['status'] ) {
            $validation_errors->add(
                'captcha_invalid',
                $this->config->messages( $response['message'] )
            );
        }

        return $validation_errors;
    }
}
