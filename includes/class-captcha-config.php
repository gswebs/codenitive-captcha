<?php
namespace codenitcaptcha\includes\config;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CODENITCA_Recaptcha_Config {
    private static $instance = null;
    private $enable_v2;
    private $enable_v3;
    private $site_key_v2;
    private $secret_key_v2;
    private $site_key_v3;
    private $secret_key_v3;
    private $wp_login;
    private $wp_register;
    private $wp_forgetpass;
    private $wp_comments;
    private $register;
    private $login;
    private $checkout;
    private $forgetpass;
    private $comments;
    private $login_show;
    private $cf7_forms;

    private $csrf_secret = '';

    private static $script_enqueued = false;


    private function __construct() {
        $this->enable_v2 = \get_option('codenitcaptcha_v2_status');
        $this->enable_v3 = \get_option('codenitcaptcha_v3_status');

        $this->site_key_v2 = \esc_attr( \get_option('codenitcaptcha_site_key') );
        $this->secret_key_v2 = \esc_attr( \get_option('codenitcaptcha_secret_key') );
        $this->site_key_v3 = \esc_attr( \get_option('codenitcaptcha_site_v3_key') );
        $this->secret_key_v3 = \esc_attr( \get_option('codenitcaptcha_secret_v3_key') );

        $this->wp_login = \get_option( 'codenitcaptcha_wp_login', 0 );
        $this->wp_register = \get_option('codenitcaptcha_wp_register', 0);
        $this->wp_forgetpass = \get_option('codenitcaptcha_wp_forget_pass', 0);
        $this->wp_comments = \get_option('codenitcaptcha_wp_comments', 0);

        if($this->check_active_plugin('woocommerce/woocommerce.php')){
            $this->register = \get_option( 'codenitcaptcha_woo_register', 0 );
            $this->login = \get_option( 'codenitcaptcha_woo_login', 0 );
            $this->checkout = \get_option( 'codenitcaptcha_woo_checkout', 0 );
            $this->forgetpass = \get_option( 'codenitcaptcha_woo_forgetpass', 0 );
            $this->comments = \get_option( 'codenitcaptcha_woo_comments', 0 );
        }
        
        if($this->check_active_plugin('contact-form-7/wp-contact-form-7.php')){
            $this->cf7_forms = \get_option('codenitcaptcha_cf7_forms', 0);
        }

        $this->login_show = \get_option( 'codenitcaptcha_hide_login', 0 );

    }

    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function enable_v2(): int {
        return $this->enable_v2;
    }

    public function enable_v3(): int {
        return $this->enable_v3;
    }

    public function get_site_key_v2(): string {
        return $this->site_key_v2;
    }

    public function get_secret_key_v2(): string {
        return $this->secret_key_v2;
    }

    public function get_site_key_v3(): string {
        return $this->site_key_v3;
    }

    public function get_secret_key_v3(): string {
        return $this->secret_key_v3;
    }

    public function get_wp_login(): int {
        return $this->wp_login;
    }

    public function get_wp_register(): int {
        return $this->wp_register;
    }

    public function get_wp_forgetpass(): int {
        return $this->wp_forgetpass;
    }

    public function get_wp_comments(): int {
        return $this->wp_comments;
    }

    public function get_wcc_login(): int {
        return $this->login;
    }

    public function get_wcc_register(): int {
        return $this->register;
    }

    public function get_wcc_forgetpass(): int {
        return $this->forgetpass;
    }

    public function get_wcc_comments(): int {
        return $this->comments;
    }

    public function get_wcc_checkout(): int {
        return $this->checkout;
    }

    public function get_show_login(): int {
        return $this->login_show;
    }

    public function get_cf7_option(): int {
        return $this->cf7_forms;
    }

    public function check_active_plugin($root): bool {
        $return = false;
        $active_plugins = \apply_filters('active_plugins', \get_option( 'active_plugins', array() ));
        // Check if plugin is active
        if ( in_array( $root, $active_plugins ) ) {
            $return = true;
        }

        return $return;
    }

    public function maybe_enqueue_script() {
        if (!self::$script_enqueued) {
            \add_action('wp_enqueue_scripts', [$this, 'enqueue_script'], 999);
            self::$script_enqueued = true;
        }
    }

    // public function enqueue_script() {
    //     \wp_enqueue_script(
    //         'google-recaptcha',
    //         'https://www.google.com/recaptcha/api.js',
    //         [],
    //         '10.0.6',
    //         true
    //     );
    // }

    public function enqueue_script() {
        if ($this->get_site_key_v2() && !is_admin()) {

            $source = 'google.com';
            $url = \sprintf( 'https://www.%s/recaptcha/api.js', $source );

            $url = \add_query_arg( array(
                //'hl'		=> esc_attr( \apply_filters( 'wpcf7_recaptcha_locale', \get_locale() ) ),	// Lowercase L
                'onload'	=> 'recaptchaCallback',
                'render' 	=> 'explicit',
            ), $url );

            \wp_enqueue_script( 'codenitcaptcha-recaptcha-js', CODENITCAPTCHA_PLUGIN_DIR_URL . 'assets/js/scripts.js', array(), CODENITCAPTCHA_VERSION, true );
            \wp_enqueue_script( 'google-recaptcha', $url, array( 'codenitcaptcha-recaptcha-js' ), CODENITCAPTCHA_VERSION, true ); 

            \wp_localize_script('codenitcaptcha-recaptcha-js', 'CodenitCaptchaData', [
                'siteKey' => $this->get_site_key_v2(),
            ]);

        }

    }

    public function messages($message){
        
        switch ( $message ) {
            case 'captcha_required':
                $output = __( 'The CAPTCHA is required. Please try again.', 'codenitive-captcha' );
                break;
            case 'captcha_invalid':
                $output = __( 'The CAPTCHA was invalid. Please try again.', 'codenitive-captcha' );
                break;
            case 'nonce_invalid':
                $output = __( 'Security check failed. Please refresh the page and try again.', 'codenitive-captcha' );
                break;
            case 'verify_invalid':
                $output = __( 'Verification check failed. Please refresh the page and try again.', 'codenitive-captcha' );
                break;
            case 'config_invalid':
                $output = __( 'Something wrong try again later.', 'codenitive-captcha' );
                break;
            default:
                $output = __( 'An unknown CAPTCHA error occurred.', 'codenitive-captcha' );
                break;
        }

        return \apply_filters('codenitcaptcha_messages', $output, $message);

    }

    public function verify_captcha() {

        $secret = $this->get_secret_key_v2();
        if (empty($secret)) {
            return array(
                'status' => 'error',
                'message' => 'config_invalid'
            );
        }

        // Verify nonce first
        if(isset($_POST['wooregister']) && $_POST['wooregister'] == '_codenitcaptcha_nonce_wcc_register') {
            if (!isset($_POST['codenitcaptcha_nonce_woo_register']) ||
                !\wp_verify_nonce(\sanitize_text_field(\wp_unslash($_POST['codenitcaptcha_nonce_woo_register'])), 'codenitcaptcha_action_woo_register')) {
                return array(
                    'status' => 'error',
                    'message' => 'nonce_invalid'
                );
            }
        } else {
            if (!isset($_POST['codenitcaptcha_nonce']) ||
                !\wp_verify_nonce(\sanitize_text_field(\wp_unslash($_POST['codenitcaptcha_nonce'])), 'codenitcaptcha_action')) {
                return array(
                    'status' => 'error',
                    'message' => 'nonce_invalid'
                );
            }
        }

        //error_log(print_r($_POST['g-recaptcha-response'], true));
        
        if (!isset($_POST['g-recaptcha-response'])) {
            return array(
                'status' => 'error',
                'message' => 'captcha_required'
            );
        }
        
        $response = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) );
        
        if (empty($response)) {
            return array(
                'status' => 'error',
                'message' => 'captcha_required'
            );
        }

        $remoteip = '';

        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            $remoteip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );

            // If it's not a valid IP, fall back to empty string
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

        if (is_wp_error($verify)) {
            return array(
                'status' => 'error',
                'message' => 'verify_invalid'
            );
        }

        $result = json_decode(wp_remote_retrieve_body($verify));
        
        //error_log(print_r($result, true));
        
        if (empty($result->success)) {
            return array(
                'status' => 'error',
                'message' => 'captcha_invalid'
            );
        }
    }

}
