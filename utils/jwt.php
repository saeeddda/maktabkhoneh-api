<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';

class JWT_Util{
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

    public function Validate_Token($auth_token){
        $payload = (array)$this->Decode_JWT($auth_token);

        if ($payload['asn'] != get_site_url())
            return new Firebase\JWT\SignatureInvalidException('Authorize key not valid!');

        if($payload['act'] < time())
            return new Firebase\JWT\BeforeValidException('Authorize dat not valid!');

        if($payload['aet'] > $this->GetExpireTime($payload['act']))
            return new Firebase\JWT\ExpiredException('Authorize code has bin expired!');

        if (empty($args['username']) && empty($args['password']) && empty($args['email'])) {
            return new Http\Exception\BadQueryStringException('some data not sent.');
        }

        return true;
    }
}