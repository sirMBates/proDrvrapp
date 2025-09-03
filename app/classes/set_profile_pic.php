<?php

class SetDrvrPictureContr extends ProfileImageUpload {
    private $file;

    public function __construct($file) {
        $this->file = $file;
    }

    public function setProfilePicture() {
        if ($this->isPhotoMissing()) {
            http_response_code(415);
            echo json_encode([
                'status' => 'error',
                'message' => 'There is no image/photo available!'
            ]);
            exit();
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

        $this->uploadImage($_SESSION['driverid'], $this->file);
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

    private function isPhotoMissing() {
        // Missing if tmp_name is empty OR the file is not a valid uploaded file
        return empty($this->file['tmp_name']) || !is_uploaded_file($this->file['tmp_name']);
    }
}

?>