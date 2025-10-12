<?php

class SetDrvrPictureContr extends ProfileImageUpload {
    private $file;

    public function __construct($file) {
        $this->file = $file;
    }

    public function setProfilePicture() {
        if ($this->file['error'] !== UPLOAD_ERR_OK) {
            $this->handleUploadError($this->file['error']);
            return; // Stop execution
        }

        if (!$this->checkPicType()) {
            http_response_code(415);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid image type. Only jpeg, png, or gif are allowed.'
            ]);
            exit();
        }

        if ($this->checkPicSize()) {
            http_response_code(415);
            echo json_encode([
                'status' => 'error',
                'message' => 'Image size exceeds the limit of 5MB.'
            ]);
            exit();
        }

        $this->uploadImage($_SESSION['driver_id'], $this->file);
    }

    private function checkPicType() {
        $result;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->file['tmp_name']);
        finfo_close($finfo);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($mimeType, $allowedTypes) && in_array($extension, $allowedExtensions)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    private function checkPicSize() {
        $megabytes5 = 5 * 1024 * 1024; //5MB
        $result;
        if ($this->file['size'] > $megabytes5) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    private function handleUploadError($errorCode) {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the server's upload_max_filesize setting.",
            UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive in the form.",
            UPLOAD_ERR_PARTIAL    => "The file was only partially uploaded.",
            UPLOAD_ERR_NO_FILE    => "No file was uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder on the server.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION  => "A PHP extension stopped the upload."
        ];

        $message = $messages[$errorCode] ?? "Unknown upload error.";

        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
        exit();
    }
}

?>