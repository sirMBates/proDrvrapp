<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use core\Logger;
require_once __DIR__ . "/../../vendor/autoload.php";

class AssignmentExporter {
    protected string $filePath;
    protected Logger $logger;

    public function __construct(string $filePath, Logger $logger) {
        $this->filePath = $filePath;
        $this->logger = $logger;
    }

    public function assignmentSubmitted(array $data): void {
        try {
            // Load spreadsheet
            $spreadsheet = IOFactory::load($this->filePath);
            $sheet = $spreadsheet->getActiveSheet();

            // Find matching row by operator, vehicle, start datetime
            $highestRow = $sheet->getHighestDataRow();
            $matchRow = null;

            for ($row = 2; $row <= $highestRow; $row++) {
                $operatorName = trim((string)$sheet->getCell("C$row")->getValue());
                $vehicleNumber = trim((string)$sheet->getCell("B$row")->getValue());
                $startDateTime = trim((string)$sheet->getCell("D$row")->getValue());

                if ($operatorName === $data['operator_name'] &&
                    $vehicleNumber === $data['vehicle_number'] &&
                    $startDateTime === $data['start_date_time']) {
                    $matchRow = $row;
                    break;
                }
            }

            if (!$matchRow) {
                $this->logger->error("[AssignmentExporter] No matching row found for operator {$data['operator_name']}, vehicle {$data['vehicle_number']}, start {$data['start_date_time']}");
                return;
            }

            // Map database fields to Excel columns
            $columns = [
                'actual_drop_time' => 'E',
                'actual_end_time' => 'F',
                'total_hrs' => 'G',
                'driving_time' => 'H',
                'pickup_details' => 'I',
                'destination_details' => 'J',
                'pre_signature_base64' => 'K',
                'post_signature_base64' => 'L'
            ];

            foreach ($columns as $field => $col) {
                if (!isset($data[$field])) continue;

                $currentValue = trim((string)$sheet->getCell("$col$matchRow")->getValue());
                $newValue = trim((string)$data[$field]);

                if ($currentValue !== $newValue && $newValue !== '') {
                    $sheet->setCellValue("$col$matchRow", $newValue);
                    $this->logger->info("[AssignmentExporter] Updated $field in row $matchRow: '$currentValue' → '$newValue'");
                } else {
                    $this->logger->debug("[AssignmentExporter] No change for $field in row $matchRow (current: '$currentValue')");
                }
            }

            // Save spreadsheet
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($this->filePath);

            $this->logger->info("[AssignmentExporter] Excel sheet saved successfully: {$this->filePath}");
        } catch (\Throwable $e) {
            $this->logger->error("[AssignmentExporter] Error updating Excel: " . $e->getMessage());
        }
    }
};

?>