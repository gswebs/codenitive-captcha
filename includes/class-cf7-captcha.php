<?php
namespace codenitcaptcha\includes;

use \codenitcaptcha\includes\config\CODENITCA_Recaptcha_Config;

use \codenitcaptcha\includes\CODENITCA_Captcha_CSRF;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('CODENITCA_Captcha_CF7_Render')) {

    class CODENITCA_Captcha_CF7_Render {
        
        private static $instance = null;

        private $site_key;

        private $secret_key;

        private $csrf_token;
        private $csrf_token_verify;

        private $config;

        private $assets;

        private $verifier;

        private static $script_enqueued = false;

        public static function get_instance() {

            if (null === self::$instance) {

                self::$instance = new self();

            }

            return self::$instance;

        }

        private function __construct() {

            $this->config = CODENITCA_Recaptcha_Config::get_instance();

            $this->assets = new CODENITCA_Captcha_Assets($this->config);

            $this->verifier = new CODENITCA_Captcha_Verifier($this->config);

            // Get keys from options
            $this->site_key = ('turnstile' === $this->config->get_active_provider()) ? $this->config->get_turnstile_site_key() : $this->config->get_site_key_v2();

            $this->secret_key = ('turnstile' === $this->config->get_active_provider()) ? $this->config->get_turnstile_secret_key() : $this->config->get_secret_key_v2();

            if ( $this->config->get_cf7_option() == 1 ) {
                $this->assets->maybe_enqueue_captcha();
                // Add hooks    
                if(!is_user_logged_in() || ($this->config->get_show_login() == 1 && is_user_logged_in())){
                    \add_action( 'wp_loaded', array($this, 'init_hooks'), 15 );
                    \add_action( 'wpcf7_init', array($this, 'add_recaptcha_tag'), 20);
                    \add_action( 'wpcf7_init', array($this, 'plugins_loaded_hooks'), 15 );
                }
            }

        }

        public function init_hooks(){
            if(!is_user_logged_in() || ($this->config->get_show_login() == 1 && is_user_logged_in())){
                \add_filter('wpcf7_validate', array($this, 'validate_nonce'), 15, 2);
            }
        }

        public function plugins_loaded_hooks(){

            \add_action( 'setup_theme', array($this, 'wpcf7_manage_hooks') );

            if( !is_user_logged_in() || ( $this->config->get_show_login() == 1 && is_user_logged_in() ) ) {
                \add_filter('wpcf7_validate_codenit_recaptcha', array($this, 'verify_recaptcha'), 20, 2);
            }

            \add_filter( 'wpcf7_form_tag', array($this, 'recaptcha_tag_name') );
            
        }

        public function is_active() {
			$sitekey = $this->site_key;
			$secret = $this->secret_key;
			return $sitekey && $secret;
		}

        public function wpcf7_manage_hooks() {
             // reCaptcha Verification
            \remove_filter( 'wpcf7_spam', 'wpcf7_recaptcha_verify_response', 9 );
            \add_filter( 'wpcf7_spam', array($this, 'wpcf7_recaptcha_check_with_google'), 9 );
            // reCaptcha Enqueues
            \remove_action( 'wp_enqueue_scripts', 'wpcf7_recaptcha_enqueue_scripts', 20 );
            // reCaptcha Footer Javascript
            \remove_action( 'wp_footer', 'wpcf7_recaptcha_onload_script', 40 );
        }

        public function add_recaptcha_tag() {
            if (function_exists('wpcf7_add_form_tag')) {
                \wpcf7_remove_form_tag( 'codenit_recaptcha' );
                \wpcf7_add_form_tag('codenit_recaptcha', array($this, 'recaptcha_tag_handler'), array( 'display-block' => true));
            }
        }

        public function recaptcha_tag_name( $tag ) {
            if( empty( $tag['name'] ) && 'codenit_recaptcha' === $tag['type'] ) {
                $tag['name'] = 'codenit_recaptcha';
            }
            return $tag;
        }

        public function recaptcha_tag_handler($tag) {

            $html = '';

            if(!is_user_logged_in() || ($this->config->get_show_login() == 1 && is_user_logged_in())) {
                $atts = array();
                
                $this->csrf_token = CODENITCA_Captcha_CSRF::get_csrf_token();

                $this->assets->enqueue_captcha();

                $atts['data-sitekey'] = $this->site_key;
        
                $captcha_class = ('turnstile' === $this->config->get_active_provider()) ? 'cf-turnstile codenitcaptcha-turnstile' : 'g-recaptcha codenitcaptcha-recaptcha';
                $atts['class'] = $tag->get_class_option(
                    \wpcf7_form_controls_class( $tag->type, $captcha_class )
                );

                //$atts['id'] = $tag->get_id_option();

                $html = \sprintf( '<span %1$s></span>', wpcf7_format_atts( $atts ) );
                $html_nonce = '<span class="wpcf7-form-control-wrap codenitcaptcha_nonce-wrap">
                    <input type="hidden" name="csrf_time" value="'.esc_attr($this->csrf_token['csrf_time']).'" />
                    <input type="hidden" name="csrf_token" value="'.esc_attr($this->csrf_token['csrf_token']).'" />
                </span>';
                if ('recaptcha' === $this->config->get_active_provider()) {
                    $html .= $this->recaptcha_noscript(
                        array( 'sitekey' => $atts['data-sitekey'] )
                    );
                }

                $html = \sprintf(  '<span class="wpcf7-form-control-wrap codenit_recaptcha" data-name="codenit_recaptcha">%1$s</span>%2$s', $html, $html_nonce );
            }

            return $html;

        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- USING CUSTOM NONCE INSTEAD OF WORDPRESS NONCE FUNCTIONS
        private function wpcf7_recaptcha_response() {

            $nonce = $this->get_nonce_response();
            $this->csrf_token_verify = CODENITCA_Captcha_CSRF::verify_csrf_token($nonce[1], $nonce[0]);
            if ( ! $this->csrf_token_verify ) {
                return false;
            }

            if ('turnstile' === $this->config->get_active_provider() && isset($_POST['cf-turnstile-response']) ) {
                return \sanitize_text_field( \wp_unslash( $_POST['cf-turnstile-response'] ) );
            }

            if ( isset($_POST['g-recaptcha-response']) ) {
                return \sanitize_text_field( \wp_unslash( $_POST['g-recaptcha-response'] ) );
            }

            return false;

        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        /**
         * Retrieves the nonce value from the POST data.
         */

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- This function only retrieves the nonce; it's verified later in validate_nonce()
        private function get_nonce_response() {
            if (isset($_POST['csrf_time']) && isset($_POST['csrf_token'])) {
                
                $csrf_time = \sanitize_text_field(\wp_unslash($_POST['csrf_time']));
                $csrf_token = \sanitize_text_field(\wp_unslash($_POST['csrf_token']));

                return [$csrf_time, $csrf_token];

            }
            return false;
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        public function wpcf7_recaptcha_check_with_google( $spam ) {

            if ( $spam ) {
                return $spam;
            }

            $contact_form = \wpcf7_get_current_contact_form();

            if ( ! $contact_form ) {
                return $spam;
            }

            $tags = $contact_form->scan_form_tags( array( 'type' => 'codenit_recaptcha' ) );

            if ( empty( $tags ) ) {
                return $spam;
            }

            if( ! $this->is_active() ) {
                return $spam;
            }

            $nonce_token = $this->get_nonce_response();

            $response_token = $this->wpcf7_recaptcha_response();

            $spam = ! $this->verify( $nonce_token, $response_token );

            return $spam;

        }

        /**
        * Display reCaptcha noscript tag should javacript be disabled.
        */
        public function recaptcha_noscript( $args = '' ) {

            $args = \wp_parse_args( $args, array(
                'sitekey' => '',
            ) );
            if ( empty( $args['sitekey'] ) ) {
                return;
            }

            $source = 'google.com';
            $url 	= \add_query_arg( 'k', $args['sitekey'],
                sprintf( 'https://www.%s/recaptcha/api/fallback', $source )
            );
            ob_start();
            ?>
            <noscript>
                <div class="grecaptcha-noscript">
                    <iframe src="<?php echo esc_url( $url ); ?>" frameborder="0" scrolling="no" width="310" height="430">
                    </iframe>
                    <textarea name="g-recaptcha-response" rows="3" cols="40" placeholder="<?php esc_attr_e( 'reCaptcha Response Here', 'codenitive-captcha' ); ?>">
                    </textarea>
                </div>
            </noscript>
            <?php
            return ob_get_clean();

        }

        /**
         * Validates the security nonce. Hooked to wpcf7_validate.
         */
        public function validate_nonce($result, $tags) {
            // Only run if our reCAPTCHA tag is in the form.
            $contact_form = \wpcf7_get_current_contact_form();
            if (!$contact_form) return $result;
            $recaptcha_tags = $contact_form->scan_form_tags(array('type' => 'codenit_recaptcha'));
            if (empty($recaptcha_tags)) {
                return $result;
            }

            $nonce = $this->get_nonce_response();

            $this->csrf_token_verify = CODENITCA_Captcha_CSRF::verify_csrf_token($nonce[1], $nonce[0]);

            if ( ! $this->csrf_token_verify ) {
                $result->invalidate(
                    $recaptcha_tags[0], 
                    $this->config->messages('nonce_invalid')
                );
            }

            return $result;
        }

        public function verify_recaptcha($result, $tag) {
            if ( empty($this->site_key) || empty($this->secret_key)) {
                return $result;
            }

            if( empty( $tag->name ) ) {
                $tag->name = 'codenit_recaptcha';
            }

            $response_field = ('turnstile' === $this->config->get_active_provider()) ? 'cf-turnstile-response' : 'g-recaptcha-response';

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in validate_nonce()
            if ( !isset( $_POST[$response_field] ) ) {
                $result->invalidate(
                    $tag,
                    ($this->config->messages('config_invalid') )
                );
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in validate_nonce()
            else if ( empty( $_POST[$response_field] ) ) {
                $result->invalidate(
                    $tag,
                    ($this->config->messages('captcha_invalid'))
                );
            }

            return $result;
        }

        public function verify( $nonce_token, $response_token ) {

            if ( empty( $nonce_token ) || empty( $response_token ) ) {
                return false;
            }

            $is_human = $this->verifier->verify_token(
                $response_token,
                $this->secret_key
            );

            return apply_filters(
                'codenitcaptcha_wpcf7_recaptcha_verify_response',
                $is_human,
                array(
                    'success' => $is_human,
                )
            );
        }

    }
}