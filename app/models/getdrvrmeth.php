<?php

class GetDriver extends ConnectDatabase {
    protected function retrieveDriver($drvrid) {
        $sql = "SELECT * FROM driver
                WHERE driverid = :driverid";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':driverid', $drvrid);
        $stmt->execute();

        $result = $stmt->fetch();        
        return $result;
    }

    public function getDrvrStats($drvrid) {
        $this->retrieveDriver($drvrid);
    }
}

?>