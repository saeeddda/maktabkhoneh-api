<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

if($_SERVER['REQUEST_METHOD'] == 'DELETE'){

    $request = apache_request_headers();

    $db = new Database();
    $user = new User($db->GetConnection());

    $userId = $_GET['user_id'];

    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $result = $user->DeleteUser($auth,$userId);

    if($result) {
        http_response_code(200);
        echo json_encode([
            'data' => 'user_deleted',
            'msg' => 'User edited successfully',
            'success' => true
        ]);
        return;
    }else{
        http_response_code(501);
        echo json_encode([
            'data' => 'failed',
            'msg' => 'Failed to delete user.',
            'success' => false
        ]);
        return;
    }
}