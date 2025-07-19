<?php

use core\Flash;

class ResetPwd extends ConnectDatabase {
    protected function checkTokenExpiration($token) {
        $alert = new Flash();
        $sql = "SELECT * FROM pwdreset
                WHERE resetToken = :resetToken";                
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([
            ':resetToken' => $token
        ]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($driver === null) {
            $alert::setMsg('error', 'Uh-oh! An unexpected error occurred, please try again.');
            header("Location: /forget?error=not+found"); //stmtfailed
            exit();
        }

        if ($stmt->rowCount() > 0) {
            echo $driver['resetid'];
            /*if (strtotime($driver["tokenExpTime"]) <= time()) {
                echo "current time is less than timestamp in database.";
            $alert = new Flash(); 
            $alert::setMsg('validate', 'Token has expired! Please generate a new token below.');
            header("Location: /compreset?validate=expired");
            exit();
            } else {
                echo "The time is less than.";
            }*/
        } else {
            echo "nothing is returning.";
        }
        $driver = null;
    }

    /*protected function updateToken($newToken, $tokenExpTime, $oldToken) {
        $sql = "UPDATE pwdreset
                SET resetToken = :newToken, tokenExpTime = :tokenExpTime 
                WHERE resetToken = :oldToken";
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
