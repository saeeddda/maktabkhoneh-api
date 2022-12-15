<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/sanitizer.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/Story.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $db = new Database();
    $story = new Story($db->GetConnection());

    $request = apache_request_headers();
    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $result = $story->AddStory($auth,$_FILES['story_file'], $_POST['user_id']);
    if($result == 'file_user_required'){
       echo json_encode([
           'data' => $result,
           'msg' => 'Story file and user id is required.',
           'success' => false
       ]);
       return;
    }else if($result == 'user_not_found'){
        echo json_encode([
            'data' => $result,
            'msg' => 'User not found',
            'success' => false
        ]);
        return;
    }else if($result == 'token_not_valid'){
        echo json_encode([
            'data' => $result,
            'msg' => 'Authorization token not valid',
            'success' => false
        ]);
        return;
    }else if($result == 'failed_story_add'){
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed to add story',
            'success' => false
        ]);
        return;
    }else {
        echo json_encode([
            'data' => $result,
            'msg' => 'Story added',
            'success' => true
        ]);
        return;
    }
}