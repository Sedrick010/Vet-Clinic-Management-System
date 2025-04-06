# VetClinic Database Backup and Restore Procedures

This document provides instructions for backing up and restoring the VetClinic database system, including both the central database and tenant clinic databases.

## Backup Methods

### 1. Manual Backup (Windows)

To manually create a backup of all databases:

1. Open Command Prompt or PowerShell
2. Navigate to the project directory: `cd C:\xampp\htdocs\VetClinicV2`
3. Run the backup script: `backup_database.bat`

This will create backup files in the `database_backups` folder, with a timestamp in the filename.

### 2. PHP Script Backup

For more detailed control:

1. Navigate to the project directory
2. Run: `php backup_database.php`

This will create SQL backup files and an HTML report in the `database_backups` folder.

### 3. Laravel Command Backup (Recommended)

For the most reliable and feature-rich backup:

1. Navigate to the project directory
2. Run: `php artisan app:backup-databases`

Options:
- `--compress` - Compress backup files to save space
- `--destination=local` - Store locally (default)

### 4. Scheduled Automatic Backups

#### Using Windows Task Scheduler:

1. Open Task Scheduler
2. Create a new Basic Task
3. Name it "VetClinic Database Backup"
4. Set the trigger (e.g., Daily at 12:00 AM)
5. Action: Start a program
6. Program/script: `C:\xampp\htdocs\VetClinicV2\scheduled_backup.bat`
7. Finish the wizard

#### Using Laravel Scheduler:

1. Add this to your server's crontab:
   ```
   * * * * * cd /path/to/VetClinicV2 && php artisan schedule:run >> /dev/null 2>&1
   ```
2. The backup will run according to the schedule defined in `app/Console/Kernel.php`

## Backup Types

1. **Main Database Backup**: Backs up the central database containing clinic information
2. **Tenant Database Backups**: Backs up each approved clinic's individual database
3. **Full System Backup**: Backs up all databases (both main and tenant)

## Restore Procedures

### Restoring a Single Database

1. Navigate to the project directory
2. Run: `restore_database.bat database_name backup_file.sql`
   - Example: `restore_database.bat vetclinicv2_new database_backups/vetclinicv2_new_20250406_123045.sql`

Or using the PHP script directly:

```
php restore_database.php database_name backup_file.sql
```

### Full System Restore

To restore all databases:

1. First restore the main database
2. Then restore each tenant database individually
3. Verify connections in the admin panel

## Important Notes

- Always verify your backups after creating them
- Store backups in multiple locations (local and remote)
- Test the restore process regularly
- Schedule regular backups (daily recommended)
- The restore process will REPLACE existing data - use with caution!

## Backup Storage Recommendations

1. Local storage (default): `database_backups` folder in the project directory
2. External hard drive or network storage
3. Cloud storage (e.g., Google Drive, Dropbox, AWS S3)
4. Email important backups to a secure email account

## Troubleshooting

### Common Backup Issues

- **Insufficient permissions**: Run command prompt as Administrator
- **Database locked**: Ensure no queries are running during backup
- **Out of disk space**: Clean up old backups or use compression

### Common Restore Issues

- **Access denied**: Check database user permissions
- **Corrupt backup file**: Always verify backups after creation
- **Version mismatch**: Ensure MySQL versions are compatible

## Emergency Contact

In case of database emergency, contact:
- System Administrator: admin@vetclinic.localhost 