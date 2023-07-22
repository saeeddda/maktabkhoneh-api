<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/Authentication.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $auth_controller = new Authentication(GetConnection());

    $userId = $_POST['user_id'];
    $verify_token = $_POST['verify_token'];

    $result = $auth_controller->resendActivationToken($userId, $verify_token);

    if($result == 'user_not_found'){
        http_response_code(404);
        echo json_encode([
            'data' => 'user_not_found',
            'ms' => 'User not found!',
            'success' => false,
        ]);
    }else if($result == 'verify_token_not_valid'){
        http_response_code(401);
        echo json_encode([
            'data' => 'verify_token_not_valid',
            'ms' => 'User verify token not valid!',
            'success' => false,
        ]);
    }else if($result == 'user_activated'){
        http_response_code(200);
        echo json_encode([
            'data' => 'user_activated',
            'ms' => 'User already activated',
            'success' => true,
        ]);
    }else if($result){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'ms' => 'Resend token successful',
            'success' => true,
        ]);

    }
    return;
}
