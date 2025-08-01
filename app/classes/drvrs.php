<?php

if (session_status() !== 2) {
    session_start();
}

class GetDrvrController {
        public function driverInfo() {
                $drvrProfile = new GetDriver();
                $stats = $drvrProfile->getDrvrStats($_SESSION['driver_id']);
                header("Content-Type: application/json");
                //header("Access-Control-Allow-Origin: *");
                echo json_encode($stats);
                exit();
        }
}

?>