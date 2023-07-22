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
    $active_token = $_POST['active_token'];

    $result = $auth_controller->verifyUserToken($userId, $verify_token, $active_token);

    if($result == 'user_active_successful'){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'ms' => 'User active successfully',
            'success' => true,
        ]);
        return;
    }else if($result == 'token_not_valid'){
        http_response_code(401);
        echo json_encode([
            'data' => $result,
            'ms' => 'User token not valid!',
            'success' => false,
        ]);
        return;
    }else if($result == 'failed_user_active'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'ms' => 'User validation failed',
            'success' => false,
        ]);
        return;
    }else{
        http_response_code(404);
        echo json_encode([
            'data' => $result,
            'ms' => 'User not found or not active',
            'success' => false,
        ]);
        return;
    }
}
