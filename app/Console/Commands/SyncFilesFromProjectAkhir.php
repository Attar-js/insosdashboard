<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SyncFilesFromProjectAkhir extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:sync-from-project-akhir {--force : Force sync even if file exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync PDF files from project-akhir storage to hope-ui storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting file sync from project-akhir...');
        
        try {
            // Get all records from database
            $records = DB::table('kkn_pendaftar')
                ->whereNotNull('file_name')
                ->where('file_name', '!=', '')
                ->get();
            
            if ($records->isEmpty()) {
                $this->warn('No file records found in database.');
                return 0;
            }
            
            $this->info("Found {$records->count()} file records in database.");
            
            $synced = 0;
            $skipped = 0;
            $errors = 0;
            
            $progressBar = $this->output->createProgressBar($records->count());
            $progressBar->start();
            
            foreach ($records as $record) {
                $fileName = $record->file_name;
                $filePath = $record->file_path;
                
                // Check if file already exists in hope-ui storage
                if (!$this->option('force') && Storage::disk('public')->exists('kkn-files/' . $fileName)) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }
                
                // Try to copy from project-akhir storage
                $projectAkhirPath = storage_path('../project-akhir/storage/app/public/');
                
                // Try multiple possible paths
                $possiblePaths = [
                    $projectAkhirPath . 'kkn-files/' . $fileName,
                    $projectAkhirPath . $fileName,
                    $projectAkhirPath . $filePath,
                ];
                
                $copied = false;
                foreach ($possiblePaths as $sourcePath) {
                    if (file_exists($sourcePath) && is_readable($sourcePath)) {
                        try {
                            // Copy file to hope-ui storage
                            $destinationPath = 'kkn-files/' . $fileName;
                            Storage::disk('public')->put($destinationPath, file_get_contents($sourcePath));
                            
                            $synced++;
                            $copied = true;
                            break;
                        } catch (\Exception $e) {
                            $this->error("Failed to copy file: $fileName - " . $e->getMessage());
                        }
                    }
                }
                
                if (!$copied) {
                    $errors++;
                    $this->warn("File not found: $fileName");
                }
                
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine();
            
            $this->info("✅ Sync completed!");
            $this->info("📊 Summary:");
            $this->info("   - Synced: $synced files");
            $this->info("   - Skipped: $skipped files (already exist)");
            $this->info("   - Errors: $errors files (not found)");
            
            if ($errors > 0) {
                $this->warn("⚠️  Some files could not be synced. Check the logs for details.");
                return 1;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Sync failed: " . $e->getMessage());
            return 1;
        }
    }
} 