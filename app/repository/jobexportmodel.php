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
            $spreadsheet = IOFactory::load($this->filePath);
            $sheet = $spreadsheet->getActiveSheet();

            $operatorName = trim($dbValues['operator_name']);
            $vehicleNumber = (string)$dbValues['vehicle_id'];

            // Convert DB start_date_time to DateTime
            $dbStartDT = \DateTime::createFromFormat('Y-m-d H:i:s', $dbValues['start_date_time']);
            if (!$dbStartDT) {
                $this->logger->error("[AssignmentExporter] Invalid DB start_date_time: {$dbValues['start_date_time']}");
                return false;
            }

            $highestRow = $sheet->getHighestDataRow();
            $matchRow = null;

            for ($row = 2; $row <= $highestRow; $row++) {
                $excelOperator = trim((string)$sheet->getCell("C$row")->getValue());
                $excelVehicle = (string)trim($sheet->getCell("A$row")->getValue());
                $excelStartValue = trim((string)$sheet->getCell("E$row")->getValue());

                // Convert Excel date/time
                if (is_numeric($excelStartValue)) {
                    $excelStartDT = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelStartValue);
                } else {
                    // Handle m/d/Y h:ia format without leading zeros
                    $excelStartDT = \DateTime::createFromFormat('n/j/Y g:ia', $excelStartValue);
                }

                if (!$excelStartDT) continue;

                // Compare DB vs Excel only up to minutes
                $excelStr = $excelStartDT->format('Y-m-d H:i');
                $dbStr = $dbStartDT->format('Y-m-d H:i');

                if ($excelOperator === $operatorName && $excelVehicle === $vehicleNumber && $excelStr === $dbStr) {
                    $matchRow = $row;
                    break;
                }
            }

            if (!$matchRow) {
                $this->logger->error("[AssignmentExporter] No matching row found for operator {$operatorName}, vehicle {$vehicleNumber}, start {$dbValues['start_date_time']}");
                $alert::setMsg('error', "Can't find assignment for Operator. Please contact dispatch.");
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

                if (in_array($field, ['actual_drop_time']) && $valueToWrite !== '') {
                    $dt = new \DateTime($valueToWrite);
                    $valueToWrite = $dt->format('h:ia');
                    $sheet->setCellValue("$col$matchRow", $valueToWrite);
                    $sheet->getStyle("$col$matchRow")->getNumberFormat()->setFormatCode('h:mma');
                }

                if ($field === 'actual_end_time' && !empty($valueToWrite)) {
                    $dt = new \DateTime($valueToWrite);
                    $valueToWrite = $dt->format('m-d-Y h:ia');
                }

                if (in_array($field, ['total_job_time', 'driving_time']) && $valueToWrite !== '') {
                    $num = number_format((float)$valueToWrite, 2, '.', '');
                    $sheet->setCellValue("$col$matchRow", (float)$num);
                    $sheet->getStyle("$col$matchRow")->getNumberFormat()->setFormatCode('0.00');
                }

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
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
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