<?php

use Core\Database;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class GetDriver {
    protected function retrieveDriver($drvrid) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $sql = "SELECT * FROM drivers
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
        $profilePicture = $result['profile_picture'] ?? null;
        return [
            'driverId' => $result['driver_id'],
            'username' => $result['username'],
            'email' => $result['email'],
            'operatorid' => $result['operator_id'],
            'firstName' => $dbFirstName,
            'lastName' => $dbLastName,
            'mobileNumber' => $dbMobileNum,
            'birthdate' => $dbBirthdate,
            'profilePicture' => $profilePicture // This will be a relative path to the image
        ];
    }

    public function getDrvrInfo($drvrid) {
        return $this->retrieveDriver($drvrid);
    }
}

// Make sure that the uploads/profiles/ directory is accessible by the web server. Set the appropriate permissions for the directory:
//chmod -R 755 uploads/profiles/ if I choose to name it that directory.
?>