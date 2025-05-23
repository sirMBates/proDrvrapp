<?php
require_once "../vendor/autoload.php";
class DrvrProfileEntry extends ConnectDatabase {
    protected function addDriverDetails($firstname, $lastname, $mobileNum, $birthdate, $username) {
        $sql = "UPDATE driver SET firstName = ?, lastName = ?, mobileNumber = ?, birthdate = ? WHERE username = ?";

        $stmt = $this->connect()->prepare($sql);

        if (!$stmt->execute(array($firstname, $lastname, $mobileNum, $birthdate, $username))) {
            $stmt = null;
            header("Location: ../../public/views/drvrinfo.php?error=stmtfailed");
            exit();
        }
        $stmt = null;
    }
}
?>