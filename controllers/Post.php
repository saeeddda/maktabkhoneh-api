
<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/FileManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/jwt.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

class Post
{
    private PDO $conn;
    private static string $table_name = 'posts';
    private static string $likes_table_name = 'likes';
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

    public function AddPost($auth, $userId, $postContent){
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {

                if ($this->user->GetUserById($userId) == null)
                    return 'user_not_found';

                $content = isset($postContent) && !empty($postContent) ? $postContent : '';
                $create_at = time();

                $query = sprintf('INSERT INTO %s (content, likes, views, user_id, create_at) VALUES (:content,0,0,:user_id,:create_at)', self::$table_name);

                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':create_at', $create_at);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        return $this->GetPostById($this->conn->lastInsertId());
                    }
                }
                return 'failed_post_add';
            } else {
                return 'token_not_valid';
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function EditPost($auth, $userId, $postId, $postContent){
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {

                if($this->user->GetUserById($userId))
                    return 'user_not_found';

                $get_post = $this->GetpostById($postId);
                if ($get_post == null)
                    return 'post_not_found';

                $content = isset($postContent) && !empty($postContent) ? $postContent: $get_post['content'];

                $query = sprintf('UPDATE %s SET content=:content', self::$table_name);

                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':content', $content);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        return $this->GetPostById($this->conn->lastInsertId());
                    }
                }
                return 'failed_post_edit';
            } else {
                return 'token_not_valid';
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function DeletePost($auth, $userId, $postId){
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                $get_result = $this->GetPostById($postId);
                if ($get_result == null)
                    return 'post_not_found';

                if ($this->fileManager->RemoveOldFile($get_result['file_url'], POST_UPLOAD_DIR)) {

                    $query = sprintf("DELETE FROM %s WHERE id=:id", self::$table_name);

                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $this->conn->prepare($query);

                    $stmt->bindParam(':id', $postId);

                    if ($stmt->execute()) {
                        if($this->DeleteFileByPostId($postId))
                            return 'post_deleted';
                    } else {
                        return 'failed_post_delete';
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

    public function GetPosts($auth, $userId, $random = false){
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                if ($random)
                    $query = sprintf("SELECT * FROM %s ORDER BY RAND()", self::$table_name);
                else
                    $query = sprintf("SELECT * FROM %s", self::$table_name);

                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $this->conn->prepare($query);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $return_values = [];

                        foreach ($posts as $post){
                            $files = $this->GetFilesByPostId($post['id']);
                            $return_values['info'] = $post;
                            $return_values['files'] = $files;
                        }

                        return $return_values;
                    } else {
                        return 'no_post';
                    }
                } else {
                    return 'failed_get_posts';
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

    public function GetPost($auth, $userId, $postId){
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {

                if (empty($postId))
                    return 'post_id_require';

                $get_result = $this->GetPostById($postId);

                if ($get_result != null) {
                    $increasePostView = $this->IncreasePostView($postId);
                    if ($increasePostView == true) {
                        $files = $this->GetFilesByPostId($postId);

                        return [
                            'info' => $get_result,
                            'files' => $files
                        ];
                    }
                } else {
                    return 'post_not_found';
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

    private function GetPostById($postId){
        try {
            $query = sprintf("SELECT * FROM %s WHERE id = :id", self::$table_name);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':id', $postId);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    $get_posts =$stmt->fetch(PDO::FETCH_ASSOC);
                    $get_files = $this->GetFilesByPostId($get_posts['id']);
                    return [
                        'info' => $get_posts,
                        'files' => $get_files,
                    ];
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

    private function GetLikeById($postId){
        try {
            $query = sprintf("SELECT * FROM %s WHERE id = :id", self::$likes_table_name);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':id', $postId);

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

    private function GetLikeByPostIdAndUserId($postId, $userId){
        try {
            $query = sprintf("SELECT * FROM %s WHERE post_id=:post_id AND user_id=:user_id", self::$likes_table_name);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':post_id', $postId);
            $stmt->bindParam(':user_id', $userId);

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

    public function LikeDislikePost($auth, $userId, $postId){
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {

                if(empty($postId))
                    return 'post_id_require';

                $get_post = $this->GetPostById($postId);

                if ($get_post != null) {

                    $get_like = $this->GetLikeByPostIdAndUserId($postId, $userId);
                    if($get_like == null) {
                        $like_query = sprintf("INSERT INTO %s (post_id, user_id) VALUES (:post_id, :user_id)", self::$likes_table_name);
                        $stmt = $this->conn->prepare($like_query);

                        $stmt->bindParam(':post_id', $postId);
                        $stmt->bindParam(':user_id', $userId);

                        if ($stmt->execute()) {
                            if ($stmt->rowCount()) {
                                $query = "UPDATE " . self::$table_name . " SET likes='" . (intval($get_post['likes']) + 1) . "' WHERE id='" . $postId . "'";

                                $stmt = $this->conn->prepare($query);

                                if ($stmt->execute()) {
                                    if ($stmt->rowCount()) {
                                        return 'post_liked';
                                    }
                                }
                            }
                        }
                    }else{
                        $like_query = sprintf("DELETE FROM %s WHERE post_id=:post_id AND user_id=:user_id", self::$likes_table_name);
                        $stmt = $this->conn->prepare($like_query);

                        $stmt->bindParam(':post_id', $postId);
                        $stmt->bindParam(':user_id', $userId);

                        if ($stmt->execute()) {
                            if ($stmt->rowCount()) {
                                $like =  intval($get_post['likes']) - 1;
                                $query = "UPDATE " . self::$table_name . " SET likes='" . ($like < 0 ? 0 : $like) . "' WHERE id='" . $postId . "'";

                                $stmt = $this->conn->prepare($query);

                                if ($stmt->execute()) {
                                    if ($stmt->rowCount()) {
                                        return 'post_unliked';
                                    }
                                }
                            }
                        }
                    }
                    return 'failed_like_post';
                } else {
                    return 'post_not_found';
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

    private function IncreasePostView($postId){
        try {

            if (empty($postId))
                return null;

            $get_post = $this->GetPostById($postId);

            if ($get_post != null) {
                $query = sprintf("UPDATE %s SET views='%s' WHERE id='%s'", self::$table_name,(intval($get_post['views']) + 1), $postId);

                $stmt = $this->conn->prepare($query);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        return true;
                    }
                }
                return false;
            } else {
                return null;
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function UploadFile($auth, $userId, $postId, $postFile)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                if ($postFile['error'] > 0 || empty($userId))
                    return 'file_user_required';

                if ($this->GetPostById($postId) == null)
                    return 'post_not_found';

                $file_url = $this->fileManager->UploadFile($postFile, POST_UPLOAD_DIR, POST_UPLOAD_URL);

                $query = sprintf('INSERT INTO %s (file_url, entity_id) VALUES (:file_url,:post_id)', self::$file_table_name);

                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':file_url', $file_url);
                $stmt->bindParam(':post_id', $postId);

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

    private function DeleteFileByPostId($postId)
    {
        try {
            if ($this->GetPostById($postId) == null)
                return 'post_not_found';

            $get_files = $this->GetFilesByPostId($postId);
            foreach ($get_files as $file) {
                if ($this->fileManager->RemoveOldFile($file['file_url'], POST_UPLOAD_DIR)) {
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

                if ($this->fileManager->RemoveOldFile($get_file['file_url'], POST_UPLOAD_DIR)) {

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

    public function GetFiles($auth, $userId, $postId)
    {
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                $get_files = $this->GetFilesByPostId($postId);
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

    private function GetFilesByPostId($postId){
        try {
            $query = sprintf("SELECT * FROM %s WHERE entity_id = :id", self::$file_table_name);
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $postId);

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