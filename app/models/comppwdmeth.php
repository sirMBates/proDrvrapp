<?php

use core\Flash;

class CompleteReset extends ConnectDatabase {
    protected function checkValidToken($token) {
        $alert = new flash();
        $sql = "SELECT * FROM pwdreset
                WHERE resetToken = :resetToken";
        $stmt = $this->connect()->prepare($sql);
        //var_dump($token);
        $stmt->execute([
            ':resetToken' => $token
        ]);

        if (!$stmt || $stmt->rowCount() === 0) {
            return null;
        }

        $driver = $stmt->fetch();
        $dbTimeStamp = strtotime($driver['tokenExpTime']);
        $currentTime = time();
        if ($dbTimeStamp <= $currentTime) {
            return false;
        }
        return $driver;
    }

    protected function updatePassword($token, $password) {
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
        $stmt = $this->connect()->prepare($sql);
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
        $alert = new Flash();
        $resetData = $this->checkValidToken($token);
        if ($resetData === null) {
            $alert::setMsg('warning', 'Token not found. Please try again!');
            return false;
        }
        $sql = "UPDATE pwdreset
                SET resetToken = '', tokenExpTime = null
                WHERE resetToken = :resetToken";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([
            ':resetToken' => $token
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