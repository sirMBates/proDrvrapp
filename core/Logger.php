<?php

namespace core;

class Logger {
    protected string $logDir;
    protected string $baseName;
    protected string $logFile;
    protected int $retentionDays = 30; // Automatically delete logs older than 30 days
    protected int $compressAfterDays = 2; // Compress Logs older than this many days

    public function __construct(string $logFile, int $retentionDays = 30, int $compressAfterDays = 2) {
        $this->logDir = dirname($logFile);
        $this->baseName = basename($logFile, '.log');
        $this->retentionDays = $retentionDays;
        $this->compressAfterDays  = $compressAfterDays;

        // Ensure directory exists
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }
        // Rotate automatically per day
        $this->rotateLogFile();
        $this->manageLogs(); // Run cleanup + compression once per initialization
    }

    protected function rotateLogFile(): void {
        $today = date('Y-m-d');
        $this->logFile = "{$this->logDir}/{$this->baseName}-{$today}.log";

        // Create file if missing
        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, "=== Log started on {$today} ===" . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

    protected function write(string $level, string $message): void {
        // Ensure current day's Log is used (auto-rotate if day changes)
        $this->rotateLogFile();

        $timestamp = date('Y-m-d H:i:s');
        $formatted = "[$timestamp][$level] $message" . PHP_EOL;
        // FILE_APPEND with LOCK_EX prevents concurrent writes from failing
        file_put_contents($this->logFile, $formatted, FILE_APPEND | LOCK_EX);
    }

    protected function manageLogs(): void {
        $files = glob("{$this->logDir}/{$this->baseName}-*.log");
        $now = time();
        $deleted = 0;
        $compressed = 0;

        foreach ($files as $file) {
            if (preg_match('/(\d{4}-\d{2}-\d{2})/', $file, $matches)) {
                $fileDate = strtotime($matches[1]);
                if (!$fileDate) continue;

                $ageDays = ($now - $fileDate) / 86400;

                // 🗜️ Compress logs older than X days (if not already compressed)
                $gzFile = $file . '.gz';
                if ($ageDays > $this->compressAfterDays && !file_exists($gzFile)) {
                    $this->compressLog($file, $gzFile);
                    $compressed++;
                }

                // 🧹 Delete logs older than retention period
                if ($ageDays > $this->retentionDays) {
                    if (@unlink($file) || @unlink($gzFile)) {
                        $deleted++;
                    }
                }
            }
        }

        if ($compressed > 0) {
            $this->write('INFO', "🗜️ Compressed {$compressed} old log file(s).");
        }
        if ($deleted > 0) {
            $this->write('INFO', "🧹 Auto-cleaned {$deleted} old log file(s).");
        }
    }
    
    ## Compress a log file into .gz format.
    protected function compressLog(string $source, string $destination): void {
        $data = file_get_contents($source);
        if ($data === false) return;

        $gz = gzopen($destination, 'w9');
        if ($gz === false) return;

        gzwrite($gz, $data);
        gzclose($gz);

        // Delete original after successful compression
        @unlink($source);
    }

    public function emergency(string $message): void {
        $this->write('EMERGENCY', $message);
    }

    public function alert(string $message): void {
        $this->write('ALERT', $message);
    }

    public function critical(string $message): void {
        $this->write('CRITICAL', $message);
    }

    public function debug(string $message): void {
        $this->write('DEBUG', $message);
    }

    public function notice(string $message): void {
        $this->write('NOTICE', $message);
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

    /*public function log(string $level, string $message): void {
        $this->write(strtoupper($level), $message);
    }*/
}

?>
