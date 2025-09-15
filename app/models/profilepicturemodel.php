<?php

use core\Database;

class ProfileImageUpload extends GetDriver {
    protected function uploadImage($drvrid, $file) {
        $db = new Database;
        $driverInformation = $this->getDrvrInfo($drvrid);
        $operatorName = [
            $driverInformation['firstName'],
            $driverInformation['lastName']
        ];
        $firstInitial = $operatorName[0][0];
        // Create a directory for the user if it doesn't exist
        $uploadDir = base_path('public/uploads/profiles/' . $firstInitial . $operatorName[1] . '-' . $drvrid . '/');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Create a unique filename using the driver's ID and current timestamp
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = strtolower($drvrid . '_' . time() . '.' . $extension);
        $filePath = $uploadDir . $filename;

        //move_uploaded_file($file['tmp_name'], $filePath);

        // Store **relative URL** in database for frontend
        $publicPath = '/uploads/profiles/' . $firstInitial . $operatorName[1] . '-' . $drvrid . '/' . $filename;

        // Move the uploaded file to the server directory
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error uploading the image.'
            ]);
            exit();
        }

        // Update the database with the path to the profile picture
        $sql = "UPDATE driver
                SET profile_picture = :profile_picture
                WHERE driver_id = :driver_id";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':profile_picture', $publicPath);
        $stmt->bindParam(':driver_id', $drvrid);
        $stmt->execute();

        if (!$stmt) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'There was a problem with your request.'
            ]);
            exit();
        }
    }
}
?>