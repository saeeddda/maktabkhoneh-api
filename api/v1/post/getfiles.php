<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/Story.php';

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $db = new Database();
    $post = new Post($db->GetConnection());

    if(!isset($_GET['user_id']) && !isset($_GET['post_id'])){
        echo json_encode([
            'data' => 'user_story_required',
            'msg' => 'User id and post id is required.',
            'success' => false
        ]);
        return;
    }

    $request = apache_request_headers();
    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $post_result = $post->GetFiles($auth,$_GET['user_id'],$_GET['post_id'] );

    if($post_result == 'files_not_found'){
        http_response_code(400);
        echo json_encode([
            'data' => $post_result,
            'msg' => 'Files not found',
            'success' => false
        ]);
        return;
    }else if($post_result == 'token_not_valid'){
        http_response_code(401);
        echo json_encode([
            'data' => $post_result,
            'msg' => 'Authorization token not valid',
            'success' => false
        ]);
        return;
    }else if($post_result){
        http_response_code(200);
        echo json_encode([
            'data' => $post_result,
            'msg' => 'Post files',
            'success' => true
        ]);
        return;
    }
}