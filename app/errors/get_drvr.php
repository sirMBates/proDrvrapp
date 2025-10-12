<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

class GetDrvrContr extends GetDriver {
        public function driverInfo() {
                try {
                        $drvrProfile = new GetDriver();
                        $driver = htmlspecialchars(trim($_SESSION['driver_id']));
                        $operator = $drvrProfile->getDrvrInfo($driver);
                        //print_r($stats);
                        echo json_encode($operator);
                        exit();
                } catch (Exception $e) {
                        http_response_code(404);
                        echo json_encode([
                                'status' => 'error',
                                'message' => 'There was a problem: ' . $e->getMessage()
                        ]);
                        exit();
                }
        }
}

?>