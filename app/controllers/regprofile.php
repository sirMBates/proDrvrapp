<?php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
require_once "../../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__, '../../.local.env');
$dotenv->load();
$alert = new core\Flash();

if (session_status() !== 2) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['__method'])) {
    $method = strtoupper($_POST['__method']);
}

if ($method === 'PATCH') {
    if (isset($_POST['reginfo'])) {
        // Get encryption key ↓
        $encryptKey = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        // Getting the info from the form using POST method from the name attribute.
        $firstname = htmlspecialchars(trim($_POST['forename']));
        $lastname = htmlspecialchars(trim($_POST['surname']));
        $mobileNum = htmlspecialchars(trim($_POST['mobilenum']));
        $birthdate = htmlspecialchars(trim($_POST['dateofbirth']));
        $formToken = htmlspecialchars(trim($_POST['drvrtoken']));
        // Encrypt the values before entering the database ↓
        $encryptedFirstName = Crypto::encrypt($firstname, $encryptKey);
        $encryptedLastName = Crypto::encrypt($lastname, $encryptKey);
        $encryptedMobileNumber = Crypto::encrypt($mobileNum, $encryptKey);
        // Instantiate the add user controller class. ↓
        include_once base_path("app/models/regprofilemeth.php");
        include_once base_path("app/classes/completeregis.php");
        $enterData = new RegProContr($encryptedFirstName, $encryptedLastName, $encryptedMobileNumber, $birthdate, $formToken);
        $enterData->processProfile();
        // Go to signin page after firstname, lastname, mobile and birthdate has been successfully entered. ↓
        $alert::setMsg('success', 'You\'ve updated your profile successfully! Please sign in to continue.');
        header("Location: /signin?success=profile+updated");
        exit();
    }
};
?>