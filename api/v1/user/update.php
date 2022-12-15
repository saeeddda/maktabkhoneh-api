<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/sanitizer.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $request = apache_request_headers();

    $db = new Database();
    $user = new User($db->GetConnection());

    $args = [
        'username' => isset($_POST['username']) && !empty($_POST['username']) ? strtolower(sanitize_strings($_POST['username'])) : '',
        'password' => isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : '',
        'email' => isset($_POST['email']) && !empty($_POST['email']) ? sanitize_email($_POST['email']) : '',
        'full_name' => isset($_POST['full_name']) && !empty($_POST['full_name']) ? $_POST['full_name'] : '',
        'phone' => isset($_POST['phone']) && !empty($_POST['phone']) ? $_POST['phone'] : '',
        'user_avatar' => isset($_FILES['image']) && !empty($_FILES['image']) ? $_FILES['image'] : '',
    ];

    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ', '', $request['Authorization']) : '';

    $result = $user->EditUser($auth, sanitize_strings($_POST['user_id']), $args);

    if ($result == 'not_found') {
        http_response_code(404);
        echo json_encode([
            'data' => 'not_found',
            'msg' => 'User not found.',
            'success' => false
        ]);
        return;
    } else if ($result) {
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
            'msg' => 'User edited successfully',
            'success' => true
        ]);
        return;
    } else {
        http_response_code(400);
        echo json_encode([
            'data' => 'failed',
            'msg' => 'Failed to update user.',
            'success' => false
        ]);
        return;
    }
}