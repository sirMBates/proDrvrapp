<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use core\Logger;

class AssignmentExporter {
    protected string $filePath;
    protected Logger $logger;

    public function __construct(string $filePath, Logger $logger) {
        $this->filePath = $filePath;
        $this->logger = $logger;
    }

    public function assignmentSubmitted(array $data, array $dbValues): bool {
        try {
            $spreadsheet = IOFactory::load($this->filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $assignmentControl = trim((string) ($dbValues['assignment_control'] ?? ''));

            if ($assignmentControl === '') {
                throw new \RuntimeException('Missing assignment_control for excel export.');
            }

            $highestRow = $sheet->getHighestDataRow();
            $matchRow = null;

            for ($row = 2; $row <= $highestRow; $row++) {
                $excelAssignmentControl = trim((string) $sheet->getCell("A{$row}")->getValue());

                if (hash_equals($assignmentControl, $excelAssignmentControl)) {
                    $matchRow = $row;
                    break;
                }
            }

            if ($matchRow === null) {
                throw new \RuntimeException('No Excel row found for assignment_control ' . $assignmentControl);
            }

            $this->logger->info('[ASSIGNMENT EXPORTER] Matched ' . "{$assignmentControl} to Excel row {$matchRow}.");

            $signatureRequired = (int) ($dbValues['signature_required'] ?? 0) === 1;

            // Map database fields to Excel columns
            $columns = [
                'vehicle_id' => [
                    'column' => 'B',
                    'submitted_key' => 'vehicle_id'
                ],
                'actual_drop_time' => [
                    'column' => 'J',
                    'submitted_key' => 'actual_drop_time'
                ],
                'actual_end_time' => [
                    'column' => 'L',
                    'submitted_key' => 'actual_end_time'
                ],
                'total_job_time' => [
                    'column' => 'M',
                    'submitted_key' => 'total_hrs'
                ],
                'driving_time' => [
                    'column' => 'N',
                    'submitted_key' => 'driving_time'
                ],
                'pickup_details' => [
                    'column' => 'X',
                    'submitted_key' => 'pickup_details'
                ],
                'destination_details' => [
                    'column' => 'Y',
                    'submitted_key' => 'destination_details'
                ],
                'pre_signature_path' => [
                    'column' => 'AA',
                    'submitted_key' => null
                ],
                'post_signature_path' => [
                    'column' => 'AB',
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
                    $sheet->getStyle("$col$matchRow")->getNumberFormat()->setFormatCode('h:mma');
                }

                if ($field === 'actual_end_time' && $valueToWrite !== '') {
                    $dt = new \DateTime($valueToWrite);
                    $valueToWrite = $dt->format('m/d/Y h:ia');
                }

                if (in_array($field, ['total_job_time', 'driving_time'], true) && $valueToWrite !== '') {
                    $valueToWrite = (float) number_format((float) $valueToWrite, 2, '.', '');
                    $sheet->getStyle("$col$matchRow")->getNumberFormat()->setFormatCode('0.00');
                }

                // Skip signatures if not required
                if (in_array($field, ['pre_signature_path', 'post_signature_path'], true) && !$signatureRequired) {
                    $this->logger->debug("[ASSIGNMENT EXPORTER] Skipping {$field}; signatures are not required.");
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
            throw $e;
        }
    }
};

?>