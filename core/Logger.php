<?php

namespace core;

class Logger {
    protected string $logFile;

    public function __construct(string $logFile = __DIR__ . '/../logs/job_import_master.log') {
        $this->logFile = $logFile;
    }

    public function log(string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
}

?>
