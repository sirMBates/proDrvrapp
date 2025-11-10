<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use core\Database;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;
require_once __DIR__ . "/../../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.local.env');
$dotenv->load();

// require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../../app/models/assignmentmodel.php";
// require_once 'D:/webapps/prodrvrapp/app/models/assignmentmodel.php';

class JobOrderImporter {
    protected string $excelFile;
    protected $logger;
    // Initialize Logger
    public function __construct(string $excelFile, $logger) {
        $this->excelFile = $excelFile;
        $this->logger = $logger;
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
            //$this->logger->debug("Excel data read: " . json_encode($data));

            //$this->logger->debug("Raw sheet row count (including header): " . count($data));
            //$this->logger->debug("First 3 rows: " . json_encode(array_slice($data, 0, 3)));
            // Read headers
            $headers = [];
            foreach ($data[1] as $col => $value) {
                $headers[$col] = strtolower(str_replace(' ', '_', trim($value)));
            }
            //$this->logger->debug("Normalized headers: " . json_encode($headers));

            $orderRef = 'JOB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $this->logger->info("Generated order reference: {$orderRef}");

            $rows = [];
            // Process each row (skip header)
            foreach ($data as $index => $row) {
                if ($index === 1) continue; // Skip header row

                $rowData = [];
                $emptyRow = true;
                foreach ($row as $col => $value) {
                    $key = $headers[$col] ?? $col;
                    $trimmed = is_string($value) ? trim($value) : $value;
                    $rowData[$key] = $trimmed;
                    if (!empty($trimmed)) $emptyRow = false;
                }

                if ($emptyRow) {
                    //$this->logger->debug("Skipping completely empty row {$index}");
                    $this->logger->info("Skipping completely empty row {$index}");
                    continue;
                }

                if (empty($rowData['operator_id'])) {
                    $this->logger->warning("Skipping row {$index} with empty operator_id");
                    continue;
                }

                //$this->logger->debug("Mapped row data: " . json_encode($rowData));
                //$this->logger->debug("Parsed row {$index}: " . json_encode($rowData));
                /*$this->logger->debug("Parsed row data: " . json_encode($rowData));
                $this->logger->info("Rows found in Excel: " . (count($data) - 1));*/

                // Lookup driver_id from drivers table
                $db = new Database();
                $pdo = $db->connect();
                $sql = "SELECT driver_id, first_name, last_name
                        FROM drivers
                        WHERE operator_id = :operator_id
                        LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':operator_id', $rowData['operator_id'], PDO::PARAM_STR);
                $stmt->execute();
                $driver = $stmt->fetch();
        
                if (!$driver) {
                    $this->logger->error("No driver found for operator_id: {$rowData['operator_id']} (row {$index})");
                    continue; // skip this row
                }

                // Map Excel data to database fields
                $rows[] = [
                    'order_ref'                   => $orderRef, // shared across all rows in this file
                    'vehicle_id'                  => trim($rowData['vehicle_id'] ?? ''),
                    'driver_id'                   => $driver['driver_id'], // insert only driver_id
                    'operator_id'                 => trim($rowData['operator_id'] ?? ''), // for display/log only
                    'operator_name'               => $driver['first_name'] . ' ' . $driver['last_name'], // display/log only
                    'num_of_coaches'              => trim($rowData['num_of_coaches'] ?? null),
                    'start_date_time'             => $this->normalizeDateTime($rowData['start_date_time'] ?? ''),
                    'spot_time'                   => $this->normalizeDateTime($rowData['spot_time'] ?? '', true),
                    'leave_date_time'             => $this->normalizeDateTime($rowData['leave_date_time'] ?? ''),
                    'return_date_drop_time'       => $this->normalizeDateTime($rowData['return_date_drop_time'] ?? ''),
                    'actual_drop_time'            => $this->normalizeDateTime($rowData['actual_drop_time'] ?? '', true),
                    'end_date_time'               => $this->normalizeDateTime($rowData['end_date_time'] ?? ''),
                    'actual_end_time'             => $this->normalizeDateTime($rowData['actual_end_time'] ?? '', true),
                    'total_job_time'              => $rowData['total_job_hrs'] !== '' ? $rowData['total_job_hrs'] : null,
                    'driving_time'                => $rowData['driving_time'] !== '' ? $rowData['driving_time'] : null,
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
                    'driver_notes'                => trim($rowData['driver_notes'] ?? ''),
                    'signature_required'          => strtolower(trim($rowData['signature_required'] ?? 'no')) === 'yes' ? 1 : 0,
                    'pre_signature_path'          => trim($rowData['pre_signature'] ?? ''),
                    'post_signature_path'         => trim($rowData['post_signature'] ?? '')
                ];
            }
            $this->logger->info("Total valid rows prepared for insert: " . count($rows));
            // Insert each row using Assignment class
            $assignment = new Assignment($this->logger);

            $notifiedDrivers = [];

            foreach ($rows as $rowData) {
                $result = $assignment->insertAssignment($rowData);

                if ($result === true) {
                    $this->logger->info("Inserted vehicle: {$rowData['vehicle_id']} ({$rowData['order_ref']}) at {$rowData['start_date_time']}");
                    // Track drivers to notify
                    $notifiedDrivers[$rowData['driver_id']] = $rowData['operator_name'];
                } elseif ($result === 'duplicate') {
                    $this->logger->warning("Duplicate vehicle: {$rowData['vehicle_id']} at {$rowData['start_date_time']}");
                } else {
                    $this->logger->log("Failed to insert vehicle: {$rowData['vehicle_id']} at {$rowData['start_date_time']}");
                }
            }

            foreach ($notifiedDrivers as $driverId => $driverName) {
                $this->sendAssignmentEmail($driverId, $driverName, $orderRef);
            }

            $this->logger->info("JobOrderImporter completed successfully with reference {$orderRef}.");
            return true;

        } catch (\Exception $e) {
            $this->logger->error("Job import error: " . $e->getMessage());
            return false;
        }
        $this->logger->info("JobOrderImporter completed successfully.");
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

    protected function sendAssignmentEmail($driverId, $driverName, $orderRef) {
        try {
            $db = new Database();
            $pdo = $db->connect();
            $sql = "SELECT email, first_name, last_name 
                    FROM drivers
                    WHERE driver_id = :driver_id
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':driver_id', $driverId);
            $stmt->execute();
            $driver = $stmt->fetch();

            if (!$driver || empty($driver['email'])) {
                $this->logger->warning("No valid email found for driver ID: {$driverId}");
                return;
            }

            $key = null;
            try {
                if (!empty($_ENV['SECRET_KEY'])) {
                    $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
                }
            } catch (Exception $e) {
                $this->logger->error("Failed to load encryption key: " . $e->getMessage());
            }

            $safeDecrypt = function ($value, $field) use ($key) {
                if (empty($value)) return '';
                if (!$key) {
                    $this->logger->info("[Decrypt] No key loaded - using plaintext for {$field}");
                    return $value;
                }
                try {
                    $decrypted = Crypto::decrypt($value, $key);
                    $this->logger->info("[Decrypt] Succesfully decrypted {$field}");
                    return $decrypted;
                } catch (WrongKeyOrModifiedCiphertextException $e) {
                    $this->logger->warning("[Decrypt] {$field} not encrypted - using plaintext");
                    return $value;
                } catch (Exception $e) {
                    $this->logger->error("[Decrypt] Failed to decrypt {$field}: " . $e->getMessage());
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
            $mail->Charset = 'UTF-8';
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
        } catch (Exception $e){
            $this->logger->error("Email send failed for driver {$driverName} (ID {$driverId}): " . $e->getMessage());
        }
    }
}

?>