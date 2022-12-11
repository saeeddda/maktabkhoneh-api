<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/jwt.php';

class User
{
    private $conn;
    private static $table_name = 'users';
    private static $site_name = '';

    public function __construct($db_conn)
    {
        $this->conn = $db_conn;
        self::$site_name = $_SERVER['DOCUMENT_ROOT'];
    }

    public function GetUser(){

    }

    public function AddUser($auth, $args)
    {
        try {
//            $jwt = new JWT_Util();
//            $payload = (array)$jwt->Decode_JWT($auth);
//
//            if ($payload['asn'] != self::$site_name)
//                return new Firebase\JWT\SignatureInvalidException('Authorize key not valid!');
//
//            if($payload['act'] < time())
//                return new Firebase\JWT\BeforeValidException('Authorize dat not valid!');
//
//            if($payload['aet'] > $jwt->GetExpireTime($payload['act']))
//                return new Firebase\JWT\ExpiredException('Authorize code has bin expired!');

            if (empty($args['username']) && empty($args['password']) && empty($args['email'])) {
                return new Http\Exception\BadQueryStringException('some data not sent.');
            }

            $username = $args['username'];
            $password = md5($args['password']);
            $full_name = empty($args['full_name']) ? '' : $args['full_name'];
            $email = $args['email'];

            $query = "INSERT INTO " . self::$table_name . " SET username = :username, password = :password, full_name = :full_name, email = :email";

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':username',$username);
            $stmt->bindParam(':password',$password);
            $stmt->bindParam(':full_name',$full_name);
            $stmt->bindParam(':email',$email);

            if ($stmt->execute()) {
                if ($stmt->rowCount()) {
                    return true;
                } else {
                    return false;
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

    public function EditUser(){

    }

    public function FollowUser(){

    }

    public function UnfollowUser(){

    }
}