<?php

use core\Flash;

class ResetPwd extends ConnectDatabase {
    protected function checkTokenExpiration($token) {
        $alert = new Flash();
        $sql = "SELECT * From driver
                WHERE resetToken = :resetToken";                
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':resetToken', $token);

        $stmt->execute();
        $driver = $stmt->fetchAll();

        if (!$driver) {
            $stmt = null;
            $alert::setMsg('error', 'Uh-oh! An unexpected error occurred, please try again.');
            header("Location: /resetpwd?error=not+found"); //stmtfailed
            exit();
        }
        
        if (strtotime($driver["tokenExpTime"]) <= time()) {
            $alert::setMsg('validate', 'Token has expired! Please generate a new token below.');
            header("Location: /compreset?validate=expired");
            exit();
        }
    }

    protected function getNewToken($token, $tokenExpTime) {
        $alert = new Flash();
        $sql = "UPDATE driver
                SET resetToken = ?, tokenExpTime = ? 
                WHERE resetToken = ?";
        $stmt = $this->connect()->prepare($sql);

        $newTokenSetup = $stmt->execute([
            ':resetToken' => $token,
            ':tokenExpTime' => $tokenExpTime
        ]);

        if (!$newTokenSetup) {
            $stmt = null;
            $alert::setMsg('error', 'Uh-oh! An unexpected error occurred, please try again.');
            header("Location: /forget?error=not+available"); //stmtfailed
            exit();
        }

        $result = $stmt->rowCount();
        $resultCheck;
        if ($result === 0) {
            $resultCheck = false;
        } else {
            $resultCheck = true;
        }
        return $resultCheck;
    }
}