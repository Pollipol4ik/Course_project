<?php

class Database{
    private static $instance;
    private $mysqli;

    private function __construct(){
        try {
        define('DB_HOST', 'localhost'); 
        define('DB_USER', 'root'); 
        define('DB_PASSWORD', '12345678'); 
        define('DB_NAME', 'vet_help'); 
        

        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ($this->mysqli->connect_error) {
        throw new Exception('Ошибка подключения к базе данных: ' . $this->mysqli->connect_error);
        }
        } catch (Exception $e) {
        die($e->getMessage());
        }
    }

    public static function getInstance(){
        if (!self::$instance) {
        self::$instance = new self();
        }

        return self::$instance;
        }

        public function getConnection()
        {
        return $this->mysqli;
    }

}

$database = Database::getInstance();
$mysqli = $database->getConnection();

?>
