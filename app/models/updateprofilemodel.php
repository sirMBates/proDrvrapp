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
        $sql = "UPDATE drivers
                SET password = :password
                WHERE driver_id = :driver_id";
        $stmt = $db->connect()->prepare($sql);
        $hashPwd = password_hash($password, PASSWORD_BCRYPT, ['cost' => 16]);
        $stmt->bindParam(':driver_id', $drvrid);
        $stmt->bindParam(':password', $hashPwd);

        $result = $stmt->execute();

        if (!$result) {
            $alert::setMsg('error', 'The request was not completed. Please try again.');
            header("Location: /profile?error=try+again");
            exit();
        }
        $stmt = null;
    }

    protected function drvrUpdateData($drvrid, $drvrEmail, /*$drvrBirthDate,*/$drvrMobile) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $alert = new Flash();
        $fields = [];
        $params = [];

        if ($drvrEmail !== null && $drvrEmail !== '') {
            $fields[] = "email = :email";
            $params[':email'] = $drvrEmail;
        }

        /*if ($drvrBirthDate !== null && $drvrBirthDate !== '') {
            $encryptedBirthDate = Crypto::encrypt($drvrBirthDate, $key);
            $fields[] = "birth_date = :birth_date";
            $params[':birth_date'] = $encryptedBirthDate;
        }*/

        if ($drvrMobile !== null && $drvrMobile !== '') {
            $encryptedMobileNum = Crypto::encrypt($drvrMobile, $key);
            $fields[] = "mobile_number = :mobile_number";
            $params[':mobile_number'] = $encryptedMobileNum;
        }

        if (count($fields) > 0) {
            $sql = "UPDATE drivers SET ". 
            implode(", ", $fields). " 
            WHERE driver_id = :driver_id";
            $params[':driver_id'] = $drvrid;
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
        $sql = "SELECT * FROM drivers
                WHERE driver_id = :driver_id";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':driver_id', $drvrid);
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