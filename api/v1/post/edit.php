<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/Post.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $db = new Database();
    $post = new Post($db->GetConnection());

    if(!isset($_POST['user_id']) && !isset($_POST['post_id'])){
        echo json_encode([
            'data' => 'file_user_required',
            'msg' => 'Post id and user id is required.',
            'success' => false
        ]);
        return;
    }

    $postFile = isset($_FILES['post_file']) && !empty($_FILES['post_file']) ? $_FILES['post_file'] : '';
    $postContent = isset($_POST['content']) && !empty($_POST['content']) ? $_POST['content'] : '';

    $request = apache_request_headers();
    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $result = $post->EditPost($auth,$_POST['user_id'] , $_POST['post_id'], $postFile, $postContent);
    if($result == 'user_not_found'){
        http_response_code(404);
        echo json_encode([
            'data' => $result,
            'msg' => 'User not found',
            'success' => false
        ]);
        return;
    }else if($result == 'post_not_found'){
        http_response_code(404);
        echo json_encode([
            'data' => $result,
            'msg' => 'Post not found',
            'success' => false
        ]);
        return;
    }else if($result == 'token_not_valid'){
        http_response_code(401);
        echo json_encode([
            'data' => $result,
            'msg' => 'Authorization token not valid',
            'success' => false
        ]);
        return;
    }else if($result == 'failed_post_edit'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed to edit post',
            'success' => false
        ]);
        return;
    }else if($result){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'msg' => 'Post edited',
            'success' => true
        ]);
        return;
    }
}