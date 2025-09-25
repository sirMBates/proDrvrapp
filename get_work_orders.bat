@echo off
REM -------------------------
REM Configuration
REM -------------------------
set PHP_PATH=C:\php\php.exe
set SCRIPT_PATH=D:\webapps\prodrvrapp\app\repository\jobordermodel.php

REM Time ranges (24-hour)
set START_HOUR_WEEKDAY=16
set START_HOUR_WEEKEND=6
set END_HOUR=23

REM Interval in seconds between runs
set INTERVAL=900  REM 900 sec = 15 minutes

:LOOP
REM Get current day of week (0=Sun, 1=Mon, ..., 6=Sat)
for /f "tokens=1" %%d in ('powershell -command "(Get-Date).DayOfWeek.value__"') do set DAYOFWEEK=%%d

REM Get current hour
for /F "tokens=1-2 delims=:" %%a in ("%time%") do set HOUR=%%a
set /A HOUR=1%HOUR%-100

REM Decide whether to run
set RUNSCRIPT=0

REM Mon-Fri (1-5) 16:00 - 23:59
if %DAYOFWEEK% GEQ 1 if %DAYOFWEEK% LEQ 5 if %HOUR% GEQ %START_HOUR_WEEKDAY% if %HOUR% LEQ %END_HOUR% set RUNSCRIPT=1

REM Sat-Sun (0,6) 06:00 - 23:59
if %DAYOFWEEK% EQU 0 if %HOUR% GEQ %START_HOUR_WEEKEND% if %HOUR% LEQ %END_HOUR% set RUNSCRIPT=1
if %DAYOFWEEK% EQU 6 if %HOUR% GEQ %START_HOUR_WEEKEND% if %HOUR% LEQ %END_HOUR% set RUNSCRIPT=1

REM Run PHP if allowed
if %RUNSCRIPT% EQU 1 (
    echo [%DATE% %TIME%] Running PHP script...
    %PHP_PATH% "%SCRIPT_PATH%"
) else (
    echo [%DATE% %TIME%] Outside allowed time/day range. Skipping...
)

REM Wait for the interval
timeout /t %INTERVAL% /nobreak >nul
goto LOOP

