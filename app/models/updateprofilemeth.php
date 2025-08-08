<?php

use core\Flash;

class UpdateDrvrPwd extends ConnectDatabase {
    protected function drvrPwdUpdate($drvrid, $password) {
        $alert = new Flash();
        $sql = "UPDATE driver
                SET password = :password
                WHERE driverid = :driverid";
        $stmt = $this->connect()->prepare($sql);
        $hashPwd = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bindParam(':driverid', $drvrid);
        $stmt->bindParam(':password', $hashPwd);

        $result = $stmt->execute();

        if (!$result) {
            $alert::setMsg('error', 'The request was not completed. Please try again.');
            header("Location: /profile?error=try+again");
            exit();
        }
        $stmt = null;
    }

    protected function drvrExist($drvrid) {
        $alert = new Flash();
        $sql = "SELECT * FROM driver
                WHERE driverid = :driverid";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':driverid', $drvrid);
        $stmt->execute();

        $result = $stmt->fetch();

        if (!$result) {
            $alert::setMsg('error', 'We hit a snag! Please try again.');
            header("Location: /profile?error=try+again");
            exit();
        }

        $checkResult;
        if ($stmt->rowCount() === 0) {
            $checkResult = false;
        } else {
            $checkResult = true;
        }
        return $checkResult;
    }
}

?>