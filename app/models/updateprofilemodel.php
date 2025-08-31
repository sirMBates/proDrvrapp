<?php

use core\Database;
use core\Flash;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;
require_once "../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.local.env');
$dotenv->load();

class UpdateDrvr {
    protected function drvrPwdUpdate($drvrid, $password) {
        $db = new Database;
        $alert = new Flash();
        $sql = "UPDATE Driver
                SET password = :password
                WHERE driverid = :driverid";
        $stmt = $db->connect()->prepare($sql);
        $hashPwd = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bindParam(':driverid', $drvrid);
        $stmt->bindParam(':password', $hashPwd);

        $result = $stmt->execute();

        if (!$result) {
            $alert::setMsg('error', 'The request was not completed. Please try again.');
            header("Location: /profile?error=try+again");
            exit();
        }
        $stmt = null;
    }

    protected function drvrUpdateData($drvrid, $drvrEmail, $drvrMobile) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $alert = new Flash();
        $fields = [];
        $params = [];

        if ($drvrEmail !== null && $drvrEmail !== '') {
            $fields[] = "email = :email";
            $params[':email'] = $drvrEmail;
        }

        if ($drvrMobile !== null && $drvrMobile !== '') {
            $encryptedMobileNum = Crypto::encrypt($drvrMobile, $key);
            $fields[] = "mobileNumber = :mobileNumber";
            $params[':mobileNumber'] = $encryptedMobileNum;
        }

        if (count($fields) > 0) {
            $sql = "UPDATE Driver SET ". 
            implode(", ", $fields). " 
            WHERE driverid = :driverid";
            $params[':driverid'] = $drvrid;
            $stmt = $db->connect()->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $result = $stmt->execute();

            if (!$result) {
                $alert::setMsg('error', 'The request was not completed. Please try again.');
                header("Location: /profile?error=try+again");
                exit();
            }

            $result = $stmt->rowCount() > 0;
            $stmt = null;
        }
    }

    protected function drvrExist($drvrid) {
        $db = new Database;
        $alert = new Flash();
        $sql = "SELECT * FROM Driver
                WHERE driverid = :driverid";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':driverid', $drvrid);
        $stmt->execute();

        $result = $stmt->fetch();

        if (!$result) {
            $alert::setMsg('error', 'We hit a snag! Please try again.');
            header("Location: /profile?error=try+again");
            exit();
        }

        $checkResult;
        if ($stmt->rowCount() === 0) {
            $checkResult = false;
        } else {
            $checkResult = true;
        }
        return $checkResult;
    }
}

?>