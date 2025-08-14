<?php

namespace core;
use Dotenv\Dotenv;
require_once "../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__, '../.local.env');
$dotenv->load();

class Database {
    private $type; 
    private $host; 
    private $dbname; 
    private $charset; 
    private $port; 
    private $username;
    private $password;
    private $connection;

    public function __construct() {
        $this->type = $_ENV['DB_TYPE'];
        $this->host = $_ENV['DB_HOST'];
        $this->dbname = $_ENV['DB_NAME'];
        $this->charset = $_ENV['DB_CHARSET'];
        $this->port = $_ENV['DB_PORT'];
        $this->username = $_ENV['DB_USERNAME'];
        $this->password = $_ENV['DB_PASSWORD'];

        $dsn = "{$this->type}:host={$this->host};dbname={$this->dbname};charset={$this->charset};port={$this->port}";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,           //Enable exceptions for errors
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,      //Set default fetch mode to associative array
            \PDO::ATTR_EMULATE_PREPARES => false,                   //Disable emulation of prepared statements
        ];
        try {
            $this->connection = new \PDO($dsn, $this->username, $this->password, $options);
        }
        catch (\PDOException $error) {
            print "Error: " . $error->getMessage() . "<br>";
            die();
        }
    }

    public function connect() {
        return $this->connection;
    }
}
?>