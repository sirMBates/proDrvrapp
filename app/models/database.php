<?php

require_once "../vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../../.local.env');
$dotenv->load();
class ConnectDatabase {
    protected function connect() {
        $type = $_ENV['DB_TYPE'];
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $charset = $_ENV['DB_CHARSET'];
        $port = $_ENV['DB_PORT'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
        $dsn = "$type:host=$host;dbname=$dbname;charset=$charset;port=$port";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,           //Enable exceptions for errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      //Set default fetch mode to associative array
            PDO::ATTR_EMULATE_PREPARES => false,                   //Disable emulation of prepared statements
        ];
        try {
            $connection = new PDO($dsn, $username, $password, $options);
            return $connection;
        }
        catch (PDOException $error) {
            print "Error: " . $error->getMessage() . "<br>";
            die();
        }
    }
}
?>