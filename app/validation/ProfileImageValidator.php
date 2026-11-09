<?php

declare(strict_types=1);

namespace App\Validation;

class ProfileImageValidator {

    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif'
    ];

    private const ALLOWED_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'gif'
    ];

    public function validate(array $file): array {
        $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($uploadError !== UPLOAD_ERR_OK) {
            return $this->handleUploadError((int) $uploadError);
        }

        if (!isset($file['tmp_name'], $file['name'], $file['size']) || !is_string($file['tmp_name']) || !is_string($file['name']) || !is_numeric($file['size']) || !is_uploaded_file($file['tmp_name'])) {
            return $this->error(400, 'Invalid uploaded file.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true) || !in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return $this->error(415, 'Invalid image type. Only jpeg, png, or gif are allowed.');
        }

        if ((int) $file['size'] > self::MAX_FILE_SIZE) {
            return $this->error(413, 'Image size exceeds the limit of 5MB.');
        }

        return [
            'valid' => true,
            'http_code' => 200,
            'message' => ''
        ];
    }

    private function handleUploadError(int $errorCode): array {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the server's upload_max_filesize setting.",
            UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive in the form.",
            UPLOAD_ERR_PARTIAL    => "The file was only partially uploaded.",
            UPLOAD_ERR_NO_FILE    => "No file was uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder on the server.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION  => "A PHP extension stopped the upload."
        ];

        return $this->error(400, $messages[$errorCode] ?? 'Unknown upload error.');
    }

    private function error(int $httpCode, string $message): array {
        return [
            'valid' => false,
            'http_code' => $httpCode,
            'message' => $message
        ];
    }
}

?>
