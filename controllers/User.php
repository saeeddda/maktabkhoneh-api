<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/jwt.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/File_Manager.php';

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

    public function GetUser($auth, $id){

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

    public function GetUserByUsername($username){
        try {
            $query = sprintf("SELECT * FROM %s WHERE username=:username", self::$table_name);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':username',$username);

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

    public function AddUser($auth, $args)
    {
        try {
//            if($this->jwt->Validate_Token($auth)) {
                $username = $args['username'];
                $password = md5($args['password']);
                $full_name = empty($args['full_name']) ? '' : $args['full_name'];
                $email = $args['email'];
                $user_avatar = !empty($args['user_avatar']) ? $args['user_avatar'] : '';

                $get_result = $this->GetUserByEmail($email);
                if ($get_result != null)
                    return 'user_already_exist';

                $user_avatar = $this->file_manager->Upload_Image($user_avatar);

                $query = sprintf("INSERT INTO %s SET username=:username, password=:password, full_name=:full_name, email=:email, user_avatar=:user_avatar", self::$table_name);

                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':user_avatar', $user_avatar);

                if ($stmt->execute()) {
                    if ($stmt->rowCount()) {
                        return 'user_added';
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

    public function EditUser(){

    }

    public function FollowUser(){

    }

    public function UnfollowUser(){

    }
}