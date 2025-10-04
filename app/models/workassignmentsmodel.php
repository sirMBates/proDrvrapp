<?php

use core\Database;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;
require_once "../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.local.env');
$dotenv->load();

class WorkAssignments {
    protected function getWork($drvrid) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $pdo = $db->connect();
        $sql = "SELECT wo.*, d.operator_id, d.first_name, d.last_name
                FROM work_orders wo INNER JOIN driver d ON wo.driver_id = d.driver_id
                WHERE wo.driver_id = :driver_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':driver_id', $drvrid);
        $stmt->execute();

        if (!$stmt) {
            throw new Exception("Driver not found");
        }

        $results = $stmt->fetchAll();

        foreach ($results as &$row) {
            try {
                $row['first_name'] = Crypto::decrypt($row['first_name'], $key);
                $row['last_name'] = Crypto::decrypt($row['last_name'], $key);
                if (isset($row['signature_required']) && $row['signature_required'] === true) {
                    $_SESSION['signature_required'] = $row['signature_required'];
                }
            } catch (\Exception $e) {
                // Handle corrupted or missing ciphertext
                $row['first_name'] = null;
                $row['last_name'] = null;
            }
        }
        //unset($row); // break the reference
        $stmt->closeCursor();
        return $results;
    }

    public function driverWorkAssignments ($drvrid) {
        return $this->getWork($drvrid);
    }
}

?>