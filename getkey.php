<?php
require_once 'vendor/autoload.php';
use Defuse\Crypto\Key;

$key = Key::createNewRandomKey();
$keyAscii = $key->saveToAsciiSafeString();

echo "Paste this into your .env file:\n\n";
echo "SECRET_KEY={$keyAscii}\n";

/* Use this file to create a key for encryption/decryption for Defuse
## Call this file in the terminal and copy the key and save to .env
*/
