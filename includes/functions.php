<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function ck_product($option){
    $output = ($option == 1 && is_product()) ? 'yes' : 'no';
    return $output;
}

function ck_single($option){
    $output = ($option == 1 && is_single()) ? 'yes' : 'no';
    return $output;
}