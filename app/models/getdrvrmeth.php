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

        $encryptedFirstName = $result['firstName'];
        $encryptedLastName = $result['lastName'];
        $encryptedMobileNum = $result['mobileNumber'];
        $encryptedBirthdate = $result['birthdate'];
        return [
            $drvrid = $result['driverid'],
            $username = $result['username'],
            $dbEmail = $result['email'],
            $dbFirstName = Crypto::decrypt($encryptedFirstName, $key),
            $dbLastName = Crypto::decrypt($encryptedLastName, $key),
            $dbMobileNum = Crypto::decrypt($encryptedMobileNum, $key),
            $dbBirthdate = Crypto::decrypt($encryptedBirthdate, $key),
        ];
    }

    public function getDrvrStats($drvrid) {
        return $this->retrieveDriver($drvrid);
    }
}

?>