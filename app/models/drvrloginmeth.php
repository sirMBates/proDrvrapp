<?php

//use Defuse\Crypto\Crypto;
//use Defuse\Crypto\Key;

require_once home_path("../vendor/autoload.php");

/*$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env');
$dotenv->load();*/

class Login extends ConnectDatabase {
    protected function getDriver($username, $password) {
        $stmt =$this->connect()->prepare("SELECT password FROM driver WHERE username = ? OR email = ?;");
        /*function getKeyFromEnv() {
            $keyAscii = $_ENV["ENCRYPT_SECRET_KEY"];
            return Key::loadFromAsciiSafeString($keyAscii);
        }
        $key = getKeyFromEnv();
        $cipherText = $username;
        $secret_data = Crypto::decrypt($cipherText, $key, $raw_binary = false);*/

        if (!$stmt->execute(array($username, $password))) {
            $stmt = null;
            header("Location: ../../public/views/drvrsignin.php?error=stmtfailed");
            exit();
        }

        if ($stmt->rowCount() === 0) {
            $stmt = null;
            header("Location: ../../public/views/drvrsignin.php?error=usernotfound");
            exit();
        }

        $hashedPsw = $stmt->fetchAll();
        $checkPsw = password_verify($password, $hashedPsw[0]["password"]);
        
        if ($checkPsw === false) {
            $stmt = null;
            header("Location: ../../public/views/drvrsignin.php?error=wrongpassword");
            exit();
        }

        elseif ($checkPsw === true) {
            $stmt =$this->connect()->prepare("SELECT * FROM driver WHERE username = ? OR email = ? AND password = ?;");

            if (!$stmt->execute(array($username, $username, $password))) {
                $stmt = null;
                header("Location: ../../public/views/drvrsignin.php?error=stmtfailed");
                exit();
            }

            if ($stmt->rowCount() === 0) {
                $stmt = null;
                header("Location: ../../public/views/drvrsignin.php?error=usernotfound");
                exit();
            }

            $driver = $stmt->fetchAll();
            session_start();
            $_SESSION['driver_id'] = $driver[0]['driverid'];
            $_SESSION['username'] = $driver[0]['username'];
            $_SESSION['first_name'] = $driver[0]['firstName'];
            $_SESSION['last_name'] = $driver[0]['lastName'];
            $_SESSION['birth_date'] = $driver[0]['birthdate'];

            $stmt = null;
        }

        $stmt = null;
    }
}
?>