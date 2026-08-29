<?php

declare(strict_types=1);

use App\Services\ProfileImageService;

class SetDrvrPictureContr {
    private array $file;
    private ProfileImageService $profileImageService;

    public function __construct(array $file) {
        $this->file = $file;
        $this->profileImageService = new ProfileImageService();
    }

    public function setProfilePicture(): array {
        if ($this->file['error'] !== UPLOAD_ERR_OK) {
            return $this->handleUploadError($this->file['error']);
        }

        if (!$this->checkPicType()) {
            http_response_code(415);
            return [
                'status' => 'error',
                'message' => 'Invalid image type. Only jpeg, png, or gif are allowed.'
            ];
        }

        if ($this->checkPicSize()) {
            http_response_code(413);
            return [
                'status' => 'error',
                'message' => 'Image size exceeds the limit of 5MB.'
            ];
        }

        $driverId = (int) ($_SESSION['user_id'] ?? 0);
        if ($driverId < 1) {
            http_response_code(401);
            return [
                'status' => 'error',
                'message' => 'Driver session is unavailable.'
            ];
        }

        return $this->profileImageService->uploadImage($driverId, $this->file);
    }

    private function checkPicType(): bool {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->file['tmp_name']);
        finfo_close($finfo);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        return in_array($mimeType, $allowedTypes, true) && in_array($extension, $allowedExtensions, true);
    }

    private function checkPicSize(): bool {
        $megabytes5 = 5 * 1024 * 1024; //5MB
        return $this->file['size'] > $megabytes5;
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

        http_response_code(400);
        return [
            'status' => 'error',
            'message' => $message[$errorCode] ?? 'Unknown upload error.'
        ];
    }
}

?>