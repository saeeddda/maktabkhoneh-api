<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
    $db = new Database();
    $user = new User($db->GetConnection());

    $request = apache_request_headers();

    if(empty($_GET['username'])) {
        echo json_encode(['data' => 'parameter_not_valid', 'msg' => 'Parameter required!', 'success' => false]);
        return;
    }

    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $get_user_result = $user->GetUser($auth, $_GET['username']);

    if($get_user_result != null) {
        http_response_code(200);

        $user_data = array();

        foreach ($get_user_result as $user) {
            $user_data[] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'user_avatar' => $user['user_avatar'],
            ];
        }
        echo json_encode([
            'data' => $user_data,
            'msg' => 'Users data reached.',
            'success' => true
        ]);
    }else{
        http_response_code(501);
        echo json_encode([
            'data' => 'not_found',
            'msg' => 'User not found.',
            'success' => false
        ]);
    }
    return;
}