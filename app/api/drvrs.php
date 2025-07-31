<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include_once base_path("core/database.php");
$db = new ConnectDatabase();

$sql = "SELECT username, email, firstname, lastname, mobileNum, birthdate
        FROM driver
        WHERE driverid = :driverid";
$stmt = $db->connect()->prepare($sql);
$stmt->execute([
    "driverid" =>  $_SESSION['driver_id'] 
])

$data = [];
if ($stmt->rowCount() > 0) {
    while($row = $stmt->fetch()) {
        $data[] = $row;
    }
}

echo json_encode($data);

$stmt = null;
?>