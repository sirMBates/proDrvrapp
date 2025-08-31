<?php

use core\Database;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;
require_once "../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.local.env');
$dotenv->load();

class ProfileImageUpload {
    public function uploadImage($drvrid, $file) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;

        // Check for file type (image) and size
        if (!in_array($file['type'], ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'])) {
            echo json_encode(['error' => 'Invalid image type. Only JPG, JPEG, PNG, or GIF are allowed.']);
            exit();
        }
        if ($file['size'] > 5 * 1024 * 1024) { // Limit to 5MB
            echo json_encode(['error' => 'Image size exceeds the limit of 5MB.']);
            exit();
        }

        // Create a directory for the user if it doesn't exist
        $uploadDir = '../../public/uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Create a unique filename using the driver's ID and current timestamp
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = strtolower($drvrid . '_' . time() . '.' . $extension);
        $filePath = $uploadDir . $filename;

        // Move the uploaded file to the server directory
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            echo json_encode(['error' => 'Error uploading the image.']);
            exit();
        }

        $encryptedProfilePicture = Crypto::encrypt($filePath, $key);
        // Update the database with the path to the profile picture
        $sql = "UPDATE Driver
                SET profilePicture = :profilePicture
                WHERE driverid = :driverid";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':profilePicture', $encryptedProfilePicture);
        $stmt->bindParam(':driverid', $drvrid);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Profile picture uploaded successfully.']);
        } else {
            echo json_encode(['error' => 'Error updating profile picture.']);
        }
    }
}
?>