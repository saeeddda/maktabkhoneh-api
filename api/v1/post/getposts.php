<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/Post.php';

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $db = new Database();
    $post = new Post($db->GetConnection());

    if(!isset($_GET['user_id'])){
        echo json_encode([
            'data' => 'user_required',
            'msg' => 'Your user id is required.',
            'success' => false
        ]);
        return;
    }

    $randomStory = isset($_GET['random']) ? $_GET['random'] : false;

    $request = apache_request_headers();
    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $result = $post->GetPosts($auth,$_GET['user_id'],$randomStory);
    if($result == 'failed_get_posts'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed to get posts.',
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
    }else if($result == 'no_post'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Nothing to get',
            'success' => false
        ]);
        return;
    }else if($result){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'msg' => 'All posts. Random = ' . $randomStory,
            'success' => true
        ]);
        return;
    }
}