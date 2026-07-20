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
                $this->logger->error("[ASSIGNMENT EXPORTER] Invalid DB start_date_time: {$dbValues['start_date_time']}");
                $alert::setMsg('error', 'The assignment start time could not be processed. Please contact dispatch.');
                header("Location: /assignments?error=invalid+start+time");
                exit();
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
                $this->logger->error("[ASSIGNMENT EXPORTER] No matching row found for operator {$operatorName}, vehicle {$vehicleNumber}, start {$dbValues['start_date_time']}");
                $alert::setMsg('error', "Assignment not found for operator. Please contact dispatch.");
                header("Location: /assignments?error=missing_assignment");
                exit();
            }

            $signatureRequired = (int) ($dbValues['signature_required'] ?? 0) === 1;

            // Map database fields to Excel columns
            $columns = [
                'actual_drop_time' => [
                    'column' => 'I',
                    'submitted_key' => 'actual_drop_time'
                ],
                'actual_end_time' => [
                    'column' => 'K',
                    'submitted_key' => 'actual_end_time'
                ],
                'total_job_time' => [
                    'column' => 'L',
                    'submitted_key' => 'total_hrs'
                ],
                'driving_time' => [
                    'column' => 'M',
                    'submitted_key' => 'driving_time'
                ],
                'pickup_details' => [
                    'column' => 'W',
                    'submitted_key' => 'pickup_details'
                ],
                'destination_details' => [
                    'column' => 'X',
                    'submitted_key' => 'destination_details'
                ],
                'pre_signature_path' => [
                    'column' => 'Z',
                    'submitted_key' => null
                ],
                'post_signature_path' => [
                    'column' => 'AA',
                    'submitted_key' => null
                ]
            ];

            foreach ($columns as $field => $config) {
                $col = $config['column'];
                $submittedKey = $config['submitted_key'];

                $hasSubmittedValue = $submittedKey !== null && array_key_exists($submittedKey, $data) && $data[$submittedKey] !== null && trim((string) $data[$submittedKey]) !== '';
                $valueToWrite = $hasSubmittedValue ? $data[$submittedKey] : ($dbValues[$field] ?? '');

                if ($field === 'actual_drop_time' && $valueToWrite !== '') {
                    $dt = new \DateTime($valueToWrite);
                    $valueToWrite = $dt->format('h:ia');
                    //$sheet->setCellValue("$col$matchRow", $valueToWrite);
                    $sheet->getStyle("$col$matchRow")->getNumberFormat()->setFormatCode('h:mma');
                }

                if ($field === 'actual_end_time' && $valueToWrite !== '') {
                    $dt = new \DateTime($valueToWrite);
                    $valueToWrite = $dt->format('m-d-Y h:ia');
                }

                if (in_array($field, ['total_job_time', 'driving_time'], true) && $valueToWrite !== '') {
                    $valueToWrite = (float) number_format((float) $valueToWrite, 2, '.', '');
                    //$num = number_format((float)$valueToWrite, 2, '.', '');
                    //$sheet->setCellValue("$col$matchRow", (float)$num);
                    $sheet->getStyle("$col$matchRow")->getNumberFormat()->setFormatCode('0.00');
                }

                // Skip signatures if not required
                if (in_array($field, ['pre_signature_path', 'post_signature_path'], true) && !$signatureRequired) {
                    $this->logger->debug("[ASSIGNMENT EXPORTER] Skipping {$field} signatures are not required.");
                    continue;
                }

                $currentValue = trim((string)$sheet->getCell("$col$matchRow")->getValue());
                if ((string) $currentValue !== (string) $valueToWrite && $valueToWrite !== '') {
                    $sheet->setCellValue("$col$matchRow", $valueToWrite);
                    $this->logger->info("[ASSIGNMENT EXPORTER] Updated {$field} " . "in row {$matchRow}: " . "'{$currentValue}' → '{$valueToWrite}'");
                } else {
                    $this->logger->debug("[ASSIGNMENT EXPORTER] No change for {$field} " . "in row {$matchRow} " . "(current: '{$currentValue}')");
                }
            }

            // Save spreadsheet
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($this->filePath);
            $this->logger->info("[ASSIGNMENT EXPORTER] Excel sheet saved successfully: {$this->filePath}");
            return true;

        } catch (\Throwable $e) {
            $this->logger->error("[ASSIGNMENT EXPORTER] Error updating Excel: " . $e->getMessage());
            $alert::setMsg('error', 'Assignment not submitted. Please try again!');
            header("Location: /assignments?error=submission+failed");
            exit();
        }
    }
};

?>