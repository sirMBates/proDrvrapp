<?php
//require_once __DIR__ . '/vendor/autoload.php';
/*$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, 'local.env');
$dotenv->load();
$dbhost = $_ENV['DB_HOST'];
echo $dbhost;*/
header("location: public/views/drvrsignup.php");
exit();
/*$request = $_SERVER['REQUEST_URI'];
$viewDir = '/public/';

switch ($request) {
    case '':
    case '/views/':
        require __DIR__ . $viewDir . 'drvrsignup.php';
    break;

    case '/views/':
        require __DIR__ . $viewDir . 'onboarding.php';
    break;

    case '/views/':
        require __DIR__ . $viewDir . 'home.php';
    break;

    case '/views/':
        require __DIR__ . $viewDir . 'joborder.php';
    break;

    case '/views/':
        require __DIR__ . $viewDir . 'dprofile.php';
    break;

    case '/views/':
        require __DIR__ . $viewDir . 'payroll.php';
    break;

    case '/views/':
        require __DIR__ . $viewDir . 'calendar.php';
    break;

    case '/views/':
        require __DIR__ . $viewDir . 'expense.php';
    break;

    case '/views/':
        require __DIR__ . $viewDir . 'mailbox.php';
    break;

    default:
        http_response_code(404);
        echo 'Page not found';
}*/
