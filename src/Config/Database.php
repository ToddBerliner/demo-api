<?php
namespace Api\Config;
// Class to create and return a database connection
class Database
{
    private $dbConnection = null;

    private $host = "localhost";
    private $port = 3306;
    private $username = "tberliner";
    private $password = "tberliner";
    private $database = "shipwire";

    public function __construct() {
        try {
                $this->dbConnection = new \PDO(
                    "mysql:host=$this->host;port=$this->port;charset=utf8mb4;dbname=$this->database",
                    $this->username,
                    $this->password
                );
        } catch (\Exception $e) {
            // Fundamental error that the user/client
            // can't do anything about, return 500
            http_response_code(500);
            die();
        }
    }

    public function getDbConnection() {
        return $this->dbConnection;
    }
}