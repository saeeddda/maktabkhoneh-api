<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/sanitizer.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/Authentication.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $db = new Database();
    $authorize = new Authentication($db->GetConnection());

    $args =[
        'username' => isset($_POST['username']) && !empty($_POST['username']) ? strtolower(sanitize_strings($_POST['username'])) : '',
        'password' => $_POST['password'],
        'email' => isset($_POST['email']) && !empty($_POST['email']) ? strtolower(sanitize_strings($_POST['email'])) : '',
        'full_name' => isset($_POST['full_name']) && !empty($_POST['full_name']) ? $_POST['full_name'] : '',
        'phone' => isset($_POST['phone']) && !empty($_POST['phone']) ? $_POST['phone'] : '',
        'user_avatar' => isset($_FILES['user_avatar']) && !empty($_FILES['user_avatar']) ? $_FILES['user_avatar'] : '',
    ];

    $result = $authorize->Register($args);

    if($result == 'user_already_exist'){
        http_response_code(201);
        echo json_encode([
            'data' => $result,
            'msg' => 'User already exist',
            'success' => false,
        ]);
        return;
    }else if($result == 'failed_user_add'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed to add user',
            'success' => false,
        ]);
        return;
    }else if($result == 'username_or_email_required'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Username or email required',
            'success' => false,
        ]);
        return;
    }else if($result == true){
        http_response_code(200);
        echo json_encode([
            'data' => 'register_ok',
            'msg' => 'User register successful',
            'success' => true,
        ]);
        return;
    }
}