<?php

class GetDrvrController {
        public function driverInfo() {
                $drvrProfile = new GetDriver();
                $stats = $drvrProfile->getDrvrStats($_SESSION['driver_id']);
                header("Content-Type: application/json");
                header("Access-Control-Allow-Origin: *");
                //print_r($stats);
                echo json_encode($stats);
                exit();
        }
}

?>