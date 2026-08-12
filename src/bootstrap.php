<?php

declare(strict_types=1);

define('BASE_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
define('HOME_PATH', __DIR__ . DIRECTORY_SEPARATOR);

require BASE_PATH . 'core/Helperfunc.php';
require BASE_PATH . 'vendor/autoload.php';
require BASE_PATH . 'config.php';

use Core\Logger;
use App\Validation\ImporterAssignmentValidator;

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
            require_once BASE_PATH . 'app/repository/jobordermodel.php';

            $excelFile = 'C:/Users/bates/OneDrive/Documents/testworkassignment.xlsx';
            file_put_contents(BASE_PATH . 'storage/logs/debug_task.log', "[" . date('Y-m-d H:i:s') . "] CLI job detected: {$job}\n", FILE_APPEND);
            $validator = new ImporterAssignmentValidator($this->logger);
            $importer = new JobOrderImporter($excelFile, $this->logger, $validator);
            $importer->run();

        } elseif ($job === 'assignments') {
            require BASE_PATH . 'app/models/assignmentmodel.php';
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
        $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
        $method = $_POST['__method'] ?? $_SERVER['REQUEST_METHOD'];

        if (isset($_SESSION['driver_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            // prevent Logged-in users from hitting signup/signin
            if (in_array($uri, ['/signup', '/signin', '/register', '/forget', '/completereset'])) {
                header("Location: /");
                exit();
            }

            $router = new \Core\Router;
            $routes = require base_path("routes.php");
            $router->route($uri, $method);
            return;
        }

        if ($uri === '/') {
            if (!isset($_COOKIE['driver_registered'])) {
                header("Location: /signup");
                exit();
            }

            header("Location: /signin");
            exit();
        }

        $router = new \Core\Router;
        $routes = require base_path("routes.php");
        $router->route($uri, $method);
    }
};

/*spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    require base_path("{$class}.php");
});*/

# To use old router file, use the path below this comment ⬇
# require base_path('core/router.php');

/*$router = new \Core\Router;

$routes = require base_path("routes.php");

$router->route($uri, $method);*/

?>