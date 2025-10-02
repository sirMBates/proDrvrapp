<?php

namespace core;

class Logger {
    protected string $logFile;

    public function __construct(string $logFile) {
        $this->logFile = $logFile;

        // Ensure directory exists
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    protected function write(string $level, string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        // FILE_APPEND with LOCK_EX prevents concurrent writes from failing
        file_put_contents($this->logFile, "[$timestamp][$level] $message\n", FILE_APPEND | LOCK_EX);
    }

    public function debug(string $message): void {
        $this->write('DEBUG', $message);
    }


    public function log(string $message): void {
        $this->write('LOG', $message);
    }

    public function info(string $message): void {
        $this->write('INFO', $message);
    }

    public function warning(string $message): void {
        $this->write('WARNING', $message);
    }

    public function error(string $message): void {
        $this->write('ERROR', $message);
    }
}

?>
