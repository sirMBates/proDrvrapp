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
        $alert = new core\Flash();
        try {
            // Load spreadsheet
            $spreadsheet = IOFactory::load($this->filePath);
            $sheet = $spreadsheet->getActiveSheet();

            $dbStartDT = new \DateTime($dbValues['start_date_time']);
            $operatorName = $dbValues['operator_name'];
            $vehicleNumber = $dbValues['vehicle_id'];

            // Find matching row by operator, vehicle, start datetime
            $highestRow = $sheet->getHighestDataRow();
            $matchRow = null;

            for ($row = 2; $row <= $highestRow; $row++) {
                $excelOperator = trim((string)$sheet->getCell("B$row")->getValue());
                $excelVehicle = trim((string)$sheet->getCell("A$row")->getValue());
                $excelStartValue = $sheet->getCell("E$row")->getValue();

                // Handle Excel date format
                if (is_numeric($excelStartValue)) {
                    $excelStartDT = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelStartValue);
                } else {
                    $excelStartDT = \DateTime::createFromFormat('m-d-Y h:ia', $excelStartValue);
                }

                if ($excelStartDT && $excelStartDT->format('Y-m-d H:i:s') === $dbStartDT->format('Y-m-d H:i:s')
                && $excelOperator === $operatorName && $excelVehicle === $vehicleNumber) {
                    $matchRow = $row;
                    break;
                }
            }

            if (!$matchRow) {
                $this->logger->error("[AssignmentExporter] No matching row found for operator {$operatorName}, vehicle {$vehicleNumber}, start {$dbValues['start_date_time']}");
                $alert::setMsg('error', 'Can\'t find assignment for Operator. Please contact dispatch.');
                header("Location: /assignments?error=missing_assignment");
                exit();
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
                $valueToWrite = $data[$field] ?? $dbValues[$field] ?? '';

                // Skip signatures if not required
                if (in_array($field, ['pre_signature_base64','post_signature_base64']) && empty($data['signature_required'])) {
                    $this->logger->debug("[AssignmentExporter] Skipping $field as signature not required.");
                    continue;
                }

                $currentValue = trim((string)$sheet->getCell("$col$matchRow")->getValue());
                if ($currentValue !== $valueToWrite && $valueToWrite !== '') {
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
            $alert::setMsg('error', 'Assignment was not submitted. Please try again!');
            header("Location: /assignments?error=submission+failed");
            exit();
        }
    }
};

?>