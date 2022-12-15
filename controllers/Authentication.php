<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/jwt.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/FileManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

class Authentication
{
    private $conn;
    private $jwt;
    private static $table_name = 'users';
    private $user;
    private $file_manager;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->jwt = new JWT_Util();
        $this->user= new User($conn);
        $this->file_manager = new File_Manager();
    }

    public function Login($username, $email, $password)
    {
        try {

            if (empty($username) && empty($email))
                return 'username_or_email_required';

            $query = '';

            if (!empty($username))
                $query = "SELECT * FROM " . self::$table_name . " WHERE username='" . $username . "' AND password='" . md5($password) . "'";

            if (!empty($email))
                $query = "SELECT * FROM " . self::$table_name . " WHERE email='" . $email . "' AND password='" . md5($password) . "'";

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {

                    $select_user = '';
                    if(!empty($email)){
                       $select_user = $this->user->GetUserByEmail($email);
                    }else if(!empty($username)){
                        $select_user = $this->user->GetUserByUsername($username)[0];
                    }

                    if($select_user['is_active']) {
                        $create_time = time();
                        $expire_time = $this->jwt->GetExpireTime($create_time);
                        $payload = [
                            'asn' => get_site_url(),
                            'act' => $create_time,
                            'aet' => $expire_time,
                            'uid'=> $select_user['id']
                        ];
                        return $this->jwt->Encode_JWT($payload);
                    }else{
                        return 'user_not_active';
                    }
                } else {
                    return 'username_or_password_false';
                }
            } else {
                return 'failed_to_login';
            }

        } catch (PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        }
    }

    public function Register($args = array())
    {
        try {
            $username = isset($args['username']) && !empty($args['username']) ? $args['username'] : '';
            $password = md5($args['password']);
            $full_name = empty($args['full_name']) ? '' : $args['full_name'];
            $email = isset($args['email']) && !empty($args['email']) ? $args['email'] : '';
            $user_avatar = isset($args['user_avatar']) && !empty($args['user_avatar']) ? $args['user_avatar'] : '';
            $phone = isset($args['phone']) && !empty($args['phone']) ? $args['phone'] : '';

            if(empty($email) && empty($username))
                return 'username_or_email_required';

            $get_user_result = '';

            if(!empty($email)) {
                $get_user_result = $this->user->GetUserByEmail($email);
            }else if(!empty($username)){
                $get_user_result = $this->user->GetUserByUsername($username);
            }

            if ($get_user_result != null)
                return 'user_already_exist';

            $active_token = generate_token();

            if (!empty($args['user_avatar']))
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
                    $user = $this->user->GetUserById($this->conn->lastInsertId());
                    send_mail($user['email'], 'فعالسازی حساب کاربری', 'حساب کاربری شما غیرفعال میباشد. برای فعالسازی لطفاً کد زیر را در اپلیکشین وارد کنید : ' . $user['active_token']);
                    return true;
                } else {
                    return 'failed_user_add';
                }
            } else {
                return false;
            }
        } catch (PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }
}