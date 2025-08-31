<?php

use core\Database;
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
        $sql = "SELECT * FROM Driver
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
        $dbProfilePicture = Crypto::decrypt($result['profliePicture'], $key);
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

    public function getDrvrInfo($drvrid) {
        return $this->retrieveDriver($drvrid);
    }
}

// Make sure that the uploads/profiles/ directory is accessible by the web server. Set the appropriate permissions for the directory:
//chmod -R 755 uploads/profiles/ if I choose to name it that directory.
?>