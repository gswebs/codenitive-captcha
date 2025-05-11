<?php
trait Recaptcha_Utils {
    public function ck_product($option){
        $output = ($option == 1 && is_product()) ? 'yes' : 'no';
        return $output;
    }
    
    public function ck_single($option){
        $output = ($option == 1 && is_single() && get_post_type() !== 'product') ? 'yes' : 'no';
        return $output;
    }

    public function ck_login_hide($option){
        $output = ($option == 1 && is_user_logged_in()) ? 'no' : 'yes';
        return $output;
    }

}