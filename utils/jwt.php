<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';

class JWT_Util
{
    public function Encode_JWT($payload){
        $key = JWT_PRIVATE_KEY;
        return Firebase\JWT\JWT::encode($payload,$key,'HS256');
    }

    public function Decode_JWT($jwt_encode){
        $key = JWT_PRIVATE_KEY;
        return Firebase\JWT\JWT::decode($jwt_encode,new Firebase\JWT\Key($key,'HS256'));
    }

    public function GetExpireTime($create_time, $expire_day = 7){
        return $create_time + (86400 * intval($expire_day));
    }
}