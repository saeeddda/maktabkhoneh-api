<?php

class Database{
    public function GetConnection(){
        try {
            include $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
            $dsn =sprintf('%s:host=%s;post=%s;dbname=%s;charset=%s', DB_DRIVER,DB_HOST,DB_PORT,DB_NAME,DB_CHARSET);
            return new PDO($dsn,DB_USERNAME,DB_PASSWORD);
        }catch (PDOException $exception){
            return $exception;
        }
    }
}
