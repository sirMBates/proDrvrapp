<?php

declare(strict_types=1);

define('BASE_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
define('HOME_PATH', __DIR__ . DIRECTORY_SEPARATOR);

require BASE_PATH . 'core/Helperfunc.php';
require BASE_PATH . 'vendor/autoload.php';
require BASE_PATH . 'config.php';

use App\ImportExport\JobOrderImporter;
use Core\Logger;
use App\Validation\ImporterAssignmentValidator;
use App\Middleware\ScriptsMiddleware;

return new class {
    private Logger $logger;

    public function __construct() {
        // Centralized Logger ( used for CLI jobs )
        $this->logger = new Logger(BASE_PATH . 'storage/logs/job_import_master.log');
    }

    public function handleCli(array $argv): void {
        $args = [];

        foreach ($argv as $arg) {
            if (strpos($arg, '=') !== false) {
                [$k, $v] = explode('=', $arg, 2);
                $args[$k] = $v;
            }
        }

        $job = $args['job'] ?? null;

        if ($job === 'import') {
            $excelFile = 'D:/Documents/TestAssignments.xlsx';
            file_put_contents(BASE_PATH . 'storage/logs/debug_task.log', "[" . date('Y-m-d H:i:s') . "] CLI job detected: {$job}\n", FILE_APPEND);
            $validator = new ImporterAssignmentValidator($this->logger);
            $importer = new JobOrderImporter($excelFile, $this->logger, $validator);
            $importer->run();

        } elseif ($job === 'assignments') {
            // Call Assignment-specific tasks if needed
            $this->logger->info("Assignments job placeholder executed");

        } else {
            $this->logger->info("No valid CLI job executed");
        }
    }

    /*
    *   Handle HTTP requests ( normal web app flow )
    */

    public function handleHttps(): void {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
        $method = $_POST['__method'] ?? $_SERVER['REQUEST_METHOD'];

        // Run script middleware BEFORE routing
        $GLOBALS['page_scripts'] = ScriptsMiddleware::resolve($uri);

        $isAuthenticated = isset($_SESSION['user_id'], $_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

        // Prevent authenticated users from returning to auth/account setup pages
        if ($isAuthenticated && in_array($uri, ['/signup', '/signin', '/register', '/forget', '/reset-password'], true)) {
            header('Location: /');
            exit();
        }

        // Handle unauthenticated root request
        if (!$isAuthenticated && $uri === '/') {
            if (!isset($_COOKIE['driver_registered'])) {
                header('Location: /signup');
                exit();
            }

            header('Location: /signin');
            exit();
        }

        $router = new \Core\Router;

        // Routes available outside the authenticated role areas
        require base_path('app/routes/auth.php');
        require base_path('app/routes/public.php');

        // Load only the route map belonging to the authenticated role
        if ($isAuthenticated) {
            switch ($_SESSION['role'] ?? null) {
                case 'driver':
                    require base_path('app/routes/driver.php');
                    break;

                default:
                    http_response_code(403);
                    exit();
            }
        }

        $router->route($uri, $method);
    }
}

?>