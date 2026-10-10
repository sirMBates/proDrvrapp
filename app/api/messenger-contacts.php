<?php

declare(strict_types=1);

use Core\Database;
use App\Repositories\UserRepository;
use App\Services\MessengerContactService;

requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.'
    ]);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);
$loggedIn = ($_SESSION['logged_in'] ?? false) === true;

if ($driverId <= 0 || !$loggedIn) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required.'
    ]);
    exit();
}

// This read-only endpoint does not need to modify the session.
// Release its lock before database access and decryption.
session_write_close();

try {
    $pdo = (new Database())->connect();

    $userRepository = new UserRepository($pdo);

    $driver = $userRepository->findById($driverId);

    if ($driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Messenger requires an active driver account.'
        ]);
        exit();
    }

    $contactService = new MessengerContactService($userRepository);
    $contacts = $contactService->contactsForDriver($driverId);

    echo json_encode([
        'status' => 'success',
        'contacts' => $contacts
    ], JSON_THROW_ON_ERROR);
    exit();

} catch (Throwable $e) {
    error_log('Messenger contacts failed: ' . get_class($e));
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Messenger contacts could not be loaded.'
    ]);
    exit();
}

?>