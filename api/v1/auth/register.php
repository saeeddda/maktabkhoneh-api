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
        'user_avatar' => isset($_FILES['image']) && !empty($_FILES['image']) ? $_FILES['image'] : '',
    ];

    $result = $authorize->Register($args);

    if($result == 'user_already_exist'){
        echo json_encode([
            'data' => $result,
            'msg' => 'User already exist',
            'success' => false,
        ]);
        return;
    }else if($result == 'failed_user_add'){
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed to add user',
            'success' => false,
        ]);
        return;
    }else if($result == 'username_or_email_required'){
        echo json_encode([
            'data' => $result,
            'msg' => 'Username or email required',
            'success' => false,
        ]);
        return;
    }else if($result){
        echo json_encode([
            'data' => $result,
            'success' => true,
        ]);
        return;
    }
}