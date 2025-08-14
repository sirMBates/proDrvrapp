<?php

use core\Database;
use core\Flash;

class RegProInfo {
    protected function addDriverDetails($firstname, $lastname, $mobileNum, $birthdate, $username) {
        $db = new Database;
        $alert = new Flash();
        $sql = "UPDATE driver 
                SET firstName = ?, lastName = ?, mobileNumber = ?, birthdate = ? 
                WHERE username = ?";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(1, $firstname);
        $stmt->bindParam(2, $lastname);
        $stmt->bindParam(3, $mobileNum);
        $stmt->bindParam(4, $birthdate);
        $stmt->bindParam(5, $username);

        $result = $stmt->execute();
        if (!$result) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /register?error=try+again"); //stmtfailed
            exit();
        }
        $stmt = null;
    }
}
?>