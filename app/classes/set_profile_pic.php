<?php

class SetDrvrPictureContr extends ProfileImageUpload {
    private $file;

    protected function __construct($file) {
        $this->file = $file;
    }

    public setProfilePicture() {
        if (checkPicType() === false) {
            http_response_code(415);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid image type. Only JPG, JPEG, PNG, or GIF are allowed.'
            ]);
            exit();
        }

        if (checkPicSize() === true) {
            http_response_code(415);
            echo json_encode([
                'status' => 'error',
                'message' => 'Image size exceeds the limit of 5MB.'
            ]);
            exit();
        }

        $this->uploadImage($_SESSION['driverid'], $this->file);
    }

    private checkPicType() {
        $result;
        if (!preg_match('/\.(jpg|jpeg|png|gif)$/i', $this->file['type']))
        {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    private checkPicSize() {
        $megabytes5 = 5 * 1024 * 1024;
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