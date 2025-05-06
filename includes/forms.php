<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class JMB_Captcha_Render {

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
            $this->init_v2();
        }
    }    

    public function init_v2() {
    
        $hide_login = $this->ck_login_hide($this->config->get_hide_login());

        if(!is_single()){
            //add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            $this->config->maybe_enqueue_script();
        }
        add_action('login_enqueue_scripts', array($this->config, 'enqueue_script'));

        if (class_exists('WooCommerce')) {
        
            if ( $this->config->get_wcc_register() == 1 ) {
                add_action('woocommerce_register_form', array($this, 'display_captcha'), 20);
                add_filter('woocommerce_process_registration_errors', array($this, 'validate_registration_captcha'), 5, 4);
            }
            if ( $this->config->get_wcc_login() == 1 ) {
                add_action('woocommerce_login_form', array($this, 'display_captcha'), 30);
                add_filter('woocommerce_process_login_errors', array($this, 'validate_login_captcha'), 10, 3);
            }
            if ( $this->config->get_wcc_checkout() == 1 ) {
                if($hide_login == 'yes'){
                    add_action('woocommerce_review_order_before_submit', array($this, 'display_captcha'), 20);
                    add_action('woocommerce_checkout_process', array($this, 'validate_checkout_captcha'), 10);
                    add_action('wp_footer', array($this, 'add_checkout_recaptcha_script'), 99);
                }
            }
            if ( $this->config->get_wcc_forgetpass() == 1 ) {
                add_action('woocommerce_lostpassword_form', array($this, 'display_captcha'), 20);
                add_action('lostpassword_post', array($this, 'validate_forgetpass_captcha'), 10, 2);
            }

        }

        if ( $this->config->get_wp_login() == 1 ) {
            add_action('login_form', array($this, 'display_captcha'), 20);
            add_action('authenticate', array($this, 'validate_wplogin_captcha'), 21, 3);
        }

        if ( $this->config->get_wp_register() == 1 ) {
            add_action('register_form', array($this, 'display_captcha'), 20);
            add_action('registration_errors', array($this, 'validate_wpregister_captcha'), 21, 3);
        }
        if ( $this->config->get_wp_forgetpass() == 1 ) {
            add_action('lostpassword_form', array($this, 'display_captcha'), 20);
            add_action('lostpassword_post', array($this, 'validate_forgetpass_captcha'), 21, 3);
        }

    }

    public function verify_google_recaptcha() { 
        $recaptcha = $_POST['g-recaptcha-response']; 
        if(empty($recaptcha)){ 
            wp_die(__("<b>ERROR: </b><b>Please click the captcha checkbox.</b><p><a href='javascript:history.back()'>« Back</a></p>")); 
        }elseif(!is_valid_captcha_response($recaptcha)){ 
            wp_die(__("<b>Sorry, spam detected!</b>")); 
        } 
    } 

    // public function enqueue_scripts() {
    //     wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js?explicit&hl=' . get_locale(), array(), null, array('strategy' => 'defer'));
    // }

    public function validate_checkout_captcha() {
        $this->verify_checkout_captcha();
    }

    public function display_captcha() {
        if ($this->config->get_site_key_v2()) {
            if(is_checkout()){
                echo '<div id="wc-captcha-box"><div class="g-recaptcha" data-sitekey="' . esc_attr($this->config->get_site_key_v2()) . '"></div></div>';
            } else {
                echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($this->config->get_site_key_v2()) . '"></div>';
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
                'secret' => $this->config->get_secret_key_v2(),
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
                'secret' => $this->config->get_secret_key_v2(),
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
        let site_key = '<?php echo $this->config->get_site_key_v2(); ?>';
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

}