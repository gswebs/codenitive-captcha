<?php
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

}
