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

    if(!isset($_FILES['post_file']) && !isset($_POST['user_id'])){
        echo json_encode([
            'data' => 'file_user_required',
            'msg' => 'Post file and user id is required.',
            'success' => false
        ]);
        return;
    }

    $request = apache_request_headers();
    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $post_file = $_FILES['post_file'];
    $user_id = $_POST['user_id'];
    $content =  isset($_POST['content']) && !empty($_POST['content']) ? $_POST['content'] : '';

    $result = $post->AddPost($auth,$user_id, $post_file, $content);
    if($result == 'file_user_required'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Story file and user id is required.',
            'success' => false
        ]);
        return;
    }else if($result == 'user_not_found'){
        http_response_code(404);
        echo json_encode([
            'data' => $result,
            'msg' => 'User not found',
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
    }else if($result == 'failed_post_add'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed to add post',
            'success' => false
        ]);
        return;
    }else if($result){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'msg' => 'Post added',
            'success' => true
        ]);
        return;
    }
}