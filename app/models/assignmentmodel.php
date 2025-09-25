<?php

use core\Database;
use core\Alert;

class Assignment {
    public function insertAssignment(array $data): bool {
      $db = new Database();
      $alert = new Alert();
        $sql = "INSERT INTO work_orders (vehicle_id, operator_id, name, num_of_coaches,
                start_date_time, spot_time, leave_date_time, return_date_time, actual_drop_time,
                end_date_time, actual_end_time, total_job_hrs, driving_time, origin, destination,
                group_name, group_leader, group_leader_mobile, customer_name, customer_phone,
                contact_name, contact_mobile, location_pickup_details, destination_location_details,
                signature_required, signature, driver_notes
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    }
}
?>