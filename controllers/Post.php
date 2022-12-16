
<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/FileManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/jwt.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

class Post
{
    private $conn;
    private static $table_name = 'posts';
    private static $likes_table_name = 'likes';
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

    public function AddPost($auth, $userId, $postFile, $postContent){
        try {
            if (empty($auth))
                return 'token_not_valid';

            if ($this->jwt->Validate_Token($auth, $userId)) {
                if ($postFile['error'] > 0 || empty($userId))
                    return 'file_user_required';

                if ($this->user->GetUserById($userId) == null)
                    return 'user_not_found';

                $content = isset($postContent) && !empty($postContent) ? $postContent : '';
                $create_at = time();

                $file_url = '';
                if (!empty($postFile))
                    $file_url = $this->fileManager->UploadFile($postFile, POST_UPLOAD_DIR, POST_UPLOAD_URL);

                $query = sprintf('INSERT INTO %s (file_url, content, likes, views, user_id, create_at) VALUES (:file_url,:content,0,0,:user_id,:create_at)', self::$table_name);

                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':file_url', $file_url);
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

    public function EditPost($auth, $userId, $postId, $postFile, $postContent){
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
                $file_url = $get_post['file_url'];

                if(isset($postFile) && !empty($postFile)){
                    if (!empty($file_url) && !empty($postFile)) {
                        if ($this->fileManager->RemoveOldFile($file_url, POST_UPLOAD_DIR)) {
                            $file_url = $this->fileManager->UploadFile($postFile, POST_UPLOAD_DIR, POST_UPLOAD_URL);
                        }else{
                            return 'file_remove_error';
                        }
                    }else{
                        $file_url = $this->fileManager->UploadFile($postFile, POST_UPLOAD_DIR, POST_UPLOAD_URL);
                    }
                }

                $query = sprintf('UPDATE %s SET file_url=:file_url, content=:content', self::$table_name);

                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':file_url', $file_url);
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
                        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

                if(empty($postId))
                    return 'post_id_require';

                $get_result = $this->GetPostById($postId);

                if ($get_result != null) {
                    return $get_result;
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

                $get_result = $this->GetPostById($postId);

                if ($get_result != null) {

                    $get_like = $this->GetLikeByPostIdAndUserId($postId, $userId);
                    if($get_like == null) {


                        $like_query = sprintf("INSERT INTO %s (post_id, user_id) VALUES (:post_id, :user_id)", self::$likes_table_name);
                        $stmt = $this->conn->prepare($like_query);

                        $stmt->bindParam(':post_id', $postId);
                        $stmt->bindParam(':user_id', $userId);

                        if ($stmt->execute()) {
                            if ($stmt->rowCount()) {
                                $get_like = $this->GetLikeById($this->conn->lastInsertId());
                                $query = sprintf("UPDATE %s SET likes=:likes WHERE id=:id", self::$table_name);

                                $stmt = $this->conn->prepare($query);

                                $stmt->bindParam(':id', $postId);
                                $stmt->bindParam(':likes', strval(intval($get_like['likes']) + 1));

                                if ($stmt->execute()) {
                                    if ($stmt->rowCount()) {
                                        return 'post_liked';
                                    }
                                }
                            }
                        }
                    }else{
                        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        $like_query = sprintf("INSERT INTO %s (post_id, user_id) VALUES (:post_id, :user_id)", self::$likes_table_name);
                        $stmt = $this->conn->prepare($like_query);

                        $stmt->bindParam(':post_id', $postId);
                        $stmt->bindParam(':user_id', $userId);

                        if ($stmt->execute()) {
                            if ($stmt->rowCount()) {
                                $get_like = $this->GetLikeById($this->conn->lastInsertId());
                                $query = sprintf("UPDATE %s SET likes=:likes WHERE id=:id", self::$table_name);

                                $stmt = $this->conn->prepare($query);

                                $stmt->bindParam(':id', $postId);
                                $stmt->bindParam(':likes', strval(intval($get_like['likes']) + 1));

                                if ($stmt->execute()) {
                                    if ($stmt->rowCount()) {
                                        return 'post_liked';
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

    public function IncresePostView($auth, $userId, $postId){

    }
}