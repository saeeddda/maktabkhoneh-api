<?php

function sanitize_strings($string){
//    return filter_var($string, FILTER_SANITIZE_STRING);
    return htmlspecialchars($string);
}

function sanitize_email($email){
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

function sanitize_url($url){
    return filter_var($url, FILTER_SANITIZE_URL);
}