<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

class GetWorkContr extends WorkAssignments {
    public function workInformation() {
        try {
            $jobAssignment = new WorkAssignments();
            $operator = htmlspecialchars(trim($_SESSION['driver_id']));
            $assignments = $jobAssignment->driverWorkAssignments($operator);
            dd($assignments);
            echo json_encode([
                'status' => 'success',
                'data' => $assignments
            ]);
            exit();
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'There was a problem: ' . $e->getMessage('There are no assignments currently.') 
            ]);
            exit();
        }
    }
}

?>