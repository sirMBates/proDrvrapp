<?php

declare(strict_types=1);
namespace Core;
use RuntimeException;

class Storage {
    private const SIGNATURE_PREFIX = 'data:image/png;base64,';
    private const MAX_SIGNATURE_SIZE = 1_048_576; // 1 MB

    private string $signatureRoot;

    public function __construct(string $signatureRoot = 'D:/prodrvr/public/signatures/') {
        $this->signatureRoot = rtrim(str_replace('\\', '/', $signatureRoot), '/');
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
        $alert = new flash();
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

        $bytesWritten = file_put_contents(
            $temporaryPath,
            $decodedImage,
            LOCK_EX
        );

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

        return is_string($actualHash)
            && hash_equals($storedHash, $actualHash);
    }

    private function buildAssignmentDirectory(string $orderId): string {
        return sprintf(
            '%s/order-%s',
            $this->signatureRoot,
            $orderId
        );
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

    private function ensureDirectoryExists(string $directory): void {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException("Unable to create storage directory: {$directory}");
        }
    }

    private function relativePathFromAbsolute(string $absolutePath): string {
        $normalizedPath = str_replace('\\', '/', $absolutePath);
        $normalizedRoot = $this->signatureRoot . '/';

        if (!str_starts_with($normalizedPath, $normalizedRoot)) {
            throw new RuntimeException('Signature path is outside the configured storage directory.');
        }

        return substr($normalizedPath, strlen($normalizedRoot));
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
};

?>