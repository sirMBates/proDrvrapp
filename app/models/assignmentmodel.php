<?php

use core\Logger;
use core\Database;

class Assignment {
    protected Logger $logger;

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    public function insertAssignment(array $data): bool|string {
        $db = new Database();
        $pdo = $db->connect();

        try {
            // Step 1: Pre-check for duplicate assignment
            // Prevents identical assignment records for the same vehicle, start time, driver & order ref
            $dupCheckSql = "SELECT COUNT(*) as cnt
                            FROM work_orders
                            WHERE vehicle_id = :vehicle_id
                            AND start_date_time = :start_date_time
                            AND driver_id = ( SELECT driver_id FROM drivers
                                            WHERE operator_id = :operator_id LIMIT 1)
                                            AND order_ref = :order_ref";
            $dupStmt = $pdo->prepare($dupCheckSql);
            $dupStmt->execute([
                ':vehicle_id' => $data['vehicle_id'] ?? null,
                ':start_date_time' => $data['start_date_time'] ?? null,
                ':operator_id' => $data['operator_id'] ?? null,
                ':order_ref' => $data['order_ref'] ?? null
            ]);
            $exists = $dupStmt->fetchColumn();
            if ($exists > 0) {
                $this->logger->warning("Skipped duplicate assignment - Vehicle {$data['vehicle_id']} at {$data['start_date_time']} for driver with operator id {$data['operator_id']} and order ref {$data['order_ref']}");
                return 'duplicate';
            }

            //$this->logger->debug("Looking up driver with operator_id='{$operatorId}'");
            $driverSql = "SELECT driver_id, first_name, last_name 
                        FROM drivers
                        WHERE operator_id = :operator_id
                        LIMIT 1";
            $driverStmt = $pdo->prepare($driverSql);
            $operatorId = isset($data['operator_id']) ? trim($data['operator_id']) : null;
            $driverStmt->bindValue(':operator_id', $operatorId, PDO::PARAM_STR);
            $driverStmt->execute();
            $driverFound = $driverStmt->fetch();

            if (!$driverFound) {
                $this->logger->log("❌ FAILURE: No driver found for operator_id: {$data['operator_id']}");
                return 'driver_not_found';
            }

            $sql = "INSERT INTO work_orders (order_ref, vehicle_id, driver_id, num_of_coaches, start_date_time, spot_time, 
                    leave_date_time, return_date_drop_time, actual_drop_time, end_date_time, actual_end_time, total_job_time, driving_time, origin, destination, group_name, group_leader, group_leader_mobile, customer_name, customer_phone, contact_name, contact_mobile, pickup_details, destination_details, driver_notes,
                    signature_required, pre_signature_path, post_signature_path)

                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                            ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            // Loop over each and set each property and value.
            /*for ($i=1; $i<=27; $i++) {
                $stmt->bindValue($i, $data[array_keys($data)[$i-1]] ?? null);
            }*/
            $stmt->bindValue(1, $data['order_ref']);
            $stmt->bindValue(2, $data['vehicle_id'] ?? null);
            $stmt->bindValue(3, $driverFound['driver_id'], \PDO::PARAM_INT); // driver_id inserted
            $stmt->bindValue(4, $data['num_of_coaches'] ?? null);
            $stmt->bindValue(5, $data['start_date_time'] ?? null);
            $stmt->bindValue(6, $data['spot_time'] ?? null);
            $stmt->bindValue(7, $data['leave_date_time'] ?? null);
            $stmt->bindValue(8, $data['return_date_drop_time'] ?? null);
            $stmt->bindValue(9, $data['actual_drop_time'] ?? null);
            $stmt->bindValue(10, $data['end_date_time'] ?? null);
            $stmt->bindValue(11, $data['actual_end_time'] ?? null);
            $stmt->bindValue(12, $data['total_job_time'] ?? null);
            $stmt->bindValue(13, $data['driving_time'] ?? null);
            $stmt->bindValue(14, $data['origin'] ?? null);
            $stmt->bindValue(15, $data['destination'] ?? null);
            $stmt->bindValue(16, $data['group_name'] ?? null);
            $stmt->bindValue(17, $data['group_leader'] ?? null);
            $stmt->bindValue(18, $data['group_leader_mobile'] ?? null);
            $stmt->bindValue(19, $data['customer_name'] ?? null);
            $stmt->bindValue(20, $data['customer_phone'] ?? null);
            $stmt->bindValue(21, $data['contact_name'] ?? null);
            $stmt->bindValue(22, $data['contact_mobile'] ?? null);
            $stmt->bindValue(23, $data['pickup_details'] ?? null);
            $stmt->bindValue(24, $data['destination_details'] ?? null);
            $stmt->bindValue(25, $data['driver_notes'] ?? null);
            $stmt->bindValue(26, $data['signature_required'] ?? 0);
            $stmt->bindValue(27, $data['pre_signature'] ?? null);
            $stmt->bindValue(28, $data['post_signature'] ?? null);
            $dataInserted = $stmt->execute();

            if ($dataInserted) {
                $this->logger->info("✅ SUCCESS: Inserted assignment for {$driverFound['first_name']} {$driverFound['last_name']} assigned to vehicle {$data['vehicle_id']} at {$data['start_date_time']} with order ref {$data['order_ref']}");
                return true;
            } else {
                $this->logger->error("❌ Assignment Insert FAILURE: Vehicle {$data['vehicle_id']} at {$data['start_date_time']} - Execute returned false");
                return false;
            }
            
        } catch (\PDOException $e) {
            $this->logger->error("❌ Assignment Insert FAILURE: Vehicle {$data['vehicle_id']} at {$data['start_date_time']} - Error: " . $e->getMessage());
            return false;
        }
    }
}

?>