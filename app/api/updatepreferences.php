<?php

declare(strict_types=1);

use App\Services\PreferenceService;

requireLoginAjax();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);
if ($driverId < 1) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid user session.'
    ]);
    exit();
}

$headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
$sessionToken = $_SESSION['drvr_token'] ?? null;

if (!$headerToken || !$sessionToken || !hash_equals($sessionToken, $headerToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied' // Invalid CSRF Token
    ]);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

    if (!is_array($input)) {
        throw new InvalidArgumentException('Invalid request body.');
    }

    $preference = $input['preference'] ?? null;
    if (!is_string($preference) || $preference === '') {
        throw new InvalidArgumentException('Preference is required.');
    }

    if (!array_key_exists('value', $input)) {
        throw new InvalidArgumentException('Preference value is required.');
    }

    $service = new PreferenceService();
    $preferences = $service->updatePreference($driverId, $preference, $input['value']);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Preference updated successfully.',
        'data' => [
            'preferences' => $preferences
        ]
    ]);
    exit();
} catch (JsonException $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON request.'
    ]);
    exit();
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unexpected server error.'
    ]);
    exit();
}

?>