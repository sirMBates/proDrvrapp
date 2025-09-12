<?php

use core\Database;
use core\Flash;

class CompleteReset {
    protected function checkValidToken($token) {
        $db = new Database;
        $alert = new Flash();
        $sql = "SELECT * FROM pwd_reset
                WHERE reset_token = :reset_token";
        $stmt = $db->connect()->prepare($sql);
        //var_dump($token);
        $stmt->execute([
            ':reset_token' => $token
        ]);

        if (!$stmt || $stmt->rowCount() === 0) {
            return null;
        }

        $driver = $stmt->fetch();
        $dbTimeStamp = strtotime($driver['token_exp_time']);
        $currentTime = time();
        if ($dbTimeStamp <= $currentTime) {
            return false;
        }
        return $driver;
    }

    protected function updatePassword($token, $password) {
        $db = new Database;
        $alert = new Flash();
        $driverResetToken = $this->checkValidToken($token);
        if ($driverResetToken === null) {
            $alert::setMsg('danger', 'Token is invalid! Please generate a new token.');
            header("Location: /forget?danger=invalid");
            exit();
        }

        if ($driverResetToken === false) {
            $alert::setMsg('validate', 'Your token has expired. Please generate a new token.');
            header("Location: /forget?validate=expired");
            exit();
        }
        //var_dump($driverResetToken);
        $driverEmail = $driverResetToken['email'];
        $newHashPwd = password_hash($password, PASSWORD_BCRYPT);

        $sql = "UPDATE driver
                SET password = :password
                WHERE email = :email";
        $stmt = $db->connect()->prepare($sql);
        $stmt->execute([
            ':password' => $newHashPwd,
            ':email' => $driverEmail
        ]);


        if (!$stmt || $stmt->rowCount() === 0) {
            $alert::setMsg('danger', 'Unfortunately, the process didn\'t complete. Please, try again!');
            header("Location: /forget?danger=not+cleared");
            exit();
        }
    }
    
    protected function clearToken($token) {
        $db = new Database;
        $alert = new Flash();
        $resetData = $this->checkValidToken($token);
        if ($resetData === null) {
            $alert::setMsg('warning', 'Token not found. Please try again!');
            return false;
        }
        $sql = "UPDATE pwd_reset
                SET reset_token = '', token_exp_time = NULL
                WHERE reset_token = :reset_token";
        $stmt = $db->connect()->prepare($sql);
        $stmt->execute([
            ':reset_token' => $token
        ]);
        
        $result;
        if (!$stmt || $stmt->rowCount() === 0) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }
}

?>