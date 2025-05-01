<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class JMB_WC_Comments_Captcha {

    private $enable_v2;
    private $enable_v3;
    private $site_key_v2;
    private $secret_key_v2;
    private $site_key_v3;
    private $secret_key_v3;

    public function __construct() {
        $this->load_options();
    }

    private function load_options() {
        $this->enable_v2 = get_option('jmb_captcha_v2_status');
        $this->enable_v3 = get_option('jmb_captcha_v3_status');

        $this->site_key_v2 = esc_attr( get_option('jmb_captcha_site_key') );
        $this->secret_key_v2 = esc_attr( get_option('jmb_captcha_secret_key') );
        $this->site_key_v3 = esc_attr( get_option('jmb_captcha_site_v3_key') );
        $this->secret_key_v3 = esc_attr( get_option('jmb_captcha_secret_v3_key') );

        if($this->enable_v2 == 1){
            $this->init_v2();
        }
    }    

    public function init_v2() {

        $wp_comments = get_option('jmb_captcha_wp_comments', 0);
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        if( $this->ck_single($wp_comments) == 'yes' ){
            add_filter('comment_form_defaults', array($this, 'add_grecaptcha_tocomment'), 10, 1); 
            //add_action('comment_form_after_fields', array($this, 'display_captcha')); //
            //add_action('comment_form_logged_in_after', array($this, 'display_captcha'));// add_action( 'comment_form_after_fields', array($this, 'scripts_print'));
            add_action( 'pre_comment_on_post', array($this, 'verify_google_recaptcha'), 50, 1);
        }

    }

    public function is_valid_captcha_response($captcha) { 
        $captcha_postdata = http_build_query( 
            array( 
                'secret' => $this->secret_key_v2, 
                'response' => $captcha, 
                'remoteip' => $_SERVER['REMOTE_ADDR'] 
            ) 
        ); 
        $captcha_opts = array( 
            'http' => array( 
                'method'  => 'POST', 
                'header'  => 'Content-type: application/x-www-form-urlencoded', 
                'content' => $captcha_postdata 
            ) 
        ); 
        $captcha_context  = stream_context_create($captcha_opts); 
        $captcha_response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, $captcha_context), true); 
        if(!empty($captcha_response['success'])){ 
            return true; 
        }else{ 
            return false; 
        } 
    } 
    
    public function verify_google_recaptcha() { 
        $recaptcha = $_POST['g-recaptcha-response']; 
        if(empty($recaptcha)){ 
            wp_die(__("<b>ERROR: </b><b>Please click the captcha checkbox.</b><p><a href='javascript:history.back()'>« Back</a></p>")); 
        }elseif(!$this->is_valid_captcha_response($recaptcha)){ 
            wp_die(__("<b>Sorry, spam detected!</b>")); 
        } 
    } 

    public function enqueue_scripts() {
        wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js?explicit&hl=' . get_locale(), array(), null, array('strategy' => 'defer'));
    }

    public function add_grecaptcha_tocomment($submit_field) { 
        $submit_field['submit_field'] = '<div class="g-recaptcha" data-sitekey="' . esc_attr($this->site_key_v2) . '"></div>'.$submit_field['submit_field']; 
        return $submit_field; 
    }

    private function ck_product($option){
        $output = ($option == 1 && is_product()) ? 'yes' : 'no';
        return $output;
    }
    
    private function ck_single($option){
        $output = ($option == 1) ? 'yes' : 'no';
        return $output;
    }

}