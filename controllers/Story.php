<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/FileManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/jwt.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

class Story
{
    private PDO $conn;
    private static string $table_name = 'stories';
    private static string $file_table_name = 'files';
    private JWT_Util $jwt;
    private File_Manager $fileManager;
    private User $user;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->jwt = new JWT_Util();
        $this->fileManager = new File_Manager();
        $this->user = new User($conn);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function AddStory($auth, $userId)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {

                if ($this->user->GetUserById($userId) == null)
                    return 'user_not_found';

                $create_at = time();
                $end_at = GetExpireTime($create_at, 1);

                $query = sprintf('INSERT INTO %s (user_id, create_at, end_at) VALUES (:user_id,:create_at,:end_at)', self::$table_name);

                $stmt = $this->conn->prepare($query);

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

    public function EditStory($auth, $userId, $storyId)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                if ($this->user->GetUserById($userId))
                    return 'user_not_found';

                $get_story = $this->GetStoryById($storyId);
                if ($get_story == null)
                    return 'story_not_found';

                $create_at = time();
                $end_at = GetExpireTime($create_at, 1);

                $query = sprintf('UPDATE %s SET create_at=:create_at, end_at=:end_at', self::$table_name);

                $stmt = $this->conn->prepare($query);

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

                $query = sprintf("DELETE FROM %s WHERE id=:id", self::$table_name);

                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':id', $storyId);

                if ($stmt->execute()) {
                    if($this->DeleteFileByStoryId($storyId))
                        return 'story_deleted';
                } else {
                    return 'failed_story_delete';
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
                        $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $return_values = [];

                        foreach ($stories as $story){
                            $files = $this->GetFilesByStoryId($story['id']);
                            $return_values['info'] = $story;
                            $return_values['files'] = $files;
                        }

                        return $return_values;
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
                    $files = $this->GetFilesByStoryId($storyId);

                    return [
                        'info' => $get_result,
                        'files' => $files
                    ];
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

    public function UploadFile($auth, $userId, $storyId, $storyFile)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                if ($storyFile['error'] > 0 || empty($userId))
                    return 'file_user_required';

                if ($this->GetStoryById($storyId) == null)
                    return 'story_not_found';

                $file_url = $this->fileManager->UploadFile($storyFile, STORY_UPLOAD_DIR, STORY_UPLOAD_URL);

                $query = sprintf('INSERT INTO %s (file_url, entity_id) VALUES (:file_url,:story_id)', self::$file_table_name);

                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':file_url', $file_url);
                $stmt->bindParam(':story_id', $storyId);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        return $this->GetFileById($this->conn->lastInsertId());
                    }
                }
                return false;
            } else {
                return 'token_not_valid';
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    private function DeleteFileByStoryId($storyId)
    {
        try {
            if ($this->GetStoryById($storyId) == null)
                return 'story_not_found';

            $get_files = $this->GetFilesByStoryId($storyId);
            foreach ($get_files as $file) {
                if ($this->fileManager->RemoveOldFile($file['file_url'], STORY_UPLOAD_DIR)) {
                    $query = sprintf('DELETE FROM %s WHERE id=:id', self::$file_table_name);
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':id', $file['id']);
                    if ($stmt->execute())
                        return true;
                }
            }
            return false;
        } catch (PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function DeleteFileById($auth, $userId, $fileId)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {

                $get_file = $this->GetFileById($fileId);
                if ($get_file == null)
                    return 'file_not_found';

                if ($this->fileManager->RemoveOldFile($get_file['file_url'], STORY_UPLOAD_DIR)) {

                    $query = sprintf('DELETE FROM %s WHERE id=:id', self::$file_table_name);

                    $stmt = $this->conn->prepare($query);

                    $stmt->bindParam(':id', $fileId);

                    if ($stmt->execute()) {
                        if ($stmt->rowCount()) {
                            return 'file_deleted';
                        }
                    }
                }
                return 'failed_file_delete';
            } else {
                return 'token_not_valid';
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function GetFiles($auth, $userId, $storyId)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                $get_files = $this->GetFilesByStoryId($storyId);
                if ($get_files != null) {
                    return $get_files;
                }
                return 'files_not_found';
            }
            return 'failed_files_get';
        } catch (PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    private function GetFilesByStoryId($storyId){
        try {
            $query = sprintf("SELECT * FROM %s WHERE entity_id = :id", self::$file_table_name);
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $storyId);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            return 'failed_files_get';
        } catch (PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    private function GetFileById($fileId){
        try {
            $query = sprintf("SELECT * FROM %s WHERE id=:id", self::$file_table_name);

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':id', $fileId);

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