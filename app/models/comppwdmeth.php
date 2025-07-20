<?php

use core\Flash;

class CompleteReset extends ConnectDatabase {
    protected function checkTokenIsValid($token) {
        $alert = new Flash();
        $sql = "SELECT * FROM pwdreset
                WHERE resetToken = :resetToken";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([
            ':resetToken' => $token
        ]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($driver === null) {
            $alert::setMsg('error', 'Token not found. Please try again.');
            header("Location: /forget?error=not+found"); //stmtfailed
            exit();
        }

        if ($stmt->rowCount() > 0) {
            if (strtotime($driver["tokenExpTime"]) <= time()) {
                $alert = new Flash(); 
                $alert::setMsg('validate', 'Token has expired! Please generate a new token below.');
                header("Location: /forget?validate=expired");
                exit();
            } 
        }
        $driver = null;
    }

    protected function updatePassword($token, $password) {
        $alert = new Flash();
        $sql = "SELECT * FROM pwdreset
                WHERE resetToken = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([
            ':resetToken' => $token
        ]);

        if ($stmt === null) {
            $alert::setMsg('error', 'There was an issue completing the reset. Please, try again.');
            header("Location: /compreset?error=try+again");
            exit();
        }

        $driver = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if (strtotime($driver["tokenExpTime"]) <= time()) { 
                $alert::setMsg('validate', 'Token has expired! Please generate a new token below.');
                header("Location: /forget?validate=expired");
                exit();
            } 
        }

        $driverEmail = $driver['email'];
        $newHashPwd = password_hash($password, PASSWORD_BCRYPT);

        $sql2 = "UPDATE driver
                SET password = ?
                WHERE email = ?";
        $stmt2 = $this->connect()->prepare($sql2);
        $stmt2->execute([
            ':password' => $newHashPwd,
            ':email' => $driverEmail
        ]);

        if ($stmt2 === null ) {
            $alert::setMsg('danger', 'Unfortunately, the process didn\'t complete. Please, try again!');
            header("Location: /compreset?danger=unchanged");
            exit();
        }

        $result;
        if (!$stmt) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }
    
    protected function clearToken($token) {
        $alert = new Flash();
        $sql = "UPDATE pwdreset
                SET resetToken = null, tokenExpTime = null
                WHERE resetToken = :resetToken";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([
            ':resetToken' => $token
        ]);

        if ($stmt === null) {
            $alert::setMsg('error', 'A problem has risen. Please, try again.');
            header("Location: /signin?error=not+cleared");
            exit();
        }

        $result;
        if (!$stmt) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }
}

?>