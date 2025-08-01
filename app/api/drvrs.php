<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

include_once base_path("core/database.php");

session_start();

if (!isset($_SESSION['driver_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Driver ID not set in session.']);
    exit;
}

$sql = "SELECT username, email, firstname, lastname, mobileNum, birthdate
        FROM driver
        WHERE driverid = :driverid";
$stmt = ConnectDatabase()->connect()->prepare($sql);
$stmt->bindParam(":driverid", $_SESSION['driver_id']);
$stmt->execute();

$result = $stmt->fetch();
$data = [];
while ($row = $result) {
        $data[] = $row;
}

echo json_encode($data);

$stmt = null;

?>