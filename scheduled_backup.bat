@echo off
REM VetClinic Database Backup Script
REM For Windows Task Scheduler

CD /D "%~dp0"
ECHO Starting scheduled backup at %date% %time%

REM Run using PHP script (simpler approach)
php backup_database.php > backup_logs\backup_%date:~-4,4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%.log 2>&1

REM Or run using Laravel command (if registered)
REM php artisan app:backup-databases > backup_logs\backup_%date:~-4,4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%.log 2>&1

ECHO Backup completed 