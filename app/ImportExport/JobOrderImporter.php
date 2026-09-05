<?php

declare(strict_types=1);

namespace App\ImportExport;

use Exception;
use App\Repositories\AssignmentRepository;
use App\Repositories\DriverCredentialRepository;
use App\Repositories\UserRepository;
use App\Validation\ImporterAssignmentValidator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;

class JobOrderImporter {
    protected string $excelFile;
    // Initialize Logger
    protected $logger;
    protected ImporterAssignmentValidator $validator;

    public function __construct(string $excelFile, $logger, ImporterAssignmentValidator $validator) {
        $this->excelFile = $excelFile;
        $this->logger = $logger;
        $this->validator = $validator;
    }

    public function run(): bool {        
        if (!file_exists($this->excelFile)) {
            $this->logger->error("File not found: {$this->excelFile}");
            return false;
        }

        try {
            $this->logger->info("Starting JobOrderImporter with file: {$this->excelFile}");
            // Load spreadsheet
            $spreadsheet = IOFactory::load($this->excelFile);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);
            
            // Read headers
            $headers = [];
            foreach ($data[1] as $col => $value) {
                $headers[$col] = strtolower(str_replace(' ', '_', trim($value)));
            }

            $orderRef = 'JOB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $this->logger->info("Generated order reference: {$orderRef}");

            $controlBatch = 'PD-' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $controlSequence = 0;
            $assignmentRepository = new AssignmentRepository(null, $this->logger);
            $credentialRepository = new DriverCredentialRepository();
            $notifiedDrivers = [];
            $insertedCount = 0;
            $duplicateCount = 0;
            $failedCount = 0;

            // Process each row (skip header)
            foreach ($data as $index => $row) {
                if ($index === 1) continue; // Skip header row

                $rowData = [];
                $emptyRow = true;
                foreach ($row as $col => $value) {
                    $key = $headers[$col] ?? $col;
                    $trimmed = is_string($value) ? trim($value) : $value;
                    $rowData[$key] = $trimmed;
                    if ($trimmed !== null && $trimmed !== '') {
                        $emptyRow = false;
                    }
                }

                if ($emptyRow) {
                    //$this->logger->debug("Skipping completely empty row {$index}");
                    $this->logger->info("Skipping completely empty row {$index}");
                    continue;
                }

                $existingControl = trim((string) ($rowData['assignment_control'] ?? ''));
                if ($existingControl !== '') {
                    $this->logger->info("Skipping already imported row {$index}: " . $existingControl);
                    continue;
                }

                $operatorId = trim((string) ($rowData['operator_id'] ?? ''));
                if ($operatorId === '') {
                    $failedCount++;
                    $this->logger->warning("Skipping row {$index} with empty operator_id");
                    continue;
                }

                // Lookup driver_id from credentials table
                $credentials = $credentialRepository->findByOperatorId($operatorId);
                if ($credentials === null) {
                    $failedCount++;
                    $this->logger->error("No driver found for operator_id: " . "{$operatorId} (row {$index})");
                    continue;
                }

                $driverId = (int) $credentials['user_id'];

                $controlSequence++;
                $assignmentControl = $controlBatch . '-' . str_pad((string) $controlSequence, 4, '0', STR_PAD_LEFT);

                $signatureRequired = strtolower(trim((string) ($rowData['signature_required'] ?? 'no'))) === 'yes' ? 1 : 0;
                $signatureStatus = $signatureRequired === 1 ? 'pending' : 'not-required';

                $this->logger->info("[SIGNATURE IMPORT] Row {$index}: " . "raw=" . var_export($rowData['signature_required'] ?? null, true) . ", required={$signatureRequired}" . ", status={$signatureStatus}");

                // Map Excel data to database fields
                $rowData['assignment_control']          = $assignmentControl;
                $rowData['order_ref']                   = $orderRef; // shared across all rows in this file
                $rowData['vehicle_id']                  = trim((string) ($rowData['vehicle_id'] ?? ''));
                $rowData['driver_id']                   = $driverId; // insert only driver_id
                $rowData['operator_id']                 = $operatorId; // for display/log only
                ////////////////////////////////////////////////////////////////////////////////////
                $rowData['num_of_coaches']              = trim((string) ($rowData['num_of_coaches'] ?? ''));
                $rowData['start_date_time']             = $this->normalizeDateTime($rowData['start_date_time'] ?? '');
                $rowData['spot_time']                   = $this->normalizeDateTime($rowData['spot_time'] ?? '', true);
                $rowData['leave_date_time']             = $this->normalizeDateTime($rowData['leave_date_time'] ?? '');
                $rowData['return_date_drop_time']       = $this->normalizeDateTime($rowData['return_date_drop_time'] ?? '');
                $rowData['actual_drop_time']            = $this->normalizeDateTime($rowData['actual_drop_time'] ?? '', true);
                $rowData['end_date_time']               = $this->normalizeDateTime($rowData['end_date_time'] ?? '');
                $rowData['actual_end_time']             = $this->normalizeDateTime($rowData['actual_end_time'] ?? '');
                $totalJobHours                          = $rowData['total_job_hrs'] ?? '';
                $rowData['total_job_time']              = $totalJobHours !== '' ? $totalJobHours : null;
                $drivingTime                            = $rowData['driving_time'] ?? '';
                $rowData['driving_time']                = $drivingTime !== '' ? $drivingTime : null;
                $rowData['origin']                      = trim((string) ($rowData['origin'] ?? ''));
                $rowData['destination']                 = trim((string) ($rowData['destination'] ?? ''));
                $rowData['group_name']                  = trim((string) ($rowData['group_name'] ?? ''));
                $rowData['group_leader']               = trim((string) ($rowData['group_leader'] ?? ''));
                $rowData['group_leader_mobile']         = trim((string) ($rowData['group_leader_mobile'] ?? ''));
                $rowData['customer_name']               = trim((string) ($rowData['customer_name'] ?? ''));
                $rowData['customer_phone']              = trim((string) ($rowData['customer_phone'] ?? ''));
                $rowData['contact_name']                = trim((string) ($rowData['contact_name'] ?? ''));
                $rowData['contact_mobile']              = trim((string) ($rowData['contact_mobile'] ?? ''));
                $rowData['pickup_details']              = trim((string) ($rowData['pickup_location_details'] ?? ''));
                $rowData['destination_details']         = trim((string) ($rowData['destination_location_details'] ?? ''));
                $rowData['signature_required']          = $signatureRequired;
                $rowData['signature_status']            = $signatureStatus;
                /*'driver_notes'                => trim($rowData['driver_notes'] ?? ''),*/

                if (!$this->validator->validate($rowData, $index)) {
                    $failedCount++;
                    continue;
                }
                
                // Insert each row using Assignment class
                $result = $assignmentRepository->insertAssignment($rowData);            

                if ($result === true) {
                    $insertedCount++;
                    $sheet->setCellValue("A{$index}", $assignmentControl);
                    $this->logger->info('[JOB IMPORTER] Inserted assignment ' . $assignmentControl . ' from Excel row ' . $index);
                    $this->logger->info("Inserted vehicle: {$rowData['vehicle_id']} ({$rowData['order_ref']}) at {$rowData['start_date_time']}");
                    // Track drivers to notify
                    $notifiedDrivers[$driverId] = true;
                } elseif ($result === 'duplicate') {
                    $duplicateCount++;
                    $this->logger->warning("Duplicate vehicle: {$rowData['vehicle_id']} at {$rowData['start_date_time']}");
                } else {
                    $failedCount++;
                    $this->logger->error("Failed to insert vehicle: {$rowData['vehicle_id']} at {$rowData['start_date_time']}");
                }
            }

            if ($insertedCount > 0) {
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save($this->excelFile);
                $this->logger->info('[JOB IMPORTER] Saved assignment control numbers to the Excel workbook.');
                foreach ($notifiedDrivers as $driverId => $_) {
                    $this->sendAssignmentEmail($driverId, $orderRef);
                }
            }

            $this->logger->info("[JOBORDER IMPORTER] finished. " . "Inserted: {$insertedCount}, " . "Duplicates: {$duplicateCount}, " . "Failed: {$failedCount}, " . "Reference: {$orderRef}");
            
            if ($failedCount > 0) {
                $this->logger->error("[JOBORDER IMPORTER] completed with {$failedCount} failed insert(s).");
                return false;
            }

        } catch (\Throwable $exception) {
            $this->logger->error("Job import error: " . $exception->getMessage());
            return false;
        }
        $this->logger->info("[JOBORDER IMPORTER] completed successfully with reference {$orderRef}.");
        return true;
    }

    /**
     * Normalize Excel datetime/time values
     * - Full datetime → Y-m-d H:i:s
     * - Time-only → H:i:s
     */
    protected function normalizeDateTime($value, bool $timeOnly = false): ?string {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            $dt = Date::excelToDateTimeObject($value);
            return $timeOnly ? $dt->format('H:i:s') : $dt->format('Y-m-d H:i:s');
        }

        $ts = strtotime($value);
        if (!$ts) return null;
        return $timeOnly ? date('H:i:s', $ts) : date('Y-m-d H:i:s', $ts);
    }

    protected function sendAssignmentEmail(int $driverId, string $orderRef): void {
        try {
            $userRepository = new UserRepository();
            try {
                $driver = $userRepository->findById($driverId);
            } catch (\RuntimeException $exception) {
                $this->logger->warning("No driver found for driver ID: {$driverId}");
                return;
            }

            if (!$driver || empty($driver['email'])) {
                $this->logger->warning("No valid email found for driver ID: {$driverId}");
                return;
            }

            $key = null;
            try {
                if (!empty($_ENV['SECRET_KEY'])) {
                    $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
                }
            } catch (\Throwable $exception) {
                $this->logger->error("Failed to load encryption key: " . $exception->getMessage());
            }

            $safeDecrypt = function ($value, $field) use ($key) {
                if (empty($value)) return '';
                if (!$key) {
                    $this->logger->info("[Decrypt] No key loaded - using plaintext for {$field}");
                    return $value;
                }
                try {
                    $decrypted = Crypto::decrypt($value, $key);
                    $this->logger->info("[Decrypt] Successfully decrypted {$field}");
                    return $decrypted;
                } catch (WrongKeyOrModifiedCiphertextException $e) {
                    $this->logger->warning("[Decrypt] {$field} not encrypted - using plaintext");
                    return $value;
                } catch (\Throwable $exception) {
                    $this->logger->error("[Decrypt] Failed to decrypt {$field}: " . $exception->getMessage());
                    return $value;
                }
            };

            $firstName = $safeDecrypt($driver['first_name'], 'first_name');
            $lastName = $safeDecrypt($driver['last_name'], 'last_name');
            $fullName = trim("{$firstName} {$lastName}");
            $to = $driver['email'];

            $mail = require base_path("core/emailSetup.php");
            //$mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->setFrom("noreply@prodriver.local", "Assignments");
            $mail->addAddress($to, $fullName ?: 'Driver');
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->Subject = "New Job Assignment(s) Ready for Confirmation";
            $mail->Body = "
                <p>Hi {$fullName},</p>
                <p>This is to notify you that new job assignment(s) have been added and are now ready for confirmation.</p>
                <p><strong>Reference:</strong> {$orderRef}</p>
                <p>Please log in to your driver portal to review and confirm your assignment(s).</p>
                <p>Regards,<br>Dispatch Team</p>
            ";
            $mail->AltBody = "Hi {$fullName},\n\nNew job assignments are ready for confirmation.\nReference: {$orderRef}\nPlease log in to your driver portal to review and confirm.\n\n- Dispatch Team";
            $mail->send();
            $this->logger->info("Notification email sent to {$fullName} ({$to}) for order {$orderRef}");
        } catch (\Throwable $exception){
            $this->logger->error("Email send failed for driver {$fullName} (ID {$driverId}): " . $exception->getMessage());
        }
    }
};

?>