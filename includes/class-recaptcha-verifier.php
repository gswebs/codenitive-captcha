<?php
namespace codenitcaptcha\includes;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists(__NAMESPACE__ . '\\CODENITCA_Captcha_Verifier')) {
    require_once CODENITCAPTCHA_PLUGIN_DIR_PATH . 'includes/class-captcha-verifier.php';
}
