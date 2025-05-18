<?php
trait Recaptcha_Utils {
    public function ck_product($option){
        global $post;
        $output = ($option == 1 && is_singular('product')) ? 'yes' : 'no';
        return $output;
    }

    
    public function ck_single($option){
        global $post;
        $output = ($option == 1 && is_single() && !is_singular('product')) ? 'yes' : 'no';
        return $output;
    }

    public function ck_login_show($option){
        $output = ($option == 1 && is_user_logged_in()) ? 'yes' : 'no';
        return $output;
    }

}