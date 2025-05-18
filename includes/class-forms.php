<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class JMB_Captcha_Render {

    protected $config;

    public function __construct(JMB_Recaptcha_Config $config = null) {
        $this->config = $config ?: JMB_Recaptcha_Config::get_instance();
        add_action('init', [$this, 'load_options']);
    }

    public function load_options() {
        if($this->config->enable_v2() == 1){
            $this->init_v2();
        }
    }    

    public function init_v2() {
    
        if(!is_single()){
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
                if(!is_user_logged_in() || ($this->config->get_show_login() == '1' && is_user_logged_in())){
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

    public function validate_checkout_captcha() {
        $this->verify_checkout_captcha();
    }

    public function display_captcha() {
        if ($this->config->get_site_key_v2()) {

            if(is_checkout()){
                $captcha = '<div id="wc-captcha-box"><div class="g-recaptcha" data-sitekey="' . esc_attr($this->config->get_site_key_v2()) . '"></div></div>';
            } else {
                $captcha = '<div class="g-recaptcha" data-sitekey="' . esc_attr($this->config->get_site_key_v2()) . '"></div>';
            }

            echo wp_kses_post( wp_nonce_field( 'jmb_recaptcha_action', 'jmb_recaptcha_nonce' ));
            echo wp_kses_post( $captcha );

        }
    }

    public function validate_wplogin_captcha($user, $username, $password) {
        if(isset($_POST['g-recaptcha-response'])){
            $response = $this->config->verify_captcha($_POST['g-recaptcha-response']);
            if(isset($response['status'])){
                if ( $response['status'] == "error") {
                    return new WP_Error('captcha_invalid', $this->config->messages($response['message']));
                }
            }
        }
        return $user;
    }

    public function validate_wpregister_captcha($validation_error, $username, $password) {
        $response = $this->config->verify_captcha($_POST['g-recaptcha-response']);
        if(isset($response['status'])){
            if ( $response['status'] == "error") {
                $validation_error = new WP_Error('captcha_invalid', $this->config->messages($response['message']));
            }
        }
        return $validation_error;
    }

    public function validate_forgetpass_captcha($validation_errors, $user_data = ''){
        if(isset($_POST['woocommerce-lost-password-nonce'])) {
            $response = $this->config->verify_captcha($_POST['g-recaptcha-response']);
            if(isset($response['status'])){
                if ( $response['status'] == "error") {
                    //$validation_error = new WP_Error('Captcha Invalid', __($response['message']));
                    $validation_errors->add( 'captcha_invalid', $this->config->messages($response['message']) );
                }
            }
        }
    }

    public function validate_login_captcha($validation_error, $username, $password) {
        $response = $this->config->verify_captcha($_POST['g-recaptcha-response']);
        if(isset($response['status'])){
            if ( $response['status'] == "error") {
                $validation_error = new WP_Error('captcha_invalid', $this->config->messages($response['message']));
            }
        }
        return $validation_error;
    }

    public function validate_registration_captcha($validation_error, $username, $password, $email) {

        $response = $this->config->verify_captcha($_POST['g-recaptcha-response']);

        if(isset($response['status'])){
            if ( $response['status'] == "error") {
                $validation_error = new WP_Error('captcha_invalid', $this->config->messages($response['message']));
            }
        }
        return $validation_error;
    }

    public function verify_checkout_captcha() {
        
        $secret = $this->config->get_secret_key_v2();
        if (empty($secret)) {
            wc_add_notice($this->config->messages('config_invalid'), 'error');
            return;
        }

        if (!isset($_POST['jmb_recaptcha_nonce']) ||
            ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['jmb_recaptcha_nonce'])), 'jmb_recaptcha_action')) {
            wc_add_notice($this->config->messages('nonce_invalid'), 'error');
            return;
        }

        if (empty($_POST['g-recaptcha-response'])) {
            wc_add_notice($this->config->messages('captcha_required'), 'error');
            return;
        }

        $response = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) );
        
        $remoteip = '';
        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            $remoteip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
            if ( false === $remoteip ) {
                $remoteip = '';
            }
        }

        $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret,
                'response' => $response,
                'remoteip' => $remoteip
            ]
        ]);

        $result = json_decode(wp_remote_retrieve_body($verify));

        if (empty($result->success)) {
            wc_add_notice($this->config->messages('captcha_invalid'), 'error');
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