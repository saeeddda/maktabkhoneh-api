<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/Post.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $post = new Post(GetConnection());

    if(!isset($_POST['post_id']) && !isset($_POST['user_id'])){
        echo json_encode([
            'data' => 'file_user_required',
            'msg' => 'Post file and user id is required.',
            'success' => false
        ]);
        return;
    }

    $request = apache_request_headers();
    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $result = $post->LikeDislikePost($auth, $_POST['user_id'], $_POST['post_id']);
    if($result == 'post_id_require'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Post id and user id is required.',
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
    }else if($result == 'failed_like_post'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed to like/unlike post',
            'success' => false
        ]);
        return;
    }else if($result == 'post_liked'){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'msg' => 'Post liked',
            'success' => false
        ]);
        return;
    }else if($result == 'post_unliked'){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'msg' => 'Post unliked',
            'success' => true
        ]);
        return;
    }
}