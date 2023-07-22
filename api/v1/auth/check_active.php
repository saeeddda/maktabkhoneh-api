<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/Authentication.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
//    $request = apache_request_headers();

    $auth_controller = new Authentication(GetConnection());

//    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $userId = $_POST['user_id'];
//    $token = $_POST['token'];

    $result = $auth_controller->checkUserActivate($userId);

    if($result){
        http_response_code(200);
        echo json_encode([
            'data' => 'user_activated',
            'ms' => 'User activated',
            'success' => true,
        ]);
    }else{
        http_response_code(401);
        echo json_encode([
            'data' => 'user_not_activated',
            'ms' => 'User not activated!',
            'success' => false,
        ]);
    }
    return;
}
