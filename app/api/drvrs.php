<?php

header("Content-Type: application/json");
//header("Access-Control-Allow-Origin: *");
include_once base_path("core/database.php");
$db = new ConnectDatabase();

$sql = "SELECT username, email, firstname, lastname, mobileNum, birthdate
        FROM driver
        WHERE driverid = :driverid";
$stmt = $db->connect()->prepare($sql);
$stmt->bindParam(":driverid", $_SESSION['driver_id']);
$stmt->execute();

$result = $stmt->fetch();

echo json_encode($result);

$stmt = null;

?>