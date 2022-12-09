<?php

class Database{

    private static $table_name = '';
    public $pdo_conn = null;

    public function __construct($table_name)
    {
        self::$table_name = $table_name;
        $this->pdo_conn = $this->GetConnection();
    }

    public function GetConnection(){
        try {
            include $_SERVER['SERVER_ADDR'] . '/consts/config.php';
            $dsn =sprintf('%s:host=%s;dbname=%s;charset=%s',DB_DRIVER,DB_HOST,DB_NAME,DB_CHARSET);
            return new PDO($dsn,DB_USERNAME,DB_PASSWORD);
        }catch (PDOException $exception){
            return $exception;
        }
    }

    public function Insert($columns = array(), $values = array()){
        $column = implode(',',$columns);
        $value = implode(',',$values);
        $query = sprintf('INSERT INTO %s (%s) VALUES (%s)',self::$table_name, $column, $value);
        $statement = $this->pdo_conn->prepare($query);
        return $statement->execute();
    }

    public function Update(){

    }

    public function Delete(){

    }

    public function GetAll(){

    }

    public function GetRandom(){

    }

    public function GetTotalRows() {

    }
}
