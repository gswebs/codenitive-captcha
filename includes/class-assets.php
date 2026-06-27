<?php
namespace codenitcaptcha\includes;

if (!defined('ABSPATH')) {
    exit;
}

use codenitcaptcha\includes\config\CODENITCA_Recaptcha_Config;

class CODENITCA_Captcha_Assets {

    private static $script_enqueued = false;
    private static $style_enqueued = false;
    private $config;

    public function __construct(?CODENITCA_Recaptcha_Config $config = null) {
        $this->config = $config ?: CODENITCA_Recaptcha_Config::get_instance();
    }

    public function maybe_enqueue_captcha(): void {
        if (!self::$script_enqueued) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_captcha'], 999);
            self::$script_enqueued = true;
        }
    }

    public function maybe_enqueue_recaptcha(): void {
        $this->maybe_enqueue_captcha();
    }

    public function enqueue_captcha(): void {
        if (is_admin() || !$this->config->is_captcha_enabled()) {
            return;
        }

        wp_enqueue_script('codenitcaptcha-captcha-js', CODENITCAPTCHA_PLUGIN_DIR_ASSETS_URL . 'js/scripts.js', [], CODENITCAPTCHA_VERSION, true);
        wp_localize_script('codenitcaptcha-captcha-js', 'CodenitCaptchaData', [
            'provider' => $this->config->get_active_provider(),
            'siteKey'  => $this->get_active_site_key(),
        ]);

        if ('turnstile' === $this->config->get_active_provider()) {
            $url = add_query_arg([
                'onload' => 'turnstileCallback',
                'render' => 'explicit',
            ], 'https://challenges.cloudflare.com/turnstile/v0/api.js');

            // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External CAPTCHA API must not receive WordPress ?ver parameter.
            wp_enqueue_script('cloudflare-turnstile', $url, ['codenitcaptcha-captcha-js'], null, true);
            return;
        }

        $url = add_query_arg([
            'onload' => 'recaptchaCallback',
            'render' => 'explicit',
        ], 'https://www.google.com/recaptcha/api.js');
        
        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External reCAPTCHA API should not receive WordPress ?ver parameter.
        wp_enqueue_script('google-recaptcha', $url, ['codenitcaptcha-captcha-js'], null, true);
    }

    public function enqueue_recaptcha(): void {
        $this->enqueue_captcha();
    }

    public function enqueue_login_recaptcha(): void {
        $this->enqueue_captcha();
    }

    public function enqueue_style(): void {
        if (self::$style_enqueued) {
            return;
        }

        wp_register_style('codenitcaptcha-style', false, [], CODENITCAPTCHA_VERSION);
        wp_enqueue_style('codenitcaptcha-style');
        wp_add_inline_style('codenitcaptcha-style', '.g-recaptcha,.cf-turnstile,.codenitcaptcha-turnstile { margin-bottom: 15px; }');
        self::$style_enqueued = true;
    }

    public function enqueue_checkout_script(): void {
        if (!function_exists('is_checkout') || !is_checkout()) {
            return;
        }

        $this->enqueue_captcha();
        $deps = ['jquery', 'codenitcaptcha-captcha-js'];
        if ('turnstile' === $this->config->get_active_provider()) {
            $deps[] = 'cloudflare-turnstile';
        } else {
            $deps[] = 'google-recaptcha';
        }
        wp_enqueue_script('codenitcaptcha-script-checkout', CODENITCAPTCHA_PLUGIN_DIR_ASSETS_URL . 'js/checkout.js', $deps, CODENITCAPTCHA_VERSION, true);
    }

    private function get_active_site_key(): string {
        if ('turnstile' === $this->config->get_active_provider()) {
            return $this->config->get_turnstile_site_key();
        }

        return $this->config->get_site_key_v2();
    }
}
