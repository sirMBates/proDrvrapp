<?php

use core\Database;

class Assignment {
    protected string $logFile;

    public function __construct($logFile = 'D:/webapps/logs/job_import_master.log') {
        $this->logFile = $logFile;
    }

    public function insertAssignment(array $data): bool {
        $db = new Database();
        $pdo = $db->connect();
        try {
            // Step 1: Pre-check for duplicate assignment
            $checkSql = "SELECT COUNT(*) FROM work_orders
                        WHERE vehicle_id = :vehicle_id
                        AND start_date_time = :start_date_time";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([
                ':vehicle_id' => $data['vehicle_id'] ?? null,
                ':start_date_time' => $data['start_date_time'] ?? null
            ]);

            if ($checkStmt->fetchColumn() > 0) {
                // Duplicate found - stop insert
                /*error_log("Duplicate assignment blocked: vehicle {$data['vehicle_id']} at {$data['start_date_time']}");
                return 'duplicate';*/
                $this->writeLog("DUPLICATE: Vehicle {$data['vehicle_id']} at {$data['start_date_time']} skipped");
                return 'duplicate';
            }
        $sql = "INSERT INTO work_orders (vehicle_id, operator_id, operator_name, num_of_coaches,
                start_date_time, spot_time, leave_date_time, return_date_drop_time, actual_drop_time,
                end_date_time, actual_end_time, total_job_time, driving_time, origin, destination,
                group_name, group_leader, group_leader_mobile, customer_name, customer_phone,
                contact_name, contact_mobile, pickup_details, destination_details,
                signature_required, signature_path, driver_notes
            ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                        ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            // Loop over each and set each property and value.
            /*for ($i=1; $i<=27; $i++) {
                $stmt->bindValue($i, $data[array_keys($data)[$i-1]] ?? null);
            }*/
            $stmt->bindValue(1, $data['vehicle_id'] ?? null);
            $stmt->bindValue(2, $data['operator_id'] ?? null);
            $stmt->bindValue(3, $data['operator_name'] ?? null);
            $stmt->bindValue(4, $data['num_of_coaches'] ?? null);
            $stmt->bindValue(5, $data['start_date_time'] ?? null);
            $stmt->bindValue(6, $data['spot_time'] ?? null);
            $stmt->bindValue(7, $data['leave_date_time'] ?? null);
            $stmt->bindValue(8, $data['return_date_time'] ?? null);
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
            $stmt->bindValue(25, $data['signature_required'] ?? 0);
            $stmt->bindValue(26, $data['signature_path'] ?? null);
            $stmt->bindValue(27, $data['driver_notes'] ?? null);
            $dataInserted = $stmt->execute();

            if ($dataInserted) {
                $this->writeLog("SUCCESS: Inserted vehicle {$data['vehicle_id']} at {$data['start_date_time']}");
                return true;
            } else {
                $this->writeLog("FAILURE: Vehicle {$data['vehicle_id']} at {$data['start_date_time']} - Execute returned false");
                return false;
            }
            
        } catch (PDOException $e) {
            $this->writeLog("FAILURE: Vehicle {$data['vehicle_id']} at {$data['start_date_time']} - Error: " . $e->getMessage());
            return false;
        }
    }

    protected function writeLog(string $message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
}

?>