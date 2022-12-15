<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/jwt.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/FileManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/mail.php';

class User
{
    private $conn;
    private static $table_name = 'users';
    private static $Follower_table_name = 'followers';
    private $jwt;
    private $file_manager;

    public function __construct($db_conn)
    {
        $this->conn = $db_conn;
        $this->jwt = new JWT_Util();
        $this->file_manager = new File_Manager();
    }

    public function GetUser($auth, $username, $userId){
        try {
            if($this->jwt->Validate_Token($auth, $userId)) {
                $get_result = $this->GetUserByUsername($username);
                if ($get_result != null && count($get_result) > 0) {
                    return $get_result;
                } else {
                    return 'not_found';
                }
            }else{
                return false;
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function GetAllUser($auth, $userId){
        try {
            if($this->jwt->Validate_Token($auth, $userId)) {
                $query = sprintf("SELECT * FROM %s ORDER BY id", self::$table_name);

                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $this->conn->prepare($query);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        return $stmt->fetch(PDO::FETCH_ASSOC);
                    } else {
                        return null;
                    }
                } else {
                    return false;
                }
            }else{
                return false;
            }
        } catch (PDOException $pdo_exception){
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        }
    }

    public function GetUserById($id){
        try {
            $query = sprintf("SELECT * FROM %s WHERE id = :id", self::$table_name);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':id',$id);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    return null;
                }
            } else {
                return false;
            }

        } catch (PDOException $pdo_exception){
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        }
    }

    public function GetUserByEmail($email){
        try {
            $query = sprintf("SELECT * FROM %s WHERE email=:email", self::$table_name);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':email',$email);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    return null;
                }
            } else {
                return false;
            }

        } catch (PDOException $pdo_exception){
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        }
    }

    public function GetUserByUsername($username){
        try {
            $query = "SELECT * FROM " . self::$table_name . " WHERE username LIKE '%" . $username . "%'";

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    return null;
                }
            } else {
                return false;
            }

        } catch (PDOException $pdo_exception){
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        }
    }

    public function GetUserByPhone($phone){
        try {
            $query = sprintf("SELECT * FROM %s WHERE phone=:phone", self::$table_name);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':phone',$phone);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    return null;
                }
            } else {
                return false;
            }

        } catch (PDOException $pdo_exception){
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        }
    }

    public function AddUser($auth, $args)
    {
        try {
            if($this->jwt->Validate_Token($auth, $args['user_id'])) {
                $username = $args['username'];
                $password = md5($args['password']);
                $full_name = empty($args['full_name']) ? '' : $args['full_name'];
                $email = $args['email'];
                $user_avatar = !empty($args['user_avatar']) ? $args['user_avatar'] : '';
                $phone = !empty($args['phone']) ? $args['phone'] : '';

                if(empty($email) && empty($username))
                    return 'username_or_email_required';

                $get_user_result = '';

                if(!empty($email)) {
                    $get_user_result = $this->GetUserByEmail($email);
                }else if(!empty($username)){
                    $get_user_result = $this->GetUserByUsername($username);
                }

                if ($get_user_result != null)
                    return 'user_already_exist';

                $active_token = generate_token();

                if(!empty($args['user_avatar']))
                    $user_avatar = $this->file_manager->UploadFile($user_avatar, AVATAR_UPLOAD_DIR, AVATAR_UPLOAD_URL);

                $query = sprintf("INSERT INTO %s (username,password,full_name,email,user_avatar,phone,active_token,is_active) VALUES (:username,:password,:full_name,:email,:user_avatar,:phone,:active_token,0)", self::$table_name);

                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':user_avatar', $user_avatar);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':active_token', $active_token);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        $user = $this->GetUserById($this->conn->lastInsertId());
                        send_mail($user['email'], 'فعالسازی حساب کاربری', 'حساب کاربری شما غیرفعال میباشد. برای فعالسازی لطفاً کد زیر را در اپلیکشین وارد کنید : ' . $user['active_token']);
                        return $user;
                    } else {
                        return 'failed_user_add';
                    }
                } else {
                    return false;
                }
            }else{
                return false;
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function EditUser($auth, $userId, $args = array()){
        try {
            if($this->jwt->Validate_Token($auth, $userId)) {

                $user_old_data = $this->GetUserById($args['edit_id']);
                if ($user_old_data == null)
                    return 'user_not_found';

                $username = isset($args['username']) && !empty($args['username']) ? $args['username'] : $user_old_data['username'];
                $password = isset($args['password']) && !empty($args['password']) ? md5($args['password']) : $user_old_data['password'];
                $full_name = isset($args['full_name']) && !empty($args['full_name']) ? $args['full_name'] : $user_old_data['full_name'];
                $email = isset($args['email']) && !empty($args['email']) ? $args['email'] : $user_old_data['email'];
                $user_avatar = isset($args['user_avatar']) && !empty($args['user_avatar']) ? $args['user_avatar'] : $user_old_data['user_avatar'];
                $phone = isset($args['phone']) && !empty($args['phone']) ? $args['phone'] : $user_old_data['phone'];
                $active_token = isset($args['active_token']) && !empty($args['active_token']) ? $args['active_token'] : $user_old_data['active_token'];

                if (isset($args['user_avatar']) && !empty($args['user_avatar'])) {
                    $this->file_manager->RemoveOldFile($user_old_data['user_avatar'], AVATAR_UPLOAD_DIR);
                    $user_avatar = $this->file_manager->UploadFile($user_avatar, AVATAR_UPLOAD_DIR, AVATAR_UPLOAD_URL);
                }

                $query = sprintf("UPDATE %s SET username=:username, password=:password, full_name=:full_name, email=:email, user_avatar=:user_avatar, phone=:phone, active_token=:active_token WHERE id=:id", self::$table_name);

                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':id', $args['edit_id']);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':user_avatar', $user_avatar);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':active_token', $active_token);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        return $this->GetUserById($userId);
                    } else {
                        return 'failed_user_add';
                    }
                } else {
                    return false;
                }
            }else{
                return  false;
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function DeleteUser($auth, $userId, $deleteId){
        try {
            if($this->jwt->Validate_Token($auth, $userId)){
                $get_result = $this->GetUserById($userId);

                if ($get_result != null && !empty($get_result)) {
                    $query = sprintf("DELETE FROM %s WHERE id=:id", self::$table_name);

                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $this->conn->prepare($query);

                    $stmt->bindParam(':id', $deleteId);

                    if ($stmt->execute()) {
                        return 'user_deleted';
                    } else {
                        return 'failed_user_delete';
                    }
                } else {
                    return 'user_not_found';
                }
            }else{
                return false;
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function FollowUnfollowUser($auth, $userId, $followerId){
        try {
            if($this->jwt->Validate_Token($auth, $userId)) {

                if ($userId == $followerId)
                    return 'user_same_as_follower';

                $get_user_result = $this->GetUserById($userId);
                if ($get_user_result == null)
                    return 'user_not_found';

                $get_follower_result = $this->GetUserById($followerId);
                if ($get_follower_result == null)
                    return 'follower_not_found';

                $get_follower = $this->GetFollower($userId, $followerId);
                if ($get_follower != null) {
                    $query = sprintf("DELETE FROM %s WHERE user_id=:user_id AND follower_id=:follower_id", self::$Follower_table_name);

                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $this->conn->prepare($query);

                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':follower_id', $followerId);

                    if ($stmt->execute()) {
                        if ($stmt->rowCount()) {
                            return 'unfollow_successful';
                        } else {
                            return 'failed_user_unfollow';
                        }
                    } else {
                        return false;
                    }
                } else {
                    $query = sprintf("INSERT INTO %s SET user_id=:user_id, follower_id=:follower_id", self::$Follower_table_name);

                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $this->conn->prepare($query);

                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':follower_id', $followerId);

                    if ($stmt->execute()) {
                        if ($stmt->rowCount()) {
                            return 'follow_successful';
                        } else {
                            return 'failed_user_follow';
                        }
                    } else {
                        return false;
                    }
                }
            }else{
                return false;
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function GetFollower($userId, $followerId){
        try {
            $query = sprintf("SELECT * FROM %s WHERE user_id=:user_id AND follower_id=:follower_id", self::$Follower_table_name);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':user_id',$userId);
            $stmt->bindParam(':follower_id',$followerId);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    return null;
                }
            } else {
                return false;
            }

        } catch (PDOException $pdo_exception){
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        }
    }

    public function VerifyUserToken($userId, $token){
        try {
            $get_result = $this->GetUserById($userId);

            if ($get_result != null && !empty($get_result) && !$get_result['is_active']) {
                if($get_result['active_token'] == $token){

                    $query = "UPDATE " . self::$table_name . " SET active_token=0, is_active=1 WHERE id=" . $userId;

                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $this->conn->prepare($query);

                    if ($stmt->execute()) {
                        if ($stmt->rowCount()) {
                            return 'user_active_successful';
                        } else {
                            return 'failed_user_active';
                        }
                    }
                    return false;
                }else{
                    return 'token_not_valid';
                }
            } else {
                return 'user_not_found_or_active';
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }
}