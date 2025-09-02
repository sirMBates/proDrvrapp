<?php

class SetDrvrPictureContr extends ProfileImageUpload {
    private $file;

    public function __construct($file) {
        $this->file = $file;
    }

    public function setProfilePicture() {
        if ($this->checkPicType() === false) {
            http_response_code(415);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid image type. Only JPG, JPEG, PNG, or GIF are allowed.'
            ]);
            exit();
        }

        if ($this->checkPicSize() === true) {
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
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($this->file['type'], $allowedTypes)) {
            $result = false;
        } else {
            $result = true;
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
}

?>