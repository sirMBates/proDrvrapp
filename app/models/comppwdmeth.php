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

    protected function updateDriverPassword($password) {}
}

?>