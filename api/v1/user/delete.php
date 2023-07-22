<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $request = apache_request_headers();

    $user = new User(GetConnection());

    $deleteId = $_POST['delete_id'];

    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $result = $user->DeleteUser($auth, $deleteId);

    if($result) {
        http_response_code(200);
        echo json_encode([
            'data' => 'user_deleted',
            'msg' => 'User edited successfully',
            'success' => true
        ]);
        return;
    }else{
        http_response_code(400);
        echo json_encode([
            'data' => 'failed',
            'msg' => 'Failed to delete user.',
            'success' => false
        ]);
        return;
    }
}