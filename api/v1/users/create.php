<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

$db = new Database();

//echo $db->GetConnection();
//exit;

$user = new User($db->GetConnection());

//$json_data = json_decode(file_get_contents('php://input'));

if(empty($_POST['username']) && empty($_POST['password']) && empty($_POST['full_name']) && empty($_POST['email']))
    echo json_encode([
        'data'=>'Data not valid!',
        'success'=>false
    ]);

$args =[
    'username' => $_POST['username'],
    'password' => $_POST['password'],
    'full_name' => $_POST['full_name'],
    'email' => $_POST['email'],
];

$auth = isset($_POST['Authorization']) && !empty($_POST['Authorization']) ? str_replace('Bearer ','', $_POST['Authorization']) : '';

$result = $user->AddUser($auth, $args);

if($result) {
    http_response_code(200);
    echo json_encode([
        'data' => 'User added',
        'success' => true
    ]);
}else{
    http_response_code(501);
    echo json_encode([
        'data' => $result,
        'success' => false
    ]);
}
