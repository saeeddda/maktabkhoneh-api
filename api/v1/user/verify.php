<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $request = apache_request_headers();

    $db = new Database();
    $user = new User($db->GetConnection());

    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $userId = $_POST['user_id'];
    $token = $_POST['token'];

    $result = $user->VerifyUserToken($userId,$token);

    if($result == 'user_active_successful'){
        echo json_encode([
            'data' => $result,
            'ms' => 'User active successfully',
            'success' => true,
        ]);
        return;
    }else if($result == 'token_not_valid'){
        echo json_encode([
            'data' => $result,
            'ms' => 'User token not valid!',
            'success' => false,
        ]);
        return;
    }else if($result == 'failed_user_active'){
        echo json_encode([
            'data' => $result,
            'ms' => 'User validation failed',
            'success' => false,
        ]);
        return;
    }else{
        echo json_encode([
            'data' => $result,
            'ms' => 'User not found or active',
            'success' => false,
        ]);
        return;
    }
}
