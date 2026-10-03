<?php

declare(strict_types=1);
namespace Core;
use RuntimeException;

class Storage {
    private const SIGNATURE_PREFIX = 'data:image/png;base64,';
    private const MAX_SIGNATURE_SIZE = 1_048_576; // 1 MB
    private const MAX_TIMESHEET_PDF_SIZE = 10_485_760; // 10MB

    private string $signatureRoot;
    private string $timesheetRoot;

    public function __construct(?string $signatureRoot = null, ?string $timesheetRoot = null) {
        $this->signatureRoot = $this->normalizeStorageRoot($signatureRoot ?? base_path('storage/uploads/signatures'));
        $this->timesheetRoot = $this->normalizeStorageRoot($timesheetRoot ?? base_path('storage/uploads/timesheets'));
        $this->ensureDirectoryExists($this->signatureRoot);
    }

    /**
     * Save any submitted pre-trip and post-trip signatures.
     *
     * Empty signature values are ignored. This allows the pre-trip signature
     * to be saved earlier and the post-trip signature to be added later.
     *
     * @return array{
     *     pre_signature_path: ?string,
     *     pre_signature_hash: ?string,
     *     pre_signature_at: ?string,
     *     post_signature_path: ?string,
     *     post_signature_hash: ?string,
     *     post_signature_at: ?string,
     *     signature_status: string
     * }
     */
    public function saveSignatures(array $data): array {
        //$driverId = $this->normalizeIdentifier($data['driver_id'] ?? null, 'driver ID');
        $orderId = $this->normalizeIdentifier($data['order_id'] ?? null, 'order ID');
        $directory = $this->buildAssignmentDirectory($orderId);
        $this->ensureDirectoryExists($directory);
        $result = $this->emptySignatureResult();
        $preSignature = trim( (string) ($data['pre_signature_base64'] ?? '') );

        if ($preSignature !== '') {
            $savedPreSignature = $this->savePngSignature($preSignature, $directory, 'pre-trip.png');
            $result['pre_signature_path'] = $savedPreSignature['relative_path'];
            $result['pre_signature_hash'] = $savedPreSignature['hash'];
            $result['pre_signature_at'] = $savedPreSignature['saved_at'];
        }

        $postSignature = trim((string) ($data['post_signature_base64'] ?? ''));

        if ($postSignature !== '') {
            $savedPostSignature = $this->savePngSignature($postSignature, $directory, 'post-trip.png');
            $result['post_signature_path'] = $savedPostSignature['relative_path'];
            $result['post_signature_hash'] = $savedPostSignature['hash'];
            $result['post_signature_at'] = $savedPostSignature['saved_at'];
        }

        $result['signature_status'] = $this->determineSignatureStatus($directory);

        return $result;
    }

    public function createSignatureBackup(string|int $orderId): array {
        $orderId = $this->normalizeIdentifier($orderId, 'order ID');
        $directory = $this->buildAssignmentDirectory($orderId);

        $files = [
            'pre-trip.png',
            'post-trip.png'
        ];

        $backup = [];

        foreach ($files as $fileName) {
            $absolutePath = "{$directory}/{$fileName}";
            $backupSuffix = bin2hex(random_bytes(8));
            $backupPath = "{$absolutePath}.bak.{$backupSuffix}";

            if (!is_file($absolutePath)) {
                $backup[$fileName] = [
                    'existed' => false,
                    'backup_path' => null
                ];

                continue;
            }

            if (!copy($absolutePath, $backupPath)) {
                throw new RuntimeException("Unable to create signature backup: {$fileName}");
            }

            $backup[$fileName] = [
                'existed' => true,
                'backup_path' => $backupPath
            ];
        }

        return [
            'directory' => $directory,
            'files' => $backup
        ];
    }

    public function rollbackSignatureBackup(array $backup): void {
        $directory = (string) ($backup['directory'] ?? '');
        $files = $backup['files'] ?? [];

        if ($directory === '' || !is_array($files)) {
            return;
        }

        foreach ($files as $fileName => $state) {
            $absolutePath = "{$directory}/{$fileName}";
            $existed = (bool) ($state['existed'] ?? false);
            $backupPath = $state['backup_path'] ?? null;

            if ($existed) {
                if (!is_string($backupPath) || !is_file($backupPath)) {
                    throw new RuntimeException("Signature backup is missing: {$fileName}");
                }

                if (!copy($backupPath, $absolutePath)) {
                    throw new RuntimeException("Unable to restore signature backup: {$fileName}");
                }

                @unlink($backupPath);
                continue;
            }

            /*
            * This signature did not exist before the attempted write,
            * so remove it if the failed operation created it.
            */
            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }
        }
    }

    public function discardSignatureBackup(array $backup): void {
        $files = $backup['files'] ?? [];

        if (!is_array($files)) {
            return;
        }

        foreach ($files as $state) {
            $backupPath = $state['backup_path'] ?? null;

            if (is_string($backupPath) && is_file($backupPath)) {
                @unlink($backupPath);
            }
        }
    }
    /**
     * Verify signature records fetched from the database.
     *
     * When $signatureRequired is false, verification succeeds immediately.
     *
     * @return array{
     *     status: string,
     *     message?: string,
     *     pre_signature_exists?: bool,
     *     post_signature_exists?: bool
     * }
     */
    public function verifySignatures(array $assignment): array {
        $signatureRequired = (int) ($assignment['signature_required'] ?? 0) === 1;
        if (!$signatureRequired) {
            return [
                'status' => 'success',
                'pre_signature_exists' => false,
                'post_signature_exists' => false
            ];
        }

        $prePath = trim((string) ($assignment['pre_signature_path'] ?? ''));
        $postPath = trim((string) ($assignment['post_signature_path'] ?? ''));

        if ($prePath === '' || $postPath === '') {
            throw new RuntimeException('The required pre & post trip signatures have not been saved.');
        }

        $preAbsolutePath = $this->absolutePathFromStoredPath($prePath);
        $postAbsolutePath = $this->absolutePathFromStoredPath($postPath);

        if (!is_file($preAbsolutePath)) {
            throw new RuntimeException('The saved pre-trip signature file is missing.');
        }

        if (!is_file($postAbsolutePath)) {
            throw new RuntimeException('The saved post-trip signature file is missing.');
        }

        if (!$this->verifyStoredHash($preAbsolutePath, $assignment['pre_signature_hash'] ?? null)) {
            throw new RuntimeException('The saved pre-trip signature failed its integrity check.');
        }

        if (!$this->verifyStoredHash($postAbsolutePath, $assignment['post_signature_hash'] ?? null)) {
            throw new RuntimeException('The saved post-trip signature failed its integrity check.');
        }

        return [
            'status' => 'success',
            'pre_signature_exists' => true,
            'post_signature_exists' => true
        ];
    }

    /**
     * Return an absolute filesystem path for an internally stored path.
     */
    public function getAbsolutePath(string $storedPath): string {
        return $this->absolutePathFromStoredPath($storedPath);
    }

    /**
     * Save one authoritative Timesheet PDF atomically.
     *
     * @return array{
     *      pdf_path: string,
     *      pdf_sha256: string,
     *      pdf_generated_at: string,
     *      created: bool
     * }
     */
    public function saveTimesheetPdf(int $driverId, string $periodStart, string $periodEnd, string $pdfBytes): array {
        if ($driverId < 1) {
            throw new RuntimeException('Invalid driver ID for Timesheet PDF storage.');
        }

        $periodStart = $this->normalizeStorageDate($periodStart, 'period start');
        $periodEnd = $this->normalizeStorageDate($periodEnd, 'period end');

        if ($periodStart > $periodEnd) {
            throw new RuntimeException('The Timesheet period start cannot be after its end.');
        }

        if ($pdfBytes === '' || !str_starts_with($pdfBytes, '%PDF-')) {
            throw new RuntimeException('The Timesheet document is not a valid PDF.');
        }

        if (strlen($pdfBytes) > self::MAX_TIMESHEET_PDF_SIZE) {
            throw new RuntimeException('The Timesheet PDF exceeds the maximum allowed size.');
        }

        $directory = $this->buildTimesheetDirectory($driverId, $periodStart, $periodEnd);
        $this->ensureDirectoryExists($directory);

        $absolutePath = "{$directory}/timesheet.pdf";
        $newHash = hash('sha256', $pdfBytes);

        /*
        * An identical retry is safe and returns the existing immutable file.
        * A different PDF for the same driver and period must never overwrite it.
        */
        if (is_file($absolutePath)) {
            $existingHash = hash_file('sha256', $absolutePath);

            if (is_string($existingHash) && hash_equals($existingHash, $newHash)) {
                return [
                    'pdf_path' => $this->relativePathFromRoot($absolutePath, $this->timesheetRoot, 'Timesheet'),
                    'pdf_sha256' => $existingHash,
                    'pdf_generated_at' => date('Y-m-d H:i:s', filemtime($absolutePath) ?: time()),
                    'created' => false
                ];
            }

            throw new RuntimeException('A different Timesheet PDF already exists for this pay period.');
        }

        $temporarySuffix = bin2hex(random_bytes(8));
        $temporaryPath = "{$absolutePath}.tmp.{$temporarySuffix}";
        $bytesWritten = file_put_contents($temporaryPath, $pdfBytes, LOCK_EX);

        if ($bytesWritten === false || $bytesWritten !== strlen($pdfBytes)) {
            @unlink($temporaryPath);
            throw new RuntimeException('Unable to write the Timesheet PDF.');
        }

        if (!@rename($temporaryPath, $absolutePath)) {
            @unlink($temporaryPath);
            throw new RuntimeException('Unable to finalize the Timesheet PDF.');
        }

        return [
            'pdf_path' => $this->relativePathFromRoot($absolutePath, $this->timesheetRoot, 'Timesheet'),
            'pdf_sha256' => $newHash,
            'pdf_generated_at' => date('Y-m-d H:i:s'),
            'created' => true
        ];
    }

    /**
     * Read an internally stored Timesheet PDF after verifying its integrity.
     */
    public function readTimesheetPdf(string $storedPath, string $expectedHash): string {
        $absolutePath = $this->absoluteTimesheetPathFromStoredPath($storedPath);
        if (!is_file($absolutePath)) {
            throw new RuntimeException('The stored Timesheet PDF is missing.');
        }

        $expectedHash = strtolower(trim($expectedHash));
        if (!preg_match('/^[a-f0-9]{64}$/', $expectedHash)) {
            throw new RuntimeException('The stored Timesheet PDF hash is invalid.');
        }

        $actualHash = hash_file('sha256', $absolutePath);
        if (!is_string($actualHash) || !hash_equals($expectedHash, $actualHash)) {
            throw new RuntimeException('The stored Timesheet PDF failed its integrity check.');
        }

        $fileSize = filesize($absolutePath);
        if ($fileSize === false || $fileSize > self::MAX_TIMESHEET_PDF_SIZE) {
            throw new RuntimeException('The stored Timesheet PDF size is invalid.');
        }

        $pdfBytes = file_get_contents($absolutePath);
        if ($pdfBytes === false || !str_starts_with($pdfBytes, '%PDF-')) {
            throw new RuntimeException('The stored Timesheet document is not a valid PDF.');
        }

        return $pdfBytes;
    }

    /**
     * Remove a newly created Timesheet PDF when submission persistence fails.
     */
    public function deleteTimesheetPdf(string $storedPath, string $expectedHash): void {
        $absolutePath = $this->absoluteTimesheetPathFromStoredPath($storedPath);
        if (!is_file($absolutePath)) {
            return;
        }

        $expectedHash = strtolower(trim($expectedHash));
        $actualHash = hash_file('sha256', $absolutePath);

        if (!preg_match('/^[a-f0-9]{64}$/', $expectedHash) || !is_string($actualHash) || !hash_equals($expectedHash, $actualHash)) {
            throw new RuntimeException('The Timesheet PDF could not be removed because its integrity check failed.');
        }

        if (!unlink($absolutePath)) {
            throw new RuntimeException('Unable to remove the Timesheet PDF.');
        }
    }

    /**
     * Decode and save one PNG signature atomically.
     *
     * @return array{
     *     relative_path: string,
     *     hash: string,
     *     saved_at: string
     * }
     */
    private function savePngSignature(string $dataUri, string $directory, string $fileName): array {
        $decodedImage = $this->decodePngDataUri($dataUri);
        $absolutePath = "{$directory}/{$fileName}";
        $temporaryPath = "{$absolutePath}.tmp";
        $newHash = hash('sha256', $decodedImage);

        /*
         * Do not rewrite an identical signature that is already stored.
         */
        if (is_file($absolutePath)) {
            $existingHash = hash_file('sha256', $absolutePath);

            if (is_string($existingHash) && hash_equals($existingHash, $newHash)) {
                return [
                    'relative_path' => $this->relativePathFromAbsolute($absolutePath),
                    'hash' => $existingHash,
                    'saved_at' => date('Y-m-d H:i:s', filemtime($absolutePath) ?: time())
                ];
            }
        }

        $bytesWritten = file_put_contents($temporaryPath, $decodedImage, LOCK_EX);
        if ($bytesWritten === false || $bytesWritten !== strlen($decodedImage)) {
            @unlink($temporaryPath);
            throw new RuntimeException("Unable to write signature file: {$fileName}");
        }

        if (!@rename($temporaryPath, $absolutePath)) {
            @unlink($temporaryPath);
            throw new RuntimeException("Unable to finalize signature file: {$fileName}");
        }

        return [
            'relative_path' => $this->relativePathFromAbsolute($absolutePath),
            'hash' => $newHash,
            'saved_at' => date('Y-m-d H:i:s')
        ];
    }

    private function decodePngDataUri(string $dataUri): string {
        if (!str_starts_with($dataUri, self::SIGNATURE_PREFIX)) {
            throw new RuntimeException('Signature must be a PNG Base64 data URI.');
        }

        $encodedData = substr($dataUri, strlen(self::SIGNATURE_PREFIX));
        $decodedData = base64_decode($encodedData, true);

        if ($decodedData === false) {
            throw new RuntimeException('Signature contains invalid Base64 data.');
        }

        if (strlen($decodedData) > self::MAX_SIGNATURE_SIZE) {
            throw new RuntimeException('Signature exceeds the maximum allowed size.');
        }

        $pngHeader = "\x89PNG\r\n\x1A\n";
        if (!str_starts_with($decodedData, $pngHeader)) {
            throw new RuntimeException('Signature data is not a valid PNG image.');
        }

        return $decodedData;
    }

    private function verifyStoredHash(string $absolutePath, mixed $storedHash): bool {
        /*
         * Hash verification remains optional during migration.
         * Once every existing signature has a hash, this may be made required.
         */
        $storedHash = trim((string) $storedHash);

        if ($storedHash === '') {
            return true;
        }

        $actualHash = hash_file('sha256', $absolutePath);
        return is_string($actualHash) && hash_equals($storedHash, $actualHash);
    }

    private function buildAssignmentDirectory(string $orderId): string {
        return "{$this->signatureRoot}/order-{$orderId}";
    }

    private function buildTimesheetDirectory(int $driverId, string $periodStart, string $periodEnd): string {
        return "{$this->timesheetRoot}/driver-{$driverId}/{$periodStart}_to_{$periodEnd}";
    }

    private function normalizeStorageDate(string $value, string $label): string {
        $value = trim($value);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();

        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $value) {
            throw new RuntimeException("Invalid Timesheet {$label}.");
        }

        return $value;
    }

    private function normalizeIdentifier(mixed $value, string $label): string {
        $identifier = trim((string) $value);

        if ($identifier === '') {
            throw new RuntimeException("Missing {$label}.");
        }

        /*
         * Allows numeric IDs and safe identifier characters without permitting
         * directory traversal.
         */
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $identifier)) {
            throw new RuntimeException("Invalid {$label}.");
        }

        return $identifier;
    }

    private function normalizeStorageRoot(string $root): string {
        $root = rtrim(str_replace('\\', '/', trim($root)), '/');

        if ($root === '') {
            throw new RuntimeException('The storage root cannot be empty.');
        }

        return $root;
    }

    private function ensureDirectoryExists(string $directory): void {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException("Unable to create storage directory: {$directory}");
        }
    }

    private function relativePathFromRoot(string $absolutePath, string $root, string $label): string {
        $normalizedPath = str_replace('\\', '/', $absolutePath);
        $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/') . '/';

        if (!str_starts_with($normalizedPath, $normalizedRoot)) {
            throw new RuntimeException("{$label} path is outside its configured storage directory.");
        }

        return substr($normalizedPath, strlen($normalizedRoot));
    }

    private function relativePathFromAbsolute(string $absolutePath): string {
        $normalizedPath = str_replace('\\', '/', $absolutePath);
        $normalizedRoot = $this->signatureRoot . '/';

        if (!str_starts_with($normalizedPath, $normalizedRoot)) {
            throw new RuntimeException('Signature path is outside the configured storage directory.');
        }

        return substr($normalizedPath, strlen($normalizedRoot));
    }

    private function absoluteTimesheetPathFromStoredPath(string $storedPath): string {
        $storedPath = ltrim(str_replace('\\', '/', trim($storedPath)), '/');
        if ($storedPath === '' || str_contains($storedPath, "\0") || str_contains($storedPath, '../')) {
            throw new RuntimeException('Invalid stored Timesheet PDF path.');
        }

        $absolutePath = "{$this->timesheetRoot}/{$storedPath}";
        $normalizedPath = str_replace('\\', '/', $absolutePath);
        $normalizedRoot = "{$this->timesheetRoot}/";

        if (!str_starts_with($normalizedPath, $normalizedRoot)) {
            throw new RuntimeException('Stored Timesheet PDF path is outside the storage directory.');
        }

        return $normalizedPath;
    }

    private function absolutePathFromStoredPath(string $storedPath): string {
        $storedPath = ltrim(str_replace('\\', '/', trim($storedPath)), '/');

        if ($storedPath === '' || str_contains($storedPath, '../') || str_contains($storedPath, '..\\')) {
            throw new RuntimeException('Invalid stored signature path.');
        }

        $absolutePath = "{$this->signatureRoot}/{$storedPath}";
        $normalizedAbsolutePath = str_replace('\\', '/', $absolutePath);

        if (!str_starts_with($normalizedAbsolutePath, $this->signatureRoot . '/')) {
            throw new RuntimeException('Stored signature path is outside the storage directory.');
        }

        return $normalizedAbsolutePath;
    }

    private function emptySignatureResult(): array {
        return [
            'pre_signature_path' => null,
            'pre_signature_hash' => null,
            'pre_signature_at' => null,
            'post_signature_path' => null,
            'post_signature_hash' => null,
            'post_signature_at' => null,
            'signature_status' => 'pending'
        ];
    }

    private function determineSignatureStatus(string $directory): string {
        $preSignatureExists = is_file("{$directory}/pre-trip.png");
        $postSignatureExists = is_file("{$directory}/post-trip.png");

        if ($preSignatureExists && $postSignatureExists) {
            return 'complete';
        }

        if ($preSignatureExists) {
            return 'pre-trip-complete';
        }

        return 'pending';
    }

    public function reusePreSignatureAsPost(string $preSignaturePath): array {
        $preSignaturePath = trim($preSignaturePath);
        if ($preSignaturePath === '') {
            throw new \RuntimeException('Pre-inspection signature path is required.');
        }

        $preFile = $this->signatureRoot . DIRECTORY_SEPARATOR . $preSignaturePath;
        if (!is_file($preFile) || !is_readable($preFile)) {
            throw new \RuntimeException('Pre-inspection signature file could not be found.');
        }

        $directory = dirname($preFile);
        $postFile = $directory . DIRECTORY_SEPARATOR . 'post-trip.png';

        if (!copy($preFile, $postFile)) {
            throw new \RuntimeException('Post-inspection signature could not be created.');
        }

        $postHash = hash_file('sha256', $postFile);
        if ($postHash === false) {
            @unlink($postFile);

            throw new \RuntimeException('Post-inspection signature hash could not be created.');
        }

        return [
            'post_signature_path' => dirname($preSignaturePath) . '/post-trip.png',
            'post_signature_hash' => $postHash,
            'post_signature_at' => date('Y-m-d H:i:s'),
            'signature_status' => 'complete'
        ];
    }
}

?>