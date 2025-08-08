<?php

class GetDrvrController {
        public function driverInfo() {
                $drvrProfile = new GetDriver();
                $driver = htmlspecialchars(trim($_SESSION['driver_id']));
                $stats = $drvrProfile->getDrvrStats($driver);
                header("Content-Type: application/json");
                header("Access-Control-Allow-Origin: *");
                //print_r($stats);
                echo json_encode($stats);
                exit();
        }
}

?>