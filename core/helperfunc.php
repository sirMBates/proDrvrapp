<?php

function dd($value) {
    echo "<pre>";
    var_dump($value);
    echo "</pre>";

    die();
}

function urlIs($value) {
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === $value;
}

function base_path($path) {
    return BASE_PATH . $path;
}

function home_path($path) {
    return HOME_PATH . $path;
}

function view($path, $attributes = []) {
    extract($attributes);
    require base_path('app/views/' . $path);
}

function requireLoginAjax(?string $requiredRole = null): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    header('Content-Type: application/json; charset=utf-8');

    // 1. Verify the authenticated session.
    $isAuthenticated = isset($_SESSION['user_id'], $_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

    if (!$isAuthenticated) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Authentication required.'
        ]);
        exit();
    }

    // 2. Verify role authorization when required.
    if ($requiredRole !== null && ($_SESSION['role'] ?? null) !== $requiredRole) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Access denied.'
        ]);
        exit();
    }

    // 3. Reject direct browser navigation.
    if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'xmlhttprequest') {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Direct access not allowed.'
        ]);
        exit();
    }
}

function routeSwitcher(\Core\Router $router, ?string $role): void {
    switch($role) {
        case 'driver':
            require base_path('app/routes/driver.php');
            break;

        case 'dispatch':
            require base_path('app/routes/dispatch.php');
            break;

        case 'admin':
            require base_path('app/routes/admin.php');
            break;

        default:
            http_response_code(403);
            exit();
    }
}

?>