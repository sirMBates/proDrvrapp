<?php

declare(strict_types=1);

use App\Repositories\AssignmentRepository;
use App\Repositories\DriverStatusRepository;
use App\Repositories\EmergencyRepository;
use App\Services\DriverStatusService;
use App\Services\EmergencyService;
use Core\Database;
use Core\Flash;

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

/*
 * Destroy the authenticated session and immediately create a
 * clean session for the post-logout flash message.
 */
$destroyAuthenticatedSession = static function (): void {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]
        );
    }

    session_destroy();
    session_id('');
    session_start();
    session_regenerate_id(true);
};

header('Content-Type: application/json; charset=utf-8');

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.'
    ]);
    exit();
}

$driverId = (int) ($_SESSION['user_id'] ?? 0);
if ($driverId < 1) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required.'
    ]);
    exit();
}

$csrfToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$sessionToken = (string) ($_SESSION['drvr_token'] ?? '');

if ($csrfToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid security token.'
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request data.'
    ]);
    exit();
}

$action = trim((string) ($input['action'] ?? ''));

$allowedActions = [
    'end_shift_and_logout',
    'logout_only'
];

if (!in_array($action, $allowedActions, true)) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid sign-out option.'
    ]);
    exit();
}

$pdo = null;
$statusRecord = null;

try {
    if ($action === 'end_shift_and_logout') {
        $pdo = (new Database())->connect();

        $assignmentRepository = new AssignmentRepository($pdo);
        $driverStatusRepository = new DriverStatusRepository($pdo);
        $emergencyRepository = new EmergencyRepository($pdo);

        $emergencyService = new EmergencyService($pdo, $emergencyRepository, $driverStatusRepository);
        $driverStatusService = new DriverStatusService($driverStatusRepository, $assignmentRepository, $emergencyService);

        if (!$pdo->beginTransaction()) {
            throw new RuntimeException('Unable to begin End of Shift transaction.');
        }

        $statusRecord = $driverStatusService->endShiftIfNeeded($driverId);

        $pdo->commit();
    }

    $destroyAuthenticatedSession();
    Flash::setMsg('success', 'See you next time!');
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'You have been signed out.',
        'data' => [
            'shiftEnded' => $action === 'end_shift_and_logout',
            'statusRecord' => $statusRecord,
            'redirect' => '/signin?success=logged+out&status=unofficial'
        ]
    ]);
    exit();
} catch (InvalidArgumentException $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('[LOGOUT ERROR] ' . $e::class . ': ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'You could not be signed out. Please try again.'
    ]);
    exit();
}

?>