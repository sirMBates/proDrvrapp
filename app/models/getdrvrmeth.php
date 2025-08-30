<?php

use core\Database;
use core\Flash;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;
require_once "../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.local.env');
$dotenv->load();

class GetDriver {
    protected function retrieveDriver($drvrid) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $alert = new Flash();
        $sql = "SELECT * FROM driver
                WHERE driverid = :driverid";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':driverid', $drvrid);
        $stmt->execute();

        $result = $stmt->fetch();

        if (!$stmt || $stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Driver not found']);
            exit();
        }

        $dbFirstName = Crypto::decrypt($result['firstName'], $key),
        $dbLastName = Crypto::decrypt($result['lastName'], $key),
        $dbMobileNum = Crypto::decrypt($result['mobileNumber'], $key),
        $dbBirthdate = Crypto::decrypt($result['birthdate'], $key),
        return [
            'driverid' => $result['driverid'],
            'username' => $result['username'],
            'email' => $result['email'],
            'firstName' => $dbFirstName,
            'lastName' => $dbLastName
            'mobileNumber' => $dbMobileNum
            'birthdate' => $dbBirthdate,
            'profilePicture' => $result['profilepicture'] // This will be a relative path to the image
        ];
    }

    class ProfileImageUpload {
    public function uploadImage($drvrid, $file) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $alert = new Flash();

        // Check for file type (image) and size
        if (!in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
            echo json_encode(['error' => 'Invalid image type. Only JPG, PNG, or GIF are allowed.']);
            exit();
        }
        if ($file['size'] > 5 * 1024 * 1024) { // Limit to 5MB
            echo json_encode(['error' => 'Image size exceeds the limit of 5MB.']);
            exit();
        }

        // Create a directory for the user if it doesn't exist
        $uploadDir = '../../uploads/profiles/';
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

        // Update the database with the path to the profile picture
        $sql = "UPDATE driver SET profilePicture = :profilePicture WHERE driverid = :driverid";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':profilePicture', $filePath);
        $stmt->bindParam(':driverid', $drvrid);

        if ($stmt->execute()) {
                echo json_encode(['success' => 'Profile picture uploaded successfully.']);
            } else {
                echo json_encode(['error' => 'Error updating profile picture.']);
            }
        }
    }

    public function getDrvrStats($drvrid) {
        return $this->retrieveDriver($drvrid);
    }
}

// Make sure that the uploads/profiles/ directory is accessible by the web server. Set the appropriate permissions for the directory:
//chmod -R 755 uploads/profiles/ if I choose to name it that directory.
?>