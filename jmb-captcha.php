<?php
/*
* Plugin Name: JMB CAPTCHA
* Description: Adds CAPTCHA to WooCommerce and WordPress.
* Version: 0.1.0
* Author: Gurjit Singh
* Text Domain: jmb-captcha
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');

define( 'JMB_CAPTCHA_VERSION', $plugin_data['version'] );

/** 
 * Google reCAPTCHA: Add widget before the submit button 
 */ 
// function add_google_recaptcha($submit_field) { 
//     $submit_field['submit_field'] = '<div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>'.$submit_field['submit_field']; 
//     return $submit_field; 
// } 
 

 
// /** 
//  * Google reCAPTCHA: verify response and validate comment submission 
//  */ 
// function is_valid_captcha_response($captcha) { 
//     $captcha_postdata = http_build_query( 
//         array( 
//             'secret' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe', 
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
 



// function verify_google_recaptcha() { 
//     $recaptcha = $_POST['g-recaptcha-response']; 
//     if(empty($recaptcha)){ 
//         wp_die(__("<b>ERROR: </b><b>Please click the captcha checkbox.</b><p><a href='javascript:history.back()'>« Back</a></p>")); 
//     }elseif(!is_valid_captcha_response($recaptcha)){ 
//         wp_die(__("<b>Sorry, spam detected!</b>")); 
//     } 
// } 


// function jmb_inert_coupons()
// {
//     if (!is_user_logged_in()) { 
//         add_filter('comment_form_defaults', 'add_google_recaptcha'); 
//     } 
//     if (!is_user_logged_in()) { 
//         add_action('pre_comment_on_post', 'verify_google_recaptcha'); 
//     }
// }
// add_action('init', 'jmb_inert_coupons');
// Initialize the plugin
function jmb_wc_captcha_settings_load() {
    
    require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
    JMB_WC_Captcha_Settings::init();

}
add_action('plugins_loaded', 'jmb_wc_captcha_settings_load');

function jmb_wc_captcha_load() {

    require_once plugin_dir_path(__FILE__) . 'includes/forms.php';

    new JMB_WC_Captcha_Forms();
        
}
add_action('wp', 'jmb_wc_captcha_load');

function jmb_wc_comments_captcha_load() {

    require_once plugin_dir_path(__FILE__) . 'includes/comments-captcha.php';

    new JMB_WC_Comments_Captcha();
        
}
add_action('init', 'jmb_wc_comments_captcha_load');