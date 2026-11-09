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

function requireLoginAjax() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        http_response_code(403);
        echo json_encode(['error' => 'Direct access not allowed']);
        exit();
    }
}

function routeSwitcher(?string $role): void {
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