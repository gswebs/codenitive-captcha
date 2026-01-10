<?php
namespace codenitcaptcha\includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('CODENITCA_Captcha_CSRF')) {
    class CODENITCA_Captcha_CSRF{
        public static function activate() {
            self::update_csrf_secret(); // or you can use a helper method outside
        }

        public static function update_csrf_secret() {
            $secret = bin2hex(random_bytes(32));
            \update_option('codenitcaptcha_csrf_secret', $secret, false); // don't autoload
        }

        public static function get_csrf_secret() {
            return \get_option('codenitcaptcha_csrf_secret'); // don't autoload
        }

        /**
         * Generate a stateless CSRF token.
         *
         * @param int $ttl Time-to-live in seconds (default: 3600 = 1 hour)
         * @return array {
         *     @type string $time  Timestamp
         *     @type string $token HMAC token
         * }
         */
        private static function csrf_generate_token($ttl) {
            $time = time();
            $token = hash_hmac('sha256', $time, self::get_csrf_secret());
            return [
                'csrf_time'  => $time,
                'csrf_token' => $token,
            ];
        }

        public static function get_csrf_token($ttl = 3600){
            return self::csrf_generate_token($ttl);
        }

        /**
         * Validate a stateless CSRF token.
         *
         * @param string $token The token from the form
         * @param string|int $time The timestamp from the form
         * @param int $ttl Time-to-live in seconds (default: 3600 = 1 hour)
         * @return bool True if valid, false otherwise
         */
        private static function csrf_validate_token($token, $time, $ttl = 3600) {
            if (!is_numeric($time)) return false;
            $expected = hash_hmac('sha256', $time, self::get_csrf_secret());
            $ttl = \apply_filters('codenitcaptcha_ttl', $ttl);
            return hash_equals($expected, $token) && (time() - (int)$time <= $ttl);
        }

        public static function verify_csrf_token($token, $time, $ttl = 3600) {
            return self::csrf_validate_token($token, $time, $ttl);
        }
        
    }
}