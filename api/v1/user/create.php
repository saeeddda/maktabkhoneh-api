<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/sanitizer.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $user = new User(GetConnection());

    $request = apache_request_headers();

    if(empty($_POST['username']) && empty($_POST['password']) && empty($_POST['email'])) {
        echo json_encode(['data' => 'data_not_valid', 'msg' => 'Data not valid!', 'success' => false]);
        return;
    }

    $args =[
        'username' => strtolower(sanitize_strings($_POST['username'])),
        'password' => $_POST['password'],
        'email' => sanitize_email($_POST['email']),
        'full_name' => isset($_POST['full_name']) && !empty($_POST['full_name']) ? $_POST['full_name'] : '',
        'phone' => isset($_POST['phone']) && !empty($_POST['phone']) ? $_POST['phone'] : '',
        'user_avatar' => isset($_FILES['image']) && !empty($_FILES['image']) ? $_FILES['image'] : '',
    ];

    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $result = $user->AddUser($auth, $args);

    if($result) {
        http_response_code(200);
        echo json_encode([
            'data' => [
                'id' => $result['id'],
                'username' => $result['username'],
                'full_name' => $result['full_name'],
                'email' => $result['email'],
                'user_avatar' => $result['user_avatar'],
                'phone' => $result['phone'],
            ],
            'msg' => 'User added successfully',
            'success' => true
        ]);
        return;
    }else if($result == 'user_already_exist'){
        http_response_code(200);
        echo json_encode([
            'data' => 'user_exist',
            'msg' => 'User already exist.',
            'success' => false
        ]);
        return;
    }else{
        http_response_code(400);
        echo json_encode([
            'data' => 'failed',
            'msg' => 'Failed to add user.',
            'success' => false
        ]);
        return;
    }
}