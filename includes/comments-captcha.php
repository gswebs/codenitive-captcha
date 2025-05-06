<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class JMB_Comments_Captcha_Render {
    
    // Load helper methods
    // The trait Recaptcha_Utils come from the includes/captcha-utils.php file.
    use Recaptcha_Utils;

    protected $config;

    public function __construct(JMB_Recaptcha_Config $config = null) {

        $this->config = $config ?: JMB_Recaptcha_Config::get_instance();

        $this->load_options();

    }    

    private function load_options() {
        if($this->config->enable_v2() == 1){
            add_action('wp', [$this, 'init_v2']);
            add_action('pre_comment_on_post', [$this, 'verify_recaptcha'], 100); // pre_comment_on_post hook only works inside wp_loaded hook
        }
    } 

    public function init_v2() {

        $wp_comments_enabled = $this->ck_single($this->config->get_wp_comments());
        $wc_comments_enabled = $this->ck_product($this->config->get_wcc_comments());
        $hide_login = $this->ck_login_hide($this->config->get_hide_login());

        if($hide_login == 'yes'){
            if ($wp_comments_enabled === 'yes' || $wc_comments_enabled === 'yes') {
                $this->config->maybe_enqueue_script();
                //add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
                add_filter('comment_form_defaults', [$this, 'render_recaptcha_html'], 50, 1);
            }
        }

    }

    // public function enqueue_scripts() {
    //     wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js?explicit&hl=' . get_locale(), array(), null, array('strategy' => 'defer'));
    // }

    public function render_recaptcha_html($defaults) {
        $site_key = $this->config->get_site_key_v2();
        $captcha = '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
    
        $defaults['submit_field'] = $captcha . $defaults['submit_field'];
        return $defaults;
    }

    public function verify_recaptcha($comment_post_ID) {

        if(isset($_POST['g-recaptcha-response'])){
            if (empty($_POST['g-recaptcha-response'])) {
                wp_die(
                    __("<strong>ERROR:</strong> Please complete the CAPTCHA."),
                    __('reCAPTCHA Error'),
                    ['back_link' => true]
                );
            }
        
            $response = sanitize_text_field($_POST['g-recaptcha-response']);
            $remote_ip = $_SERVER['REMOTE_ADDR'];
            $secret = $this->config->get_secret_key_v2();
        
            $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret'   => $secret,
                    'response' => $response,
                    'remoteip' => $remote_ip
                ]
            ]);
        
            $body = json_decode(wp_remote_retrieve_body($verify), true);
        
            if (empty($body['success'])) {
                wp_die(
                    __("<strong>ERROR:</strong> CAPTCHA verification failed. Please try again."),
                    __('reCAPTCHA Failed'),
                    ['back_link' => true]
                );
            }
        }
        
    }       

}