<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $request = apache_request_headers();

    $db = new Database();
    $user = new User($db->GetConnection());

    $auth = isset($request['Authorization']) && !empty($request['Authorization']) ? str_replace('Bearer ','', $request['Authorization']) : '';

    $result = $user->FollowUnfollowUser($auth, $_POST['user_id'], $_POST['follower_id']);
    if($result == 'follow_successful'){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'msg' => 'User follow successful',
            'success' => true,
        ]);
        return;
    }else if($result == 'failed_user_follow'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed user follow',
            'success' => false,
        ]);
        return;
    }else if($result == 'unfollow_successful'){
        http_response_code(200);
        echo json_encode([
            'data' => $result,
            'msg' => 'User unfollow successful',
            'success' => true,
        ]);
        return;
    }else if($result == 'failed_user_unfollow'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed user unfollow',
            'success' => false,
        ]);
        return;
    }else if($result == 'user_not_found'){
        http_response_code(404);
        echo json_encode([
            'data' => $result,
            'msg' => 'User not found',
            'success' => false,
        ]);
        return;
    }else if($result == 'follower_not_found'){
        http_response_code(404);
        echo json_encode([
            'data' => $result,
            'msg' => 'Follower not found',
            'success' => false,
        ]);
        return;
    }else if($result == 'user_same_as_follower'){
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'User cant follow self',
            'success' => false,
        ]);
        return;
    }else {
        http_response_code(400);
        echo json_encode([
            'data' => $result,
            'msg' => 'Failed',
            'success' => false,
        ]);
        return;
    }
}