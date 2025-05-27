<?php
//use Defuse\Crypto\Crypto;
//use Defuse\Crypto\Key;
require_once base_path("vendor/autoload.php");
//$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env');
//$dotenv->load();
class AddedDrvr extends ConnectDatabase {
    protected function setDriver($username, $email, $password, $firstname, $lastname, $mobileNum, $birthdate) {
        $stmt = $this->connect()->prepare("INSERT INTO driver (username, email, password, firstName, lastName, mobileNumber, birthdate) VALUES (?,?,?,?,?,?,?);");

        $hashPsW = password_hash($password, PASSWORD_BCRYPT);

        /*function getKeyFromEnv() {
            $keyAscii = $_ENV["ENCRYPT_SECRET_KEY"];
            return Key::loadFromAsciiSafeString($keyAscii);
        }
        $key = getKeyFromEnv();
        $secret_data = $username;
        $cipherText = Crypto::encrypt($secret_data, $key, $raw_binary = false);*/

        if (!$stmt->execute(array($username, $email, $hashPsW, $firstname, $lastname, $mobileNum, $birthdate))) { 
            $stmt = null;
            header("Location: /?error=stmtfailed");
            exit();
        }

        $stmt = null;
    }

    protected function checkDriver($username, $email) {
        $stmt =$this->connect()->prepare('SELECT driverid FROM driver WHERE username = ? OR email = ?;');
        if (!$stmt->execute(array($username, $email))) {
            $stmt = null;
            header("Location: /?error=stmtfailed");
            exit();
        }

        $resultCheck;
        if ($stmt->rowCount() > 0) {
            $resultCheck = false;
        }
        else {
            $resultCheck = true;
        }
        return $resultCheck;
    }
}

?>