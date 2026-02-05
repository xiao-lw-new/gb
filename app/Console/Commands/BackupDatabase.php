<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:backup-db';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Daily database backup to /data/database/backlup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $backupDir = '/data/database/backlup';
        
        // 1. 确保备份目录存在
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");
        $filename = "db_backup_" . Carbon::now()->format('Y-m-d_H-i-s');

        $this->info("Starting backup for connection: {$connection}...");

        try {
            if ($connection === 'sqlite') {
                $dbPath = $dbConfig['database'];
                $destPath = "{$backupDir}/{$filename}.sqlite";
                
                if (File::exists($dbPath)) {
                    File::copy($dbPath, $destPath);
                    $this->info("Backup saved to: {$destPath}");
                } else {
                    $this->error("SQLite database file not found at: {$dbPath}");
                }
            } elseif ($connection === 'mysql') {
                $destPath = "{$backupDir}/{$filename}.sql";
                $command = sprintf(
                    'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                    escapeshellarg($dbConfig['username']),
                    escapeshellarg($dbConfig['password']),
                    escapeshellarg($dbConfig['host']),
                    escapeshellarg($dbConfig['port']),
                    escapeshellarg($dbConfig['database']),
                    escapeshellarg($destPath)
                );
                
                $returnVar = NULL;
                $output = NULL;
                exec($command, $output, $returnVar);

                if ($returnVar === 0) {
                    $this->info("Backup saved to: {$destPath}");
                    exec("gzip " . escapeshellarg($destPath));
                    $this->info("Backup compressed: {$destPath}.gz");
                } else {
                    $this->error("MySQL backup failed with exit code: {$returnVar}");
                }
            } elseif ($connection === 'pgsql') {
                $destPath = "{$backupDir}/{$filename}.sql";
                // 使用 PGPASSWORD 环境变量传递密码
                $command = sprintf(
                    'PGPASSWORD=%s pg_dump -U %s -h %s -p %s %s > %s',
                    escapeshellarg($dbConfig['password']),
                    escapeshellarg($dbConfig['username']),
                    escapeshellarg($dbConfig['host']),
                    escapeshellarg($dbConfig['port']),
                    escapeshellarg($dbConfig['database']),
                    escapeshellarg($destPath)
                );

                $returnVar = NULL;
                $output = NULL;
                exec($command, $output, $returnVar);

                if ($returnVar === 0) {
                    $this->info("Backup saved to: {$destPath}");
                    exec("gzip " . escapeshellarg($destPath));
                    $this->info("Backup compressed: {$destPath}.gz");
                } else {
                    $this->error("PostgreSQL backup failed with exit code: {$returnVar}");
                }
            } else {
                $this->warn("Backup for driver [{$connection}] is not implemented.");
            }

            // 2. 清理超过 14 天的备份
            $this->cleanupOldBackups($backupDir);

        } catch (\Exception $e) {
            $this->error("Backup error: " . $e->getMessage());
        }
    }

    /**
     * Clean up backups older than 14 days.
     */
    protected function cleanupOldBackups(string $dir)
    {
        $this->info("Cleaning up old backups (older than 14 days)...");
        
        $files = File::files($dir);
        $threshold = Carbon::now()->subDays(14);

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp($file->getMTime());
            if ($lastModified->lt($threshold)) {
                File::delete($file->getPathname());
                $this->info("Deleted old backup: " . $file->getFilename());
            }
        }
    }
}
