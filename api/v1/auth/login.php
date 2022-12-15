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

    $username = isset($_POST['username']) && !empty($_POST['username']) ? strtolower(sanitize_strings($_POST['username'])) : '';
    $email = isset($_POST['email']) && !empty($_POST['email']) ? strtolower(sanitize_strings($_POST['email'])) : '';
    $password = $_POST['password'];

    $result = $authorize->Login($username,$email, $password);

    if($result == 'username_or_password_false'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Username or password not valid',
            'success' => false,
        ]);
        return;
    }else if($result == 'failed_to_login'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed to user login',
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
    }else if($result == 'user_not_active'){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'msg' => 'User not active. Please active before login.',
            'success' => true,
        ]);
        return;
    }else if($result){
        http_response_code(200);
        echo json_encode([
            'auth_token' => $result,
            'success' => true,
        ]);
        return;
    }
}