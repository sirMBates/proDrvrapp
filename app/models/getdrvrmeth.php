<?php

use core\Database;
use core\Flash;

class GetDriver {
    protected function retrieveDriver($drvrid) {
        $db = new Database;
        $alert = new Flash();
        $sql = "SELECT driverid, username, email, firstname, lastname, mobileNumber, birthdate FROM driver
                WHERE driverid = :driverid";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':driverid', $drvrid);
        $stmt->execute();

        $result = $stmt->fetch();

        if (!$stmt || $stmt->rowCount() === 0) {
            $alert::setMsg('error', 'There seems to be a problem. Try again later.');
            header("location: /?error=try+again");
            exit();
        }

        return $result;
    }

    public function getDrvrStats($drvrid) {
        return $this->retrieveDriver($drvrid);
    }
}

?>