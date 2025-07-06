<?php
use core\Flash;

class RegProInfo extends ConnectDatabase {
    protected function addDriverDetails($firstname, $lastname, $mobileNum, $birthdate, $username) {
        $sql = "UPDATE driver SET firstName = ?, lastName = ?, mobileNumber = ?, birthdate = ? WHERE username = ?";

        $stmt = $this->connect()->prepare($sql);

        if (!$stmt->execute(array($firstname, $lastname, $mobileNum, $birthdate, $username))) {
            $alert = new Flash();
            $stmt = null;
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /register?error=try+again"); //stmtfailed
            exit();
        }
        $stmt = null;
    }
}
?>