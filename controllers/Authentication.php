<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/jwt.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/FileManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/User.php';

class Authentication
{
    private PDO $conn;
    private static string $table_name = 'users';
    private JWT_Util $jwt;
    private User $user;
    private File_Manager $file_manager;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->jwt = new JWT_Util();
        $this->user= new User($conn);
        $this->file_manager = new File_Manager();
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function login($username, $email, $password)
    {
        try {

            if (empty($username) && empty($email))
                return 'username_or_email_required';

            $query = '';

            if (!empty($username))
                $query = "SELECT * FROM " . self::$table_name . " WHERE username='" . $username . "' AND password='" . md5($password) . "'";

            if (!empty($email))
                $query = "SELECT * FROM " . self::$table_name . " WHERE email='" . $email . "' AND password='" . md5($password) . "'";

            $stmt = $this->conn->prepare($query);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {

                    $select_user = '';
                    if(!empty($email)){
                       $select_user = $this->user->GetUserByEmail($email);
                    }else if(!empty($username)){
                        $select_user = $this->user->GetUserByUsername($username)[0];
                    }

                    if($this->checkUserActivate($select_user['id'])) {
                        $create_time = time();
                        $expire_time = getExpireTime($create_time);
                        $payload = [
                            'asn' => getAppUrl(),
                            'act' => $create_time,
                            'aet' => $expire_time,
                            'uid'=> $select_user['id']
                        ];
                        return 'Bearer ' . $this->jwt->Encode_JWT($payload);
                    }else{
                        return 'user_not_active';
                    }
                } else {
                    return 'username_or_password_false';
                }
            } else {
                return 'failed_to_login';
            }

        } catch (\PDOException $pdo_exception) {
            return 'PDO Exception : ' . $pdo_exception->getMessage();
        }
    }

    public function register($args = array())
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

            $active_token = generateActivateToken();

            if (!empty($args['user_avatar']))
                $user_avatar = $this->file_manager->UploadFile($user_avatar, AVATAR_UPLOAD_DIR, AVATAR_UPLOAD_URL);

            $query = sprintf("INSERT INTO %s (username,password,full_name,email,user_avatar,phone,verify_token,active_token,is_active) VALUES (:username,:password,:full_name,:email,:user_avatar,:phone,:verify_token,:active_token,0)", self::$table_name);

            $verify_token = generateRandomString();

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_avatar', $user_avatar);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':verify_token', $verify_token);
            $stmt->bindParam(':active_token', $active_token);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    $user = $this->user->GetUserById($this->conn->lastInsertId());
                    send_mail($user['email'], 'فعالسازی حساب کاربری', 'حساب کاربری شما غیرفعال میباشد. برای فعالسازی لطفاً کد زیر را در اپلیکشین وارد کنید : ' . $user['active_token']);

                    return [
                        'user_id' => $user['id'],
                        'user_email' => $user['email'],
                        'verify_token' => $verify_token,
                    ];
                } else {
                    return 'failed_user_add';
                }
            } else {
                return false;
            }
        } catch (\PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (\Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function verifyUserToken($userId, $verifyToken, $activeToken){
        try {
            $get_result = $this->user->GetUserById($userId);

            if (!empty($get_result) && !$get_result['is_active']) {
                if($get_result['verify_token'] == $verifyToken && $get_result['active_token'] == $activeToken){

                    $query = "UPDATE " . self::$table_name . " SET verify_token=null, active_token=null, is_active=1 WHERE id=" . $userId;

                    $stmt = $this->conn->prepare($query);

                    if ($stmt->execute()) {
                        if ($stmt->rowCount() > 0) {
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
        } catch (\PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (\Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }

    public function checkUserActivate($userId): bool
    {
        $get_result = $this->user->GetUserById($userId);

        if (!empty($get_result) && $get_result['is_active']) {
            return true;
        } else {
            return false;
        }
    }

    public function resendActivationToken($userId, $verifyToken){
        try {
            $get_user_result = '';

            if(!empty($userId)) $get_user_result = $this->user->GetUserById($userId);
            if (empty($get_user_result)) return 'user_not_found';
            if($verifyToken != $get_user_result['verify_token']) return 'verify_token_not_valid';
            if($get_user_result['is_active']) return 'user_activated';

            $active_token = generateActivateToken();
            $verify_token = generateRandomString();

            $query = sprintf("UPDATE %s SET verify_token='%s', active_token='%s'", self::$table_name, $verify_token, $active_token);

            $stmt = $this->conn->prepare($query);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    $user = $this->user->GetUserById($userId);
                    send_mail($user['email'], 'فعالسازی حساب کاربری', 'حساب کاربری شما غیرفعال میباشد. برای فعالسازی لطفاً کد زیر را در اپلیکشین وارد کنید : ' . $user['active_token']);

                    return [
                        'user_id' => $user['id'],
                        'user_email' => $user['email'],
                        'verify_token' => $verify_token,
                    ];
                } else {
                    return 'failed_user_add';
                }
            } else {
                return false;
            }
        } catch (\PDOException $pdo_exception) {
            return 'PDO : ' . $pdo_exception->getMessage();
        } catch (\Exception $exception) {
            return 'Exception : ' . $exception->getMessage();
        }
    }
}