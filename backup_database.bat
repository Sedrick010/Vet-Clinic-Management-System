@echo off
setlocal enabledelayedexpansion

REM Set date format for backup file names
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "YYYY=%dt:~0,4%"
set "MM=%dt:~4,2%"
set "DD=%dt:~6,2%"
set "HH=%dt:~8,2%"
set "Min=%dt:~10,2%"
set "Sec=%dt:~12,2%"
set "datestamp=%YYYY%%MM%%DD%_%HH%%Min%%Sec%"

REM Create backup directory if it doesn't exist
if not exist "database_backups" mkdir database_backups

REM Database credentials
set DB_USER=root
set DB_PASS=
set MYSQL_PATH=C:\xampp\mysql\bin

REM Main database name from .env file
for /f "tokens=1,* delims==" %%a in ('findstr "DB_DATABASE" .env') do (
    set "DB_NAME=%%b"
)

echo Backing up main database: %DB_NAME%
"%MYSQL_PATH%\mysqldump.exe" --user=%DB_USER% --host=localhost --result-file="database_backups\%DB_NAME%_%datestamp%.sql" %DB_NAME%
if %ERRORLEVEL% neq 0 (
    echo Failed to backup main database.
    goto :error
) else (
    echo Main database backup completed successfully.
)

REM Get all clinic databases from the database
echo Backing up tenant databases...
for /f "tokens=*" %%a in ('"%MYSQL_PATH%\mysql.exe" --user=%DB_USER% --host=localhost --silent --skip-column-names -e "SELECT database_name FROM %DB_NAME%.clinics WHERE approval_status='approved'"') do (
    set TENANT_DB=%%a
    echo Backing up tenant database: !TENANT_DB!
    "%MYSQL_PATH%\mysqldump.exe" --user=%DB_USER% --host=localhost --result-file="database_backups\!TENANT_DB!_%datestamp%.sql" !TENANT_DB!
    if !ERRORLEVEL! neq 0 (
        echo Failed to backup tenant database: !TENANT_DB!
    ) else (
        echo Tenant database !TENANT_DB! backup completed successfully.
    )
)

echo All backups completed. Files saved to database_backups directory.
goto :end

:error
echo An error occurred during backup process.

:end
pause 