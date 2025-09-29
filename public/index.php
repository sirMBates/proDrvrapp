<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

file_put_contents('D:/webapps/logs/debug_argv.log', print_r($argv, true));

// Use necessary classes
use core\Logger;
require_once __DIR__ . '/../app/repository/jobordermodel.php';
require_once __DIR__ . '/../app/models/assignmentmodel.php';

// Instantiate central logger
$log = new Logger('D:/webapps/logs/job_import_master.log');

// Log task start
$log->info("Task Scheduler started PHP script");

$jobArg = $argv[1] ?? null;

// Only run job import if called with ?job=import
if ($jobArg === 'job=import') {
    $log->info("Job import argument detected: {$jobArg}");
    // Run the importer
    $excelFile = 'C:/Users/bates/OneDrive/Documents/testworkassignment.xlsx';
    $importer = new JobOrderImporter($excelFile, $log);
    $importer->run();
} else {
    $log->info("No job parameter detected; skipping import");
}

// Log task end
$log->info("Task Scheduler completed PHP script");

?>