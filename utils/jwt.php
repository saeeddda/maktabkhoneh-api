<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';

class JWT_Util{
    public function Encode_JWT($payload){
        $key = JWT_PRIVATE_KEY;
        return Firebase\JWT\JWT::encode($payload,$key,'HS256');
    }

    public function Decode_JWT($jwt_encode){
        $key = JWT_PRIVATE_KEY;
        return Firebase\JWT\JWT::decode($jwt_encode,new Firebase\JWT\Key($key,'HS256'));
    }

    public function Validate_Token($auth_token, $userId){
        $payload = (array)$this->Decode_JWT($auth_token);

        if ($payload['asn'] != GetAppUrl())
            return 'token_not_valid';

        if($payload['act'] < time())
            return 'token_time_not_valid';

        if($payload['aet'] > GetExpireTime($payload['act']))
            return 'token_time_not_valid';

        if ($payload['uid'] != $userId) {
            return 'user_not_valid';
        }

        return true;
    }
}