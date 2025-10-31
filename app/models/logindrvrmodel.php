<?php

use core\Database;
use core\Flash;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;
require_once "../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.local.env');
$dotenv->load();

class Login {
    protected function getDriver($username, $password) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $alert = new Flash();
        $sql = "SELECT password FROM drivers
                WHERE username = :username";
        $stmt = $db->connect()->prepare($sql);
        
        $stmt->bindParam(":username", $username);
        $stmt->execute();        

        if (!$stmt || $stmt->rowCount() === 0) {
            $alert::setMsg('error', 'User not found. Please check your username.');
            header("Location: /signin?error=not+found"); // noRegisteredUseraccount
            exit();
        }

        $hashedPsw = $stmt->fetchAll();
        $checkPsw = password_verify($password, $hashedPsw[0]["password"]);
        
        if ($checkPsw === false) {
            $alert::setMsg('danger', 'Incorrect password. Please try again.');
            header("Location: /signin?danger=invalid"); // wrongPassword
            exit();
        } elseif ($checkPsw === true) {
            $sql2 = "SELECT * FROM driver
                    WHERE username = :username";
            $stmt = $db->connect()->prepare($sql2);
            $stmt->bindParam(":username", $username);
            $stmt->execute();

            $driver = $stmt->fetch();
            //session_start();
            $encryptedBirthdate = $driver['birth_date'];
            $encryptedFirstName = $driver['first_name'];
            $dbBirthdate = Crypto::decrypt($encryptedBirthdate, $key);
            $dbFirstName = Crypto::decrypt($encryptedFirstName, $key);
            $_SESSION['driver_id'] = $driver['driver_id'];
            $_SESSION['first_name'] = $dbFirstName;
            $currentDate = date('md');
            $drvrDate = date('md', strtotime($dbBirthdate));
            if ($currentDate === $drvrDate) {
                $_SESSION['birth_date'] = $dbBirthdate;
            }
            $_SESSION['logged_in'] = true;

            $stmt = null;
        }

        $stmt = null;
    }
}
?>