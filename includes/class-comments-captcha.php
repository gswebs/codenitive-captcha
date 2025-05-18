<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class JMB_Comments_Captcha_Render {

    protected $config;

    public function __construct(JMB_Recaptcha_Config $config = null) {

        $this->config = $config ?: JMB_Recaptcha_Config::get_instance();
        
        add_filter('comment_form_defaults', [$this, 'render_recaptcha_html'], 50, 1);
        add_filter('preprocess_comment', [$this, 'comment_captcha_validate'], 10, 1);
    } 

    public function render_recaptcha_html($defaults) {
        if($this->config->get_show_login() !== '1' && is_user_logged_in()){
            return $defaults;
        }
        if(function_exists('is_product')){
            if(is_product() && $this->config->get_wcc_comments() === '1' ){
                return $defaults;
            }
        }
        if($this->config->enable_v2() === '1'){
            if($this->config->get_wp_comments() === '1'){

                $this->config->maybe_enqueue_script();
                $site_key = $this->config->get_site_key_v2();
                $captcha = '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
                $captcha .= wp_nonce_field( 'jmb_recaptcha_action', 'jmb_recaptcha_nonce', true, false );
            
                $defaults['submit_field'] = $captcha . $defaults['submit_field'];

            }
        }
        return $defaults;
    }

    public function comment_captcha_validate($commentdata) {
        $site_key = $this->config->get_site_key_v2();

        $post_id = $commentdata['comment_post_ID'];
        // Get the post type using the post ID
        $post_type = get_post_type($post_id);

        if($this->config->enable_v2() !== '1' || empty($site_key) ){
            return $commentdata;
        }

        if($this->config->get_show_login() !== '1' && is_user_logged_in()) {
            return $commentdata;
        }

        if($post_type === 'product' && $this->config->get_wcc_comments() === '1') {
                return $commentdata;
        }
        
        if($this->config->get_wp_comments() !== '1') {
            return $commentdata;
        }

        if(!$this->verify_captcha()) {
            wp_die(
                wp_kses_post($this->config->messages('captcha_invalid')),
                esc_html__('reCAPTCHA Failed', 'jmb-captcha'),
                ['back_link' => true]
            );   
        }

        return $commentdata;
    }

    /**
     * Verify reCAPTCHA
     */
    private function verify_captcha() {
        $secret = $this->config->get_secret_key_v2();
        if (empty($secret)) {
            return false;
        }

        $captcha_response = '';
        $captcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';

        if (empty($captcha_response) && !empty($_POST)) {
            return false;
        }

        // Verify nonce first
        if (!isset($_POST['jmb_recaptcha_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['jmb_recaptcha_nonce'])), 'jmb_recaptcha_action')) {
            return false;
        }

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'body' => [
            'secret' => $secret,
            'response' => $captcha_response
        ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return isset($body['success']) && $body['success'];
    }
}