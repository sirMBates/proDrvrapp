@echo off
REM -------------------------
REM Configuration
REM -------------------------
set PHP_PATH=C:/php/php.exe
set SCRIPT_PATH=D:/webapps/prodrvrapp/src/bootstrap.php
set MASTER_LOG=D:/webapps/logs/work_scheduler.log
set DEBUG_LOG=D:/webapps/logs/debug_task.log

REM Ensure logs directory exists
if not exist "D:/webapps/logs" mkdir "D:/webapps/logs"

REM Log start of task
echo [%DATE% %TIME%] Task Scheduler started >> "%MASTER_LOG%"

REM If no argument is passed to the bat, default to job=import
if "%1"=="" (
    set ARG=job=import
) else (
    set ARG=%1
)

REM Run PHP script with argument and redirect stdout & stderr
"%PHP_PATH%" "%SCRIPT_PATH%" %ARG% >> "%MASTER_LOG%" 2>&1

REM Log end of task
echo [%DATE% %TIME%] Task Scheduler finished >> "%MASTER_LOG%"
echo Argument passed: %ARG% >> "%DEBUG_LOG%"

