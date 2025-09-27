<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

require __DIR__ . '/../../vendor/autoload.php';
require_once 'D:/webapps/prodrvrapp/app/models/assignmentmodel.php';
// require_once base_path('app/models/assignmentmodel.php);

// Master log (same as Assignment class)
$masterLog = 'D:/webapps/logs/job_import_master.log';

function writeLog(string $message) {
    global $masterLog;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($masterLog, "[$timestamp] $message\n", FILE_APPEND);
}

// Absolute Windows path to the Excel file
$filePathName = 'C:/Users/bates/OneDrive/Documents/testworkassignment.xlsx';

// Check if the file exists
if (!file_exists($filePathName)) {
    die("❌ File not found: $filePathName");
}

try {
    // Load spreadsheet
    $spreadsheet = IOFactory::load($filePathName);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true);

    // Read headers
    $headers = [];
    foreach ($data[1] as $col => $value) {
        $headers[$col] = strtolower(str_replace(' ', '_', trim($value)));
    }

    $rows = [];

    // Process each row (skip header)
    foreach ($data as $index => $row) {
        if ($index === 1) continue; // Skip header row

        $rowData = [];
        foreach ($row as $col => $value) {
            $key = $headers[$col] ?? $col;
            $rowData[$key] = $value;
        }

        // Map Excel data to database fields
        $rows[] = [
            'vehicle_id'                  => trim($rowData['vehicle_id'] ?? ''),
            'operator_id'                 => trim($rowData['operator_id'] ?? ''),
            'operator_name'               => trim($rowData['operator_name'] ?? ''),
            'num_of_coaches'              => trim($rowData['num_of_coaches'] ?? ''),
            'start_date_time'             => normalizeDateTime($rowData['start_date_time'] ?? ''),
            'spot_time'                   => normalizeDateTime($rowData['spot_time'] ?? '', true),
            'leave_date_time'             => normalizeDateTime($rowData['leave_date_time'] ?? ''),
            'return_date_drop_time'       => normalizeDateTime($rowData['return_date_drop_time'] ?? ''),
            'actual_drop_time'            => normalizeDateTime($rowData['actual_drop_time'] ?? '', true),
            'end_date_time'               => normalizeDateTime($rowData['end_date_time'] ?? ''),
            'actual_end_time'             => normalizeDateTime($rowData['actual_end_time'] ?? '', true),
            'total_job_time'              => trim($rowData['total_job_hrs'] ?? ''),
            'driving_time'                => trim($rowData['driving_time'] ?? ''),
            'origin'                      => trim($rowData['origin'] ?? ''),
            'destination'                 => trim($rowData['destination'] ?? ''),
            'group_name'                  => trim($rowData['group_name'] ?? ''),
            'group_leader'                => trim($rowData['group_leader'] ?? ''),
            'group_leader_mobile'         => trim($rowData['group_leader_mobile'] ?? ''),
            'customer_name'               => trim($rowData['customer_name'] ?? ''),
            'customer_phone'              => trim($rowData['customer_phone'] ?? ''),
            'contact_name'                => trim($rowData['contact_name'] ?? ''),
            'contact_mobile'              => trim($rowData['contact_mobile'] ?? ''),
            'pickup_details'              => trim($rowData['pickup_location_details'] ?? ''),
            'destination_details'         => trim($rowData['destination_location_details'] ?? ''),
            'signature_required'          => strtolower(trim($rowData['signature_required'] ?? 'no')) === 'yes' ? 1 : 0,
            'signature_path'              => trim($rowData['signature'] ?? ''),
            'driver_notes'                => trim($rowData['driver_notes'] ?? '')
        ];
    }

    // Insert each row using Assignment class
    $assignment = new Assignment();

    foreach ($rows as $rowData) {
        $result = $assignment->insertAssignment($rowData);

        if ($result === true) {
            writeLog("✅ SUCCESS: Inserted vehicle {$rowData['vehicle_id']} at {$rowData['start_date_time']}");
        } elseif ($result === 'duplicate') {
            writeLog("⚠️ DUPLICATE: Skipped vehicle {$rowData['vehicle_id']} at {$rowData['start_date_time']}");
        } else {
            writeLog("❌ FAILURE: Insert failed for vehicle {$rowData['vehicle_id']} at {$rowData['start_date_time']}");
        }
    }

} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    echo "❌ Spreadsheet Reader Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
    echo "❌ PhpSpreadsheet Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (\Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "\n";
    exit(1);
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