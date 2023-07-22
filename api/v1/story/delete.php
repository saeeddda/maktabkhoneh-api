<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/Story.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $request = apache_request_headers();

    $story = new Story(GetConnection());

    $storyId = $_POST['story_id'];
    $userId = $_POST['user_id'];

    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $result = $story->DeleteStory($auth,$userId,$storyId);

    if($result == 'story_deleted'){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'msg' => 'Story delete successfully',
            'success' => true
        ]);
        return;
    }else if($result == 'token_not_valid') {
        http_response_code(401);
        echo json_encode([
            'data' => $result,
            'msg' => 'Authorization token is required',
            'success' => false
        ]);
        return;
    }else if($result == 'failed_story_delete' || $result == 'failed_file_delete') {
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed to delete story',
            'success' => false
        ]);
        return;
    }else if($result == 'story_not_found') {
        http_response_code(404);
        echo json_encode([
            'data' => $result,
            'msg' => 'Story not found',
            'success' => false
        ]);
        return;
    }
}