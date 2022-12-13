<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/sanitizer.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

if($_SERVER['REQUEST_METHOD'] == 'DELETE'){

    parse_str(file_get_contents('php://input'),$inputData);
    foreach ($inputData as $data){
        echo $data . ' -> ';
    }
    return;
    $request = apache_request_headers();

    $db = new Database();
    $user = new User($db->GetConnection());

    $args =[
        'username' => isset($inputData['username']) && !empty($inputData['username']) ? strtolower(sanitize_strings($inputData['username'])) : '',
        'password' => isset($inputData['password']) && !empty($inputData['password']) ? $inputData['password'] : '',
        'email' => isset($inputData['email']) && !empty($inputData['email']) ? sanitize_email($inputData['email']) : '',
        'full_name' => isset($inputData['full_name']) && !empty($inputData['full_name']) ? $inputData['full_name'] : '',
        'phone' => isset($inputData['phone']) && !empty($inputData['phone']) ? $inputData['phone'] : '',
        'user_avatar' => isset($_FILES['image']) && !empty($_FILES['image']) ? $_FILES['image'] : '',
    ];

    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $result = $user->EditUser($auth, sanitize_strings($_GET['id']), $args);

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
            'msg' => 'User edited successfully',
            'success' => true
        ]);
        return;
    }else{
        http_response_code(501);
        echo json_encode([
            'data' => 'failed',
            'msg' => 'Failed to add user.',
            'success' => false
        ]);
        return;
    }
}