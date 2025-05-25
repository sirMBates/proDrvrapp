<?php
require_once base_path("../vendor/autoload.php");
class DrvrProfileEntry extends ConnectDatabase {
    protected function addDriverDetails($firstname, $lastname, $mobileNum, $birthdate, $username) {
        $sql = "UPDATE driver SET firstName = ?, lastName = ?, mobileNumber = ?, birthdate = ? WHERE username = ?";

        $stmt = $this->connect()->prepare($sql);

        if (!$stmt->execute(array($firstname, $lastname, $mobileNum, $birthdate, $username))) {
            $stmt = null;
            header("Location: /register?error=stmtfailed");
            exit();
        }
        $stmt = null;
    }
}
?>