<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/FileManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/jwt.php';
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
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function AddStory($auth, $storyFile, $userId)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                if ($storyFile['error'] > 0 || empty($userId))
                    return 'file_user_required';

                if ($this->user->GetUserById($userId) == null)
                    return 'user_not_found';

                $create_at = time();
                $end_at = GetExpireTime($create_at, 1);

                $file_url = '';
                if (!empty($storyFile))
                    $file_url = $this->fileManager->UploadFile($storyFile, STORY_UPLOAD_DIR, STORY_UPLOAD_URL);

                $query = sprintf('INSERT INTO %s (file_url, user_id, create_at, end_at) VALUES (:file_url,:user_id,:create_at,:end_at)', self::$table_name);

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
        } catch (PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function EditStory($auth, $userId, $storyId, $storyFile)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                if ($storyFile['error'] > 0 || empty($storyId))
                    return 'file_user_required';

                if($this->user->GetUserById($userId))
                    return 'user_not_found';

                $get_story = $this->GetStoryById($storyId);
                if ($get_story == null)
                    return 'story_not_found';

                $create_at = time();
                $end_at = GetExpireTime($create_at, 1);

                $file_url = $get_story['file_url'];

                if(isset($storyFile) && !empty($storyFile)){
                    if (!empty($file_url) && !empty($storyFile)) {
                        if ($this->fileManager->RemoveOldFile($file_url, STORY_UPLOAD_DIR)) {
                            $file_url = $this->fileManager->UploadFile($storyFile, STORY_UPLOAD_DIR, STORY_UPLOAD_URL);
                        }else{
                            return 'file_remove_error';
                        }
                    }else{
                        $file_url = $this->fileManager->UploadFile($storyFile, STORY_UPLOAD_DIR, STORY_UPLOAD_URL);
                    }
                }

                $query = sprintf('UPDATE %s SET file_url=:file_url, create_at=:create_at, end_at=:end_at', self::$table_name);

                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':file_url', $file_url);
                $stmt->bindParam(':create_at', $create_at);
                $stmt->bindParam(':end_at', $end_at);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        return $this->GetStoryById($this->conn->lastInsertId());
                    }
                }
                return 'failed_story_edit';
            } else {
                return 'token_not_valid';
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function DeleteStory($auth, $userId, $storyId)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                $get_result = $this->GetStoryById($storyId);
                if ($get_result == null)
                    return 'story_not_found';

                if ($this->fileManager->RemoveOldFile($get_result['file_url'], STORY_UPLOAD_DIR)) {

                    $query = sprintf("DELETE FROM %s WHERE id=:id", self::$table_name);

                    $stmt = $this->conn->prepare($query);

                    $stmt->bindParam(':id', $storyId);

                    if ($stmt->execute()) {
                        return 'story_deleted';
                    } else {
                        return 'failed_story_delete';
                    }
                } else {
                    return 'failed_file_delete';
                }
            } else {
                return 'token_not_valid';
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function GetStories($auth, $userId, $random = false)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                if ($random)
                    $query = sprintf("SELECT * FROM %s ORDER BY RAND()", self::$table_name);
                else
                    $query = sprintf("SELECT * FROM %s", self::$table_name);

                $stmt = $this->conn->prepare($query);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        return $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        return 'no_story';
                    }
                } else {
                    return 'failed_get_stories';
                }
            } else {
                return 'token_not_valid';
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function GetStory($auth, $userId, $storyId)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                $get_result = $this->GetStoryById($storyId);

                if ($get_result != null) {
                    return $get_result;
                } else {
                    return 'story_not_found';
                }
            } else {
                return 'token_not_valid';
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    private function GetStoryById($storyId)
    {
        try {
            $query = sprintf("SELECT * FROM %s WHERE id = :id", self::$table_name);

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':id', $storyId);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    return null;
                }
            } else {
                return false;
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        }
    }
}