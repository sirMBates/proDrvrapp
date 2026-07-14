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

    public function assignmentSubmitted(array $data, array $dbValues): bool {
        try {
            // Load spreadsheet
            $spreadsheet = IOFactory::load($this->filePath);
            $sheet = $spreadsheet->getActiveSheet();

            // Find matching row by operator, vehicle, start datetime
            $highestRow = $sheet->getHighestDataRow();
            $matchRow = null;

            $dbStartDT = \DateTime::createFromFormat('Y-m-d H:i:s', $dbValues['start_date_time']);

            for ($row = 2; $row <= $highestRow; $row++) {
                $operatorName = trim((string)$sheet->getCell("B$row")->getValue());
                $vehicleNumber = trim((string)$sheet->getCell("A$row")->getValue());
                $excelStartDT = \DateTime::createFromFormat('m-d-Y h:ia', trim((string)$sheet->getCell("E$row")->getValue()));

                if ($operatorName === $dbValues['operator_name'] && $vehicleNumber === $dbValues['vehicle_id'] && $excelStartDT && $dbStartDT && $excelStartDT->format('Y-m-d H:i:s') === $dbStartDT->format('Y-m-d H:i:s')) {
                    $matchRow = $row;
                    break;
                }
            }

            if (!$matchRow) {
                $this->logger->error("[AssignmentExporter] No matching row found for operator {$dbValues['operator_name']}, vehicle {$dbValues['vehicle_id']}, start {$dbValues['start_date_time']}");
                return false;
            }

            // Map database fields to Excel columns
            $columns = [
                'actual_drop_time' => 'I',
                'actual_end_time' => 'K',
                'total_job_time' => 'L',
                'driving_time' => 'M',
                'pickup_details' => 'W',
                'destination_details' => 'X',
                'pre_signature_base64' => 'Z',
                'post_signature_base64' => 'AA'
            ];

            foreach ($columns as $field => $col) {
                $currentValue = trim((string)$sheet->getCell("$col$matchRow")->getValue());
                $valueToWrite = $submittedData[$field] ?? $dbValues[$field] ?? '';

                if ($currentValue !== $valueToWrite && $valueToWrite !== '') {
                    if (in_array($field, ['pre_signature_base64','post_signature_base64']) &&
                        empty($submittedData['signature_required'])) {
                        $this->logger->debug("[AssignmentExporter] Skipping $field as signature not required.");
                        continue;
                    }

                    $sheet->setCellValue("$col$matchRow", $valueToWrite);
                    $this->logger->info("[AssignmentExporter] Updated $field in row $matchRow: '$currentValue' → '$valueToWrite'");
                } else {
                    $this->logger->debug("[AssignmentExporter] No change for $field in row $matchRow (current: '$currentValue')");
                }
            }

            // Save spreadsheet
            $writer = IOFactory::createWriter($spreadsheet, 'xlsx');
            $writer->save($this->filePath);
            $this->logger->info("[AssignmentExporter] Excel sheet saved successfully: {$this->filePath}");
            return true;
        } catch (\Throwable $e) {
            $this->logger->error("[AssignmentExporter] Error updating Excel: " . $e->getMessage());
            return false;
        }
    }
};

?>