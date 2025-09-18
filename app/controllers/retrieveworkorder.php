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

    // Loop through rows ( skip 1st row, which is headers).
    foreach($data as $index => $row) {
        if ($index === 1) continue; // Skip header row
        
    }
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    die("❌ Spreadsheet Reader Error: " . $e->getMessage());
} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
    die("❌ PhpSpreadsheet Error: " . $e->getMessage());
} catch (\Exception $e) {
    die("❌ General Error: " . $e->getMessage());
}

?>