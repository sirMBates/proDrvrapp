<?php

use core\Flash;

class ResetPwd extends ConnectDatabase {
    protected function checkTokenandExpiration($token) {
        $alert = new Flash();
        $sql = "SELECT * FROM pwdreset
                WHERE resetToken = :resetToken";                
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([
            ':resetToken' => $token
        ]);

        if (!$stmt || $stmt->rowCount() === 0) {
            $alert::setMsg('error', 'You have an invalid token. Please generate a new token below.');
            header("Location: /forget?error=not+found"); //no token found
            exit();
        }

        $driver = $stmt->fetchAll();
        $dbTimeStamp = strtotime($driver[0]["tokenExpTime"]);
        $currentTime = time();
        if ($dbTimeStamp <= $currentTime) {
            $alert::setMsg('validate', 'Token has expired! Please generate a new token below.');
            header("Location: /forget?validate=expired");
            exit();
        }
        $driver = null;
    }

    /*protected function updateToken($newToken, $tokenExpTime, $oldToken) {
        $sql = "UPDATE pwdreset
                SET resetToken = :newToken, tokenExpTime = :tokenExpTime 
                WHERE resetToken = :oldtoken";
        $stmt = $this->connect()->prepare($sql);

        $newTokenSetup = $stmt->execute([
            ':newToken' => $newToken,
            ':tokenExpTime' => $tokenExpTime,
            ':oldToken' => $oldToken
        ]);

        if ($newTokenSetup === null) {
            $alert = new Flash();
            $alert::setMsg('error', 'Uh-oh! An unexpected error occurred, please try again.');
            header("Location: /forget?error=not+available");
            exit();
        }

        /*if (!$newTokenSetup) {
            $alert = new Flash();
            $alert::setMsg('error', 'No new token! Token has been sent or already used.');
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
    }*/
}
