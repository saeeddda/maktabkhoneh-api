<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/FileManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/jwt.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/sanitizer.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

class Story
{
    private $conn;
    private static $table_name = 'stories';
    private $jwt;
    private $fileManager;
    private $user;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->jwt = new JWT_Util();
        $this->fileManager = new File_Manager();
        $this->user = new User($conn);
    }

    public function AddStory($auth, $storyFile, $userId){
        try {
            if ($this->jwt->Validate_Token($auth, $userId)) {
                if (empty($storyFile) || empty($userId))
                    return 'file_user_required';

                $getUser = $this->user->GetUserById($userId);
                if ($getUser == null)
                    return 'user_not_found';

                $create_at = time();
                $end_at = GetExpireTime($create_at, 1);

                $file_url = '';
                if (!empty($storyFile))
                    $file_url = $this->fileManager->UploadFile($storyFile, STORY_UPLOAD_DIR, STORY_UPLOAD_URL);

                $query = sprintf('INSERT INTO $s (file_url, user_id, create_at, end_at) VALUES (:file_url,:user_id,:create_at,:end_at)', self::$table_name);

                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':file_url', $file_url);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':create_at', $create_at);
                $stmt->bindParam(':end_at', $end_at);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        return $this->GetStoryById($this->conn->lastInsertId());
                    } else {
                        return 'failed_story_add';
                    }
                } else {
                    return false;
                }
            } else {
                return 'token_not_valid';
            }
        }catch (PDOException $pdo_exception){
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        }catch (Exception $exception){
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public  function EditStory($auth, $userId, $args = array()){

    }

    public function DeleteStory($auth, $userId, $storyId){

    }

    public function GetStories($auth){

    }

    public function GetStory($auth, $storyId){

    }

    private function GetStoryById($storyId){
        return null;
    }
}