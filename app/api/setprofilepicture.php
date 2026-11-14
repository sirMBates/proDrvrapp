<?php

declare(strict_types=1);

use App\Services\DriverProfileService;
use App\Services\ProfileImageService;
use App\Validation\ProfileImageValidator;

$method = strtoupper($_POST['__method'] ?? $_SERVER['REQUEST_METHOD']);

if (!in_array($method, ['GET', 'PATCH'], true)) {
    http_response_code(405);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);
if ($driverId < 1) {
    http_response_code(401);
    exit();
}

if ($method === 'GET') {
    $profileService = new DriverProfileService();
    $driver = $profileService->driverProfile($driverId);
    $storedPath = $driver['profilePicture'] ?? null;

    if (!$storedPath) {
        http_response_code(404);
        exit();
    }

    $uploadRoot = realpath(BASE_PATH . 'storage/uploads');
    $filePath = realpath(BASE_PATH . 'storage/uploads/' . $storedPath);

    if ($uploadRoot === false || $filePath === false || !str_starts_with($filePath, $uploadRoot . DIRECTORY_SEPARATOR) || !is_file($filePath)) {
        http_response_code(404);
        exit();
    }

    $mimeType = mime_content_type($filePath);
    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif'
    ];

    if (!in_array($mimeType, $allowedTypes, true)) {
        http_response_code(415);
        exit();
    }

    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, no-cache, no-store, must-revalidate');

    readfile($filePath);
    exit();
}

if ($method === 'PATCH') {
    requireLoginAjax('driver');
    header("Content-Type: application/json; charset=utf-8");

    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    $formToken = isset($_POST['userToken']) ? trim((string) $_POST['userToken']) : null;
    $sessionToken = $_SESSION['user_token'] ?? null;

    if ($sessionToken === null) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No session token found.'
        ]);
        exit();
    }

    $validHeaderToken = $headerToken !== null && hash_equals($sessionToken, $headerToken);
    $validFormToken = $formToken !== null && hash_equals($sessionToken, $formToken);

    if (!$validHeaderToken && !$validFormToken) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Access denied!'
        ]);
        exit();
    }

    if (!isset($_FILES['profileImage'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'No profile image was provided.'
        ]);
        exit();
    }

    $file = $_FILES['profileImage'];
    $imageValidator = new ProfileImageValidator();
    $validation = $imageValidator->validate($file);

    if (!$validation['valid']) {
        http_response_code($validation['http_code']);
        echo json_encode([
            'status' => 'error',
            'message' => $validation['message']
        ]);
        exit();
    }

    try {
        $profileImageService = new ProfileImageService();

        $result = $profileImageService->uploadImage($driverId, $file);

        if (($result['status'] ?? null) !== 'success') {
            http_response_code(500);
        } else {
            http_response_code(200);
        }

    	echo json_encode($result);
    } catch (\Throwable $exception ) {
	    error_log('Profile image upload failed: ' . $exception->getMessage());
	    http_response_code(500);
	    echo json_encode([
	        'status' => 'error',
	        'message' => 'Profile image could not be uploaded.'
	    ]);
    }

    exit();
}

?>