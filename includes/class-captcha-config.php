<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class JMB_Recaptcha_Config {
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

    private static $script_enqueued = false;


    private function __construct() {
        $this->enable_v2 = get_option('jmb_captcha_v2_status');
        $this->enable_v3 = get_option('jmb_captcha_v3_status');

        $this->site_key_v2 = esc_attr( get_option('jmb_captcha_site_key') );
        $this->secret_key_v2 = esc_attr( get_option('jmb_captcha_secret_key') );
        $this->site_key_v3 = esc_attr( get_option('jmb_captcha_site_v3_key') );
        $this->secret_key_v3 = esc_attr( get_option('jmb_captcha_secret_v3_key') );

        $this->wp_login = get_option( 'jmb_captcha_wp_login', 0 );
        $this->wp_register = get_option('jmb_captcha_wp_register', 0);
        $this->wp_forgetpass = get_option('jmb_captcha_wp_forget_pass', 0);
        $this->wp_comments = get_option('jmb_captcha_wp_comments', 0);

        $this->register = get_option( 'jmb_captcha_woo_register', 0 );
        $this->login = get_option( 'jmb_captcha_woo_login', 0 );
        $this->checkout = get_option( 'jmb_captcha_woo_checkout', 0 );
        $this->forgetpass = get_option( 'jmb_captcha_woo_forgetpass', 0 );
        $this->comments = get_option( 'jmb_captcha_woo_comments', 0 );

        $this->login_show = get_option( 'jmb_captcha_hide_login', 0 );

    }

    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function enable_v2(): string {
        return $this->enable_v2;
    }

    public function enable_v3(): string {
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

    public function get_wp_login(): string {
        return $this->wp_login;
    }

    public function get_wp_register(): string {
        return $this->wp_register;
    }

    public function get_wp_forgetpass(): string {
        return $this->wp_forgetpass;
    }

    public function get_wp_comments(): string {
        return $this->wp_comments;
    }

    public function get_wcc_login(): string {
        return $this->login;
    }

    public function get_wcc_register(): string {
        return $this->register;
    }

    public function get_wcc_forgetpass(): string {
        return $this->forgetpass;
    }

    public function get_wcc_comments(): string {
        return $this->comments;
    }

    public function get_wcc_checkout(): string {
        return $this->checkout;
    }

    public function get_show_login(): string {
        return $this->login_show;
    }

    public function maybe_enqueue_script() {
        if (!self::$script_enqueued) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_script']);
            self::$script_enqueued = true;
        }
    }

    public function enqueue_script() {
        wp_enqueue_script(
            'google-recaptcha',
            'https://www.google.com/recaptcha/api.js',
            [],
            JMB_CAPTCHA_VERSION,
            true
        );
    }

    public function messages($message){
        
        switch ( $message ) {
            case 'captcha_required':
                $output = __( '<b>Error: </b>Please complete the CAPTCHA.', 'jmb-captcha' );
                break;
            case 'captcha_invalid':
                $output = __( '<b>Error: </b>The CAPTCHA was incorrect. Please try again.', 'jmb-captcha' );
                break;
            case 'nonce_invalid':
                $output = __( '<b>Error: </b>Security check failed. Please refresh the page and try again.', 'jmb-captcha' );
                break;
            default:
                $output = __( '<b>Error: </b>An unknown CAPTCHA error occurred.', 'jmb-captcha' );
                break;
        }

        return $output;

    }

    public function verify_captcha() {

        // Verify nonce first
        if (!isset($_POST['jmb_recaptcha_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['jmb_recaptcha_nonce'])), 'jmb_recaptcha_action')) {
            return array(
                'status' => 'error',
                'message' => 'nonce_invalid'
            );
        }

        if(isset($_POST['g-recaptcha-response'])){
            
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
                    'secret' => $this->get_secret_key_v2(),
                    'response' => $response,
                    'remoteip' => $remoteip
                ]
            ]);

            $result = json_decode(wp_remote_retrieve_body($verify));

            if (empty($result->success)) {
                return array(
                    'status' => 'error',
                    'message' => 'captcha_invalid'
                );
            }
        }
    }

}
