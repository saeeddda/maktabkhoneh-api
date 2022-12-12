<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $user = new User($db->GetConnection());

    $request = apache_request_headers();

    if(empty($_POST['email']) || empty($_POST['id']) || empty($_POST['username'])) {
        echo json_encode(['data' => 'parameter_not_valid', 'msg' => 'Parameter required!', 'success' => false]);
        return;
    }

    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $get_user_result = $user->GetUser();

    if(count($get_user_result) > 0) {
        http_response_code(200);
        echo json_encode([
            'data' => [
                'id' => $get_user_result['id'],
                'username' => $get_user_result['username'],
                'full_name' => $get_user_result['full_name'],
                'email' => $get_user_result['email'],
                'user_avatar' => $get_user_result['user_avatar'],
            ],
            'msg' => 'User added successfully',
            'success' => true
        ]);
    }else{
        http_response_code(501);
        echo json_encode([
            'data' => 'failed',
            'msg' => 'Failed to get user.',
            'success' => false
        ]);
    }
    return;
}