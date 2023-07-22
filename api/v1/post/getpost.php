<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/Post.php';

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $post = new Post(GetConnection());

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

    $result = $post->GetPost($auth,$_GET['user_id'],$_GET['post_id']);
    if($result == 'post_not_found'){
        http_response_code(404);
        echo json_encode([
            'data' => $result,
            'msg' => 'Post not found.',
            'success' => false
        ]);
        return;
    }else if($result == 'post_id_require'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Post id require',
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
    }else if($result != null){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'msg' => 'Single post',
            'success' => true
        ]);
        return;
    }
}