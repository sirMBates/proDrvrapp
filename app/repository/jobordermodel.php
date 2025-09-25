<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__ . '/../../vendor/autoload.php';

// Absolute Windows path to file
$filePathName = 'C:/Users/bates/OneDrive/Documents/testworkassignment.xlsx';

// Check if the file exists before trying to load it
if (!file_exists($filePathName)) {
    die("❌ File not found: $filePathName");
}

try {
    // Attempt to load the spreadsheet
    $spreadsheet = IOFactory::load($filePathName);
    // Get first sheet (active)
    $sheet = $spreadsheet->getActiveSheet();
    // Convert to array
    $data = $sheet->toArray(null, true, true, true);

    //echo "✅ File loaded successfully. Row count: " . count($data) . "<br><br>";

    // Dump data
    /*foreach ($data as $row) {
        echo implode(' | ', $row) . "<br>";
    }*/
    
        $header = [];
        foreach ($data[1] as $col => $value) {
            $headers[$col] = strtolower(str_replace(' ', '_', trim($value)));
        }

    // Loop through rows ( skip 1st row, which is headers).
    foreach($data as $index => $row) {
        if ($index === 1) continue; // Skip header row

        // Map each cell to it's header
        $rowData = [];
        foreach ($row as $col => $value) {
            $key = $headers[$col] ?? $col;
            $rowData[$key] = $value;
        }

        // Now safely access by your column names
        $vehicleId          = trim($rowData['vehicle_id'] ?? '');
        $operatorId         = trim($rowData['operator_id'] ?? '');
        $operatorName       = trim($rowData['operator_name'] ?? '');
        $numCoaches         = trim($rowData['num_of_coaches'] ?? '');
        $startDateTime      = normalizeDateTime($rowData['start_date_time'] ?? '');
        $spotTime           = normalizeDateTime($rowData['spot_time'] ?? '', true);
        $leaveDateTime      = normalizeDateTime($rowData['leave_date_time'] ?? '');
        $returnDateTime     = normalizeDateTime($rowData['return_date_drop_time'] ?? '');
        $actualDropTime     = normalizeDateTime($rowData['actual_drop_time'] ?? '', true);
        $endDateTime        = normalizeDateTime($rowData['end_date_time'] ?? '');
        $actualEndTime      = normalizeDateTime($rowData['acutal_end_time'] ?? '', true);
        $totalHrs           = trim($rowData['total_job_hrs'] ?? '');
        $driveTime          = trim($rowData['driving_time'] ?? '');
        $origin             = trim($rowData['origin'] ?? '');
        $destination        = trim($rowData['destination'] ?? '');
        $groupName          = trim($rowData['group_name'] ?? '');
        $groupLeader        = trim($rowData['group_leader'] ?? '');
        $groupLeadMobile    = trim($rowData['group_leader_mobile'] ?? '');
        $customerName       = trim($rowData['customer_name'] ?? '');
        $customerPhone      = trim($rowData['customer_phone'] ?? '');
        $contactName        = trim($rowData['contact_name'] ?? '');
        $contactMobile      = trim($rowData['contact_mobile'] ?? '');
        $pickUpdetails      = trim($rowData['pickup_location_details'] ?? '');
        $destDetails        = trim($rowData['destination_location_details'] ?? '');
        $rawSignature       = $rowData['signature_required'] ?? 'no';
        $isSignature        = strtolower(trim($rawSignature)) === 'yes' ? 1 : 0;
        $signature          = trim($rowData['signature'] ?? '');
        $driverNotes        = trim($rowData['driver_notes'] ?? '');
        
        $rows[] = [
            'vehicle_id'        => $vehicleId,
            'operator_id'       => $operatorId,
            'operator_name'     => $operatorName,
            'num_of_coaches'    => $numCoaches,
            'start_date_time'   => $startDateTime,
            'spot_time'         => $spotTime,
            'leave_date_time'   => $leaveDateTime,
            'return_date_drop_time'  => $returnDateTime,
            'actual_drop_time'  => $actualDropTime,
            'end_date_time'     => $endDateTime,
            'actual_end_time'   => $actualEndTime,
            'total_job_hrs'     => $totalHrs,
            'driving_time'      => $driveTime,
            'origin'            => $origin,
            'destination'       => $destination,
            'group_name'        => $groupName,
            'group_leader'      => $groupLeader,
            'group_leader_mobile' => $groupLeadMobile,
            'customer_name'     => $customerName,
            'customer_phone'    => $customerPhone,
            'contact_name'      => $contactName,
            'contact_mobile'    => $contactMobile,
            'pickup_location_details' => $pickUpdetails,
            'destination_location_details' => $destDetails,
            'signature_required' => $isSignature,
            'signature'         => $signature,
            'driver_notes'      => $driverNotes
        ];
    }
    return $rows;

} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    die("❌ Spreadsheet Reader Error: " . $e->getMessage());
} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
    die("❌ PhpSpreadsheet Error: " . $e->getMessage());
} catch (\Exception $e) {
    die("❌ General Error: " . $e->getMessage());
}

/**
 * Normalize Excel datetime/time values
 * - Full datetime → Y-m-d H:i:s
 * - Time-only → H:i:s
 */
function normalizeDateTime($value, $timeOnly = false) {
    if (empty($value)) return null;

    if (is_numeric($value)) {
        $dt = Date::excelToDateTimeObject($value);
        return $timeOnly ? $dt->format('H:i:s') : $dt->format('Y-m-d H:i:s');
    } else {
        $ts = strtotime($value);
        if (!$ts) return null;
        return $timeOnly ? date('H:i:s', $ts) : date('Y-m-d H:i:s', $ts);
    }
}

?>