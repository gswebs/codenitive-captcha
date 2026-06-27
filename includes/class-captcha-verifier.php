<?php
namespace codenitcaptcha\includes;

if (!defined('ABSPATH')) {
    exit;
}

use codenitcaptcha\includes\config\CODENITCA_Recaptcha_Config;

class CODENITCA_Captcha_Verifier {

    private $config;

    public function __construct(?CODENITCA_Recaptcha_Config $config = null) {
        $this->config = $config ?: CODENITCA_Recaptcha_Config::get_instance();
    }

    public function verify_post(string $nonce_action = 'codenitcaptcha_action', string $nonce_field = 'codenitcaptcha_nonce'): array {
        if (!isset($_POST[$nonce_field]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_field])), $nonce_action)) {
            return ['status' => 'error', 'message' => 'nonce_invalid'];
        }

        $provider = $this->config->get_active_provider();
        if (empty($provider)) {
            return ['status' => 'error', 'message' => 'config_invalid'];
        }

        if ('turnstile' === $provider) {
            $token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';
            if (empty($token)) {
                return ['status' => 'error', 'message' => 'captcha_required'];
            }
            return $this->verify_turnstile_token($token)
                ? ['status' => 'success']
                : ['status' => 'error', 'message' => 'captcha_invalid'];
        }

        $token = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';
        if (empty($token)) {
            return ['status' => 'error', 'message' => 'captcha_required'];
        }

        return $this->verify_recaptcha_token($token)
            ? ['status' => 'success']
            : ['status' => 'error', 'message' => 'captcha_invalid'];
    }

    public function verify_token(string $response_token, ?string $secret = null): bool {
        if ('turnstile' === $this->config->get_active_provider()) {
            return $this->verify_turnstile_token($response_token, $secret);
        }

        return $this->verify_recaptcha_token($response_token, $secret);
    }

    public function verify_recaptcha_token(string $response_token, ?string $secret = null): bool {
        $secret = $secret ?: $this->config->get_secret_key_v2();
        if (empty($secret) || empty($response_token)) {
            return false;
        }

        $response = wp_safe_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret'   => $secret,
                'response' => $response_token,
                'remoteip' => $this->get_remote_ip(),
            ],
        ]);

        return $this->is_successful_response($response);
    }

    public function verify_turnstile_token(string $response_token, ?string $secret = null): bool {
        $secret = $secret ?: $this->config->get_turnstile_secret_key();
        if (empty($secret) || empty($response_token)) {
            return false;
        }

        $response = wp_safe_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret'   => $secret,
                'response' => $response_token,
                'remoteip' => $this->get_remote_ip(),
            ],
        ]);

        return $this->is_successful_response($response);
    }

    private function is_successful_response($response): bool {
        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return isset($body['success']) && true === $body['success'];
    }

    private function get_remote_ip(): string {
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            return '';
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        $remoteip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);

        return false === $remoteip ? '' : (string) $remoteip;
    }
}

class CODENITCA_Recaptcha_Verifier extends CODENITCA_Captcha_Verifier {}
