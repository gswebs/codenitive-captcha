<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class JMB_WC_Captcha_Forms {

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
        
        $wp_login = get_option( 'jmb_captcha_wp_login', 0 );
        $wp_register = get_option('jmb_captcha_wp_register', 0);
        $wp_forgetpass = get_option('jmb_captcha_wp_forget_pass', 0);
        $wp_comments = get_option('jmb_captcha_wp_comments', 0);
        
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('login_enqueue_scripts', array($this, 'enqueue_scripts'));

        if (class_exists('WooCommerce')) {
            $register = get_option( 'jmb_captcha_woo_register', 0 );
            $login = get_option( 'jmb_captcha_woo_login', 0 );
            $checkout = get_option( 'jmb_captcha_woo_checkout', 0 );
            $forgetpass = get_option( 'jmb_captcha_woo_forgetpass', 0 );
            $comments = get_option( 'jmb_captcha_woo_comments', 0 );

            //$ck_checkout = ck_checkout($checkout);

            if ( $register == 1 ) {
                add_action('woocommerce_register_form', array($this, 'display_captcha'), 20);
                add_filter('woocommerce_process_registration_errors', array($this, 'validate_registration_captcha'), 5, 4);
            }
            if ( $login == 1 ) {
                add_action('woocommerce_login_form', array($this, 'display_captcha'), 30);
                add_filter('woocommerce_process_login_errors', array($this, 'validate_login_captcha'), 10, 3);
            }
            if ( $checkout == 1 ) {
                add_action('woocommerce_review_order_before_submit', array($this, 'display_captcha'), 20);
                add_action('woocommerce_checkout_process', array($this, 'validate_checkout_captcha'), 10);
                add_action('wp_footer', array($this, 'add_checkout_recaptcha_script'), 99);
            }
            if ( $forgetpass == 1 ) {
                add_action('woocommerce_lostpassword_form', array($this, 'display_captcha'), 20);
                add_action('lostpassword_post', array($this, 'validate_forgetpass_captcha'), 10, 2);
            }

        }


        if ( $wp_login == 1 ) {
            add_action('login_form', array($this, 'display_captcha'), 20);
            add_action('authenticate', array($this, 'validate_wplogin_captcha'), 21, 3);
        }

        if ( $wp_register == 1 ) {
            add_action('register_form', array($this, 'display_captcha'), 20);
            add_action('registration_errors', array($this, 'validate_wpregister_captcha'), 21, 3);
        }
        if ( $wp_forgetpass == 1 ) {
            add_action('lostpassword_form', array($this, 'display_captcha'), 20);
            add_action('lostpassword_post', array($this, 'validate_forgetpass_captcha'), 21, 3);
        }

    }


    // function is_valid_captcha_response($captcha) { 
    //     $captcha_postdata = http_build_query( 
    //         array( 
    //             'secret' => $this->secret_key_v2, 
    //             'response' => $captcha, 
    //             'remoteip' => $_SERVER['REMOTE_ADDR'] 
    //         ) 
    //     ); 
    //     $captcha_opts = array( 
    //         'http' => array( 
    //             'method'  => 'POST', 
    //             'header'  => 'Content-type: application/x-www-form-urlencoded', 
    //             'content' => $captcha_postdata 
    //         ) 
    //     ); 
    //     $captcha_context  = stream_context_create($captcha_opts); 
    //     $captcha_response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, $captcha_context), true); 
    //     if(!empty($captcha_response['success'])){ 
    //         return true; 
    //     }else{ 
    //         return false; 
    //     } 
    // } 
     
    function verify_google_recaptcha() { 
        $recaptcha = $_POST['g-recaptcha-response']; 
        if(empty($recaptcha)){ 
            wp_die(__("<b>ERROR: </b><b>Please click the captcha checkbox.</b><p><a href='javascript:history.back()'>« Back</a></p>")); 
        }elseif(!is_valid_captcha_response($recaptcha)){ 
            wp_die(__("<b>Sorry, spam detected!</b>")); 
        } 
    } 

    public function enqueue_scripts() {
        wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js?explicit&hl=' . get_locale(), array(), null, array('strategy' => 'defer'));
    }

    public function validate_checkout_captcha() {
        $this->verify_checkout_captcha();
    }

    public function display_captcha() {
        if ($this->site_key_v2) {
            if(is_checkout()){
                echo '<div id="wc-captcha-box"><div class="g-recaptcha" data-sitekey="' . esc_attr($this->site_key_v2) . '"></div></div>';
            } else {
                echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($this->site_key_v2) . '"></div>';
            }
        }
    }

    public function validate_wplogin_captcha($user, $username, $password) {
        if(isset($_POST['g-recaptcha-response'])){
            $response = $this->verify_captcha($_POST['g-recaptcha-response']);
            if(isset($response['status'])){
                if ( $response['status'] == "error") {
                    return new WP_Error('Captcha Invalid', __($response['message']));
                }
            }
        }
        return $user;
    }

    public function validate_wpregister_captcha($validation_error, $username, $password) {
        $response = $this->verify_captcha($_POST['g-recaptcha-response']);
        if(isset($response['status'])){
            if ( $response['status'] == "error") {
                $validation_error = new WP_Error('Captcha Invalid', __($response['message']));
            }
        }
        return $validation_error;
    }

    public function validate_forgetpass_captcha($validation_errors, $user_data = ''){
        if(isset($_POST['woocommerce-lost-password-nonce'])) {
            $response = $this->verify_captcha($_POST['g-recaptcha-response']);
            if(isset($response['status'])){
                if ( $response['status'] == "error") {
                    //$validation_error = new WP_Error('Captcha Invalid', __($response['message']));
                    $validation_errors->add( 'Captcha Invalid', __( $response['message'], 'recaptcha-woo' ) );
                }
            }
        }
    }

    public function validate_login_captcha($validation_error, $username, $password) {
        $response = $this->verify_captcha($_POST['g-recaptcha-response']);
        if(isset($response['status'])){
            if ( $response['status'] == "error") {
                $validation_error = new WP_Error('Captcha Invalid', __($response['message']));
            }
        }
        return $validation_error;
    }

    public function validate_registration_captcha($validation_error, $username, $password, $email) {

        $response = $this->verify_captcha($_POST['g-recaptcha-response']);

        if(isset($response['status'])){
            if ( $response['status'] == "error") {
                //$validation_error->add('captcha_error', __($response['message'], 'woocommerce'));
                $validation_error = new WP_Error('Captcha Invalid', __($response['message']));
            }
        }
        return $validation_error;
    }

    public function verify_captcha($response) {
        if (empty($_POST['g-recaptcha-response'])) {
            return array(
                'status' => 'error',
                'message' => 'Please complete the reCAPTCHA.'
            );
        }

        $response = sanitize_text_field($_POST['g-recaptcha-response']);
        $remoteip = $_SERVER['REMOTE_ADDR'];

        $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $this->secret_key_v2,
                'response' => $response,
                'remoteip' => $remoteip
            ]
        ]);

        $result = json_decode(wp_remote_retrieve_body($verify));

        if (empty($result->success)) {
            return array(
                'status' => 'error',
                'message' => 'reCAPTCHA failed. Please try again later'
            );
        }
    }

    public function verify_checkout_captcha() {
        if (empty($_POST['g-recaptcha-response'])) {
            wc_add_notice(__('Please complete the reCAPTCHA.', 'your-textdomain'), 'error');
            return;
        }

        $response = sanitize_text_field($_POST['g-recaptcha-response']);
        $remoteip = $_SERVER['REMOTE_ADDR'];

        $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $this->secret_key_v2,
                'response' => $response,
                'remoteip' => $remoteip
            ]
        ]);

        $result = json_decode(wp_remote_retrieve_body($verify));

        if (empty($result->success)) {
            wc_add_notice(__('reCAPTCHA failed. Please try again.', 'your-textdomain'), 'error');
        }
    }
    public function add_checkout_recaptcha_script() {
        if (!is_checkout()) return;
        ?>
        <div id="recaptcha-script-placeholder"></div>
        <script>
        let captchaRendered = false;
        let site_key = '<?php echo $this->site_key_v2; ?>';
        function renderCaptchaOnCheckout() {
            const wrapper = document.querySelector('#wc-captcha-box');
            if (!wrapper || typeof grecaptcha === 'undefined') return;

            // Remove previous .g-recaptcha to avoid double render
            const oldRecaptcha = wrapper.querySelector('.g-recaptcha');
            if (oldRecaptcha) oldRecaptcha.remove();

            // Add new one
            const newRecaptcha = document.createElement('div');
            newRecaptcha.className = 'g-recaptcha';
            wrapper.appendChild(newRecaptcha);

            recaptchaWidgetId = grecaptcha.render(newRecaptcha, {
                sitekey: site_key
            });
        }

        function onRecaptchaApiLoad() {
            renderCaptchaOnCheckout();

            jQuery(document.body).on('updated_checkout updated_wc_div', function () {
                renderCaptchaOnCheckout();
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const script = document.createElement('script');
            script.src = "https://www.google.com/recaptcha/api.js?onload=onRecaptchaApiLoad&render=explicit";
            script.async = true;
            script.defer = true;
            document.getElementById('recaptcha-script-placeholder').appendChild(script);
        });
        </script>
        <?php
    }

    private function ck_product($option){
        $output = ($option == 1 && is_product()) ? 'yes' : 'no';
        return $output;
    }
    
    private function ck_single($option){
        $output = ($option == 1 && is_single()) ? 'yes' : 'no';
        return $output;
    }

}