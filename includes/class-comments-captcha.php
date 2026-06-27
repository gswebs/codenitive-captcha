<?php
namespace codenitcaptcha\includes;

use codenitcaptcha\includes\config\CODENITCA_Recaptcha_Config;

if (!defined('ABSPATH')) {
    exit;
}

class CODENITCA_Comments_Captcha_Render {

    protected $config;
    protected $assets;
    protected $verifier;

    public function __construct() {
        $this->config   = CODENITCA_Recaptcha_Config::get_instance();
        $this->assets   = new CODENITCA_Captcha_Assets($this->config);
        $this->verifier = new CODENITCA_Captcha_Verifier($this->config);

        add_filter('comment_form_defaults', [$this, 'render_recaptcha_html'], 50, 1);
        add_filter('preprocess_comment', [$this, 'comment_captcha_validate'], 10, 1);
    }

    public function render_recaptcha_html($defaults) {
        if ($this->config->get_show_login() != 1 && is_user_logged_in()) {
            return $defaults;
        }

        if (function_exists('is_product') && is_product() && $this->config->get_wcc_comments() == 1) {
            return $defaults;
        }

        if ($this->config->is_captcha_enabled() && $this->config->get_wp_comments() == 1) {
            $this->assets->maybe_enqueue_recaptcha();
            $this->assets->enqueue_style();

            $captcha  = $this->get_captcha_html();
            $captcha .= wp_nonce_field('codenitcaptcha_action', 'codenitcaptcha_nonce', true, false);

            $defaults['submit_field'] = $captcha . $defaults['submit_field'];
        }

        return $defaults;
    }

    protected function get_captcha_html(): string {
        if ('turnstile' === $this->config->get_active_provider()) {
            return '<div class="cf-turnstile codenitcaptcha-turnstile" data-sitekey="' . esc_attr($this->config->get_turnstile_site_key()) . '"></div>';
        }

        return '<div class="g-recaptcha codenitcaptcha-recaptcha" data-sitekey="' . esc_attr($this->config->get_site_key_v2()) . '"></div>';
    }

    public function comment_captcha_validate( $commentdata ) {

        $post_id   = isset( $commentdata['comment_post_ID'] ) ? (int) $commentdata['comment_post_ID'] : 0;
        $post_type = $post_id ? get_post_type( $post_id ) : '';

        if ( ! $this->config->is_captcha_enabled() ) {
            return $commentdata;
        }

        if ( $this->config->get_show_login() != 1 && is_user_logged_in() ) {
            return $commentdata;
        }

        if ( 'product' === $post_type && $this->config->get_wcc_comments() == 1 ) {
            return $commentdata;
        }

        if ( $this->config->get_wp_comments() != 1 ) {
            return $commentdata;
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Verifying comment form nonce.
        $nonce = isset( $_POST['codenitcaptcha_nonce'] )
            ? sanitize_text_field( wp_unslash( $_POST['codenitcaptcha_nonce'] ) )
            : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'codenitcaptcha_action' ) ) {
            wp_die(
                esc_html( $this->config->messages( 'nonce_invalid' ) ),
                esc_html__( 'CAPTCHA Failed', 'codenitive-captcha' ),
                array( 'back_link' => true )
            );
        }

        $response = $this->verifier->verify_post();

        if ( isset( $response['status'] ) && 'error' === $response['status'] ) {
            wp_die(
                esc_html( $this->config->messages( $response['message'] ) ),
                esc_html__( 'CAPTCHA Failed', 'codenitive-captcha' ),
                array( 'back_link' => true )
            );
        }

        return $commentdata;
    }
}
