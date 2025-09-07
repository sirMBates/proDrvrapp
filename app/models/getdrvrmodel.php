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
        $sql = "SELECT * FROM driver
                WHERE driver_id = :driver_id";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':driver_id', $drvrid);
        $stmt->execute();

        $result = $stmt->fetch();

        if (!$stmt || $stmt->rowCount() === 0) {
            throw new Exception("Driver not found");
        }

        $dbFirstName = Crypto::decrypt($result['first_name'], $key);
        $dbLastName = Crypto::decrypt($result['last_name'], $key);
        $dbMobileNum = Crypto::decrypt($result['mobile_number'], $key);
        $dbBirthdate = Crypto::decrypt($result['birth_date'], $key);
        if (!empty($result['profile_picture'])) {
            $result['profile_picture'];
        } else {
            $result['profile_picture'] = NULL;
        }
        return [
            'driverid' => $result['driver_id'],
            'username' => $result['username'],
            'email' => $result['email'],
            'firstName' => $dbFirstName,
            'lastName' => $dbLastName,
            'mobileNumber' => $dbMobileNum,
            'birthdate' => $dbBirthdate,
            'profilePicture' => $result['profile_picture'] // This will be a relative path to the image
        ];
    }

    public function getDrvrInfo($drvrid) {
        return $this->retrieveDriver($drvrid);
    }
}

// Make sure that the uploads/profiles/ directory is accessible by the web server. Set the appropriate permissions for the directory:
//chmod -R 755 uploads/profiles/ if I choose to name it that directory.
?>