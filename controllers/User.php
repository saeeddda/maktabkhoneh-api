<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/jwt.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/File_Manager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';

class User
{
    private $conn;
    private static $table_name = 'users';
    private $jwt;
    private $file_manager;

    public function __construct($db_conn)
    {
        $this->conn = $db_conn;
        $this->jwt = new JWT_Util();
        $this->file_manager = new File_Manager();
    }

//    public function GetUserInfo($username, $email = ''){
//
//    }

    public function GetUser($auth, $username){
        try {
            //todo: auth token is important
//            if($this->jwt->Validate_Token($auth)) {

            $get_result = $this->GetUserByUsername($username);

            if ($get_result != null && count($get_result) > 0) {
                return $get_result;
            } else {
                return 'not_found';
            }
//            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    private function GetUserById($id){
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

    private function GetUserByEmail($email){
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

    private function GetUserByUsername($username){
        try {
            $query = "SELECT * FROM " . self::$table_name . " WHERE username LIKE '%" . $username . "%'";

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

//            $stmt->bindParam(':username',$username);

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

    private function GetUserByPhone($phone){
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
            //todo: auth token is important
//            if($this->jwt->Validate_Token($auth)) {
                $username = $args['username'];
                $password = md5($args['password']);
                $full_name = empty($args['full_name']) ? '' : $args['full_name'];
                $email = $args['email'];
                $user_avatar = !empty($args['user_avatar']) ? $args['user_avatar'] : '';
                $phone = !empty($args['phone']) ? $args['phone'] : '';

                $get_result = $this->GetUserByEmail($email);
                if ($get_result != null)
                    return 'user_already_exist';

                $active_token = generate_token();

                if(!empty($args['user_avatar']))
                    $user_avatar = $this->file_manager->Upload_Image($user_avatar);

                $query = sprintf("INSERT INTO %s SET username=:username, password=:password, full_name=:full_name, email=:email, user_avatar=:user_avatar, phone=:phone, active_token=:active_token, is_active=0", self::$table_name);

                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':user_avatar', $user_avatar);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':active_token', $active_token);

                //todo: send verification email

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        return $this->GetUserById($this->conn->lastInsertId());
                    } else {
                        return 'failed_user_add';
                    }
                } else {
                    return false;
                }
//            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function EditUser($auth, $userId, $args = array()){
        try {
            //todo: auth token is important
//            if($this->jwt->Validate_Token($auth)) {

            $user_old_data = $this->GetUserById($userId);
            if ($user_old_data == null)
                return 'user_not_found';

            $username = isset($args['username']) && !empty($args['username']) ? $args['username'] : $user_old_data['username'];
            $password = isset($args['password']) && !empty($args['password']) ? md5($args['password']) : $user_old_data['password'];
            $full_name = isset($args['full_name']) && !empty($args['full_name']) ? $args['full_name'] : $user_old_data['full_name'];
            $email = isset($args['email']) && !empty($args['email']) ? $args['email'] : $user_old_data['email'];
            $user_avatar = isset($args['user_avatar']) && !empty($args['user_avatar']) ? $args['user_avatar'] : $user_old_data['user_avatar'];
            $phone = isset($args['phone']) && !empty($args['phone']) ? $args['phone'] : $user_old_data['phone'];
            $active_token = isset($args['active_token']) && !empty($args['active_token']) ? $args['active_token'] : $user_old_data['active_token'];
            $is_active = isset($args['is_active']) && !empty($args['is_active']) ? $args['is_active'] : $user_old_data['is_active'];

            if(isset($args['user_avatar']) && !empty($args['user_avatar'])) {
                $this->file_manager->Remove_Old_Image($user_old_data['user_avatar']);
                $user_avatar = $this->file_manager->Upload_Image($user_avatar);
            }

            $query = sprintf("UPDATE %s SET username=:username, password=:password, full_name=:full_name, email=:email, user_avatar=:user_avatar, phone=:phone, active_token=:active_token WHERE id=:id", self::$table_name);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':id', $userId);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_avatar', $user_avatar);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':active_token', $active_token);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    return 'failed_user_add';
                }
            } else {
                return false;
            }
//            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function FollowUser($auth, $userId, $followId){

    }

    public function UnfollowUser($auth, $userId, $followId){

    }

    public function VerifyUserToken($userId, $token){
        try {
            $get_result = $this->GetUserById($userId);

            if ($get_result != null && !empty($get_result)) {
                if($get_result['active_token'] == $token){

                    $args =[

                    ];

//                    $this->EditUser('', $userId, )
                }
            } else {
                return 'user_not_valid';
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }
}