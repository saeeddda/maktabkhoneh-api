<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/sanitizer.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $db = new Database();
    $user = new User($db->GetConnection());

    $request = apache_request_headers();

    if(empty($_POST['username']) && empty($_POST['password']) && empty($_POST['full_name']) && empty($_POST['email'])) {
        echo json_encode(['data' => 'data_not_valid', 'msg' => 'Data not valid!', 'success' => false]);
        return;
    }

    $args =[
        'username' => sanitize_strings($_POST['username']),
        'password' => $_POST['password'],
        'full_name' => $_POST['full_name'],
        'user_avatar' => $_FILES['image'],
        'email' => sanitize_email($_POST['email']),
    ];

    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $result = $user->AddUser($auth, $args);
    if($result == 'user_added') {
        http_response_code(200);
        echo json_encode([
            'data' => 'user_added',
            'msg' => 'User added successfully',
            'success' => true
        ]);
    }else if($result == 'user_already_exist'){
        http_response_code(200);
        echo json_encode([
            'data' => 'user_exist',
            'msg' => 'User already exist.',
            'success' => false
        ]);
        return;
    }else{
        http_response_code(501);
        echo json_encode([
            'data' => 'failed',
            'msg' => 'Failed to add user.',
            'success' => false
        ]);
    }
    return;
}