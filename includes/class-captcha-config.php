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
    private $login_hide;

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

        $this->login_hide = get_option( 'jmb_captcha_hide_login', 0 );

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

    public function get_hide_login(): string {
        return $this->login_hide;
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

    public function verify_captcha($response) {

        // Verify nonce first
        if (!isset($_POST['jmb_recaptcha_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['jmb_recaptcha_nonce'])), 'jmb_recaptcha_action')) {
            return array(
                'status' => 'error',
                'message' => 'Something wrong try again.'
            );
        }

        if (empty($response)) {
            return array(
                'status' => 'error',
                'message' => 'Please complete the reCAPTCHA.'
            );
        }

        $response = sanitize_text_field($response);
        $remoteip = $_SERVER['REMOTE_ADDR'];

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
                'message' => 'reCAPTCHA failed. Please try again later'
            );
        }
    }

}
