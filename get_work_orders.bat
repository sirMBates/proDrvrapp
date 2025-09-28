@echo off
REM -------------------------
REM Configuration
REM -------------------------
set PHP_PATH=C:\php\php.exe
set SCRIPT_PATH=D:\webapps\prodrvrapp\public\index.php
set MASTER_LOG=D:\webapps\logs\job_import_master.log

REM Get current timestamp
for /f "tokens=1-5 delims=/:. " %%a in ("%date% %time%") do (
    set TIMESTAMP=%%a-%%b-%%c_%%d-%%e
)

REM Log start of task
echo [%DATE% %TIME%] Task Scheduler started PHP script >> "%MASTER_LOG%"

REM Run PHP script with a query string-like parameter for the job import and redirect both stdout and stderr to master log
"%PHP_PATH%" "%SCRIPT_PATH%" job=import >> "%MASTER_LOG%" 2>&1

REM Check exit code
if %ERRORLEVEL% neq 0 (
    echo [%DATE% %TIME%] PHP script exited with error code %ERRORLEVEL% >> "%MASTER_LOG%"
) else (
    echo [%DATE% %TIME%] PHP script completed successfully >> "%MASTER_LOG%"
)

REM Extra blank line for readability
echo. >> "%MASTER_LOG%"


