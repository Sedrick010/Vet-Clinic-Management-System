@echo off
REM VetClinic Database Restore Script

if "%~1"=="" (
    echo Error: Missing database name
    echo Usage: restore_database.bat database_name backup_file.sql
    exit /b 1
)

if "%~2"=="" (
    echo Error: Missing backup file
    echo Usage: restore_database.bat database_name backup_file.sql
    exit /b 1
)

echo ========================================
echo VetClinic Database Restoration
echo ========================================
echo.
echo WARNING: This will REPLACE the current database "%~1" with the backup.
echo All current data in "%~1" will be LOST.
echo.
echo Database to restore: %~1
echo Backup file: %~2
echo.
set /p CONFIRM=Are you sure you want to continue? (y/N): 

if /i "%CONFIRM%" NEQ "y" (
    echo Restore cancelled.
    exit /b 0
)

echo.
echo Starting restoration process...
php restore_database.php %1 %2

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Restoration failed! Please check the error messages above.
) else (
    echo.
    echo Restoration completed successfully!
)

pause 