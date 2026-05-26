<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class FileController extends Controller
{
    /**
     * Menampilkan file PDF dengan proper headers
     */
    public function showPdf($filename)
    {
        // Debug: Log untuk tracking
        \Log::info('FileController@showPdf called', [
            'filename' => $filename,
            'request_url' => request()->url()
        ]);
        
        // Method 1: Try direct file access first
        $directPath = storage_path('app/public/kkn-files/' . $filename);
        if (file_exists($directPath) && is_readable($directPath)) {
            \Log::info('File found via direct path', ['path' => $directPath]);
            
            return response()->file($directPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }
        
        // Method 2: Try database lookup
        $dbRecord = DB::table('kkn_pendaftar')
            ->where('file_name', $filename)
            ->orWhere('file_name', 'LIKE', '%' . $filename . '%')
            ->first();
        
        if ($dbRecord && $dbRecord->file_path) {
            \Log::info('File found in database', ['db_record' => $dbRecord]);
            
            // Try the path from database
            $dbPath = storage_path('app/public/' . $dbRecord->file_path);
            if (file_exists($dbPath) && is_readable($dbPath)) {
                \Log::info('File found via database path', ['path' => $dbPath]);
                
                return response()->file($dbPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                    'Cache-Control' => 'public, max-age=3600',
                ]);
            }
        }
        
        // Method 3: Try Storage facade with multiple paths
        $possiblePaths = [
            'kkn-files/' . $filename,
            $filename,
            'public/kkn-files/' . $filename
        ];
        
        foreach ($possiblePaths as $path) {
            \Log::info('Checking storage path', ['path' => $path]);
            
            if (Storage::disk('public')->exists($path)) {
                $file = Storage::disk('public')->get($path);
                $type = Storage::disk('public')->mimeType($path) ?: 'application/pdf';
                
                \Log::info('File found via storage', [
                    'path' => $path,
                    'size' => Storage::disk('public')->size($path),
                    'type' => $type
                ]);
                
                return Response::make($file, 200, [
                    'Content-Type' => $type,
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                    'Cache-Control' => 'public, max-age=3600',
                ]);
            }
        }
        
        // Method 4: Try public storage link
        $publicPath = public_path('storage/kkn-files/' . $filename);
        if (file_exists($publicPath) && is_readable($publicPath)) {
            \Log::info('File found via public path', ['path' => $publicPath]);
            
            return response()->file($publicPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }
        
        // If all methods fail, return error
        \Log::error('PDF file not found after trying all methods', [
            'filename' => $filename,
            'direct_path' => $directPath,
            'public_path' => $publicPath,
            'storage_files' => Storage::disk('public')->files('kkn-files'),
            'all_files' => Storage::disk('public')->allFiles()
        ]);
        
        return response()->view('errors.file-not-found', [
            'filename' => $filename,
            'message' => 'File PDF tidak ditemukan di server. Silakan cek kembali file upload.',
            'debug_info' => [
                'direct_path' => $directPath,
                'public_path' => $publicPath,
                'storage_files' => Storage::disk('public')->files('kkn-files')
            ]
        ], 404);
    }

    /**
     * Download file PDF
     */
    public function downloadPdf($filename)
    {
        // Debug: Log untuk tracking
        \Log::info('FileController@downloadPdf called', [
            'filename' => $filename
        ]);
        
        // Try multiple paths
        $possiblePaths = [
            'kkn-files/' . $filename,
            $filename,
            'public/kkn-files/' . $filename
        ];
        
        $filePath = null;
        $file = null;
        $type = null;
        
        foreach ($possiblePaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                $filePath = $path;
                $file = Storage::disk('public')->get($path);
                $type = Storage::disk('public')->mimeType($path);
                break;
            }
        }
        
        if (!$filePath || !$file) {
            \Log::error('PDF file not found for download', [
                'filename' => $filename,
                'searched_paths' => $possiblePaths
            ]);
            return response()->view('errors.file-not-found', [
                'filename' => $filename,
                'message' => 'File PDF tidak ditemukan untuk download.'
            ], 404);
        }
        
        return Response::make($file, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Check file status (for debugging)
     */
    public function checkFile($filename)
    {
        $results = [
            'filename' => $filename,
            'in_database' => false,
            'in_storage' => false,
            'database_record' => null,
            'storage_path' => null,
            'storage_size' => null,
            'debug_info' => []
        ];

        // Check database
        try {
            $record = DB::table('kkn_pendaftar')
                ->where('file_name', $filename)
                ->orWhere('file_name', 'LIKE', '%' . $filename . '%')
                ->first();
            
            if ($record) {
                $results['in_database'] = true;
                $results['database_record'] = $record;
            }
        } catch (\Exception $e) {
            $results['database_error'] = $e->getMessage();
        }

        // Check storage
        $possiblePaths = [
            'kkn-files/' . $filename,
            $filename,
            'public/kkn-files/' . $filename
        ];
        
        foreach ($possiblePaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                $results['in_storage'] = true;
                $results['storage_path'] = $path;
                $results['storage_size'] = Storage::disk('public')->size($path);
                break;
            }
        }

        // Add debug info
        $results['debug_info'] = [
            'storage_files' => Storage::disk('public')->files('kkn-files'),
            'all_files' => Storage::disk('public')->allFiles(),
            'storage_exists' => Storage::disk('public')->exists('kkn-files'),
            'public_storage_link' => is_link(public_path('storage'))
        ];

        return response()->json($results);
    }

    /**
     * List all files in storage (for debugging)
     */
    public function listFiles()
    {
        $files = Storage::disk('public')->allFiles();
        $kknFiles = Storage::disk('public')->files('kkn-files');
        
        return response()->json([
            'all_files' => $files,
            'kkn_files' => $kknFiles,
            'storage_path' => storage_path('app/public'),
            'public_storage_path' => public_path('storage'),
            'storage_link_exists' => is_link(public_path('storage'))
        ]);
    }

    /**
     * Fix missing files by copying from project-akhir
     */
    public function fixMissingFiles()
    {
        $results = [
            'fixed' => [],
            'errors' => [],
            'not_found' => []
        ];

        // Get all records from database
        $records = DB::table('kkn_pendaftar')->get(['id', 'file_name', 'file_path']);

        foreach ($records as $record) {
            // Check if file exists in hope-ui storage
            if (!Storage::disk('public')->exists($record->file_path)) {
                // Try to find file in project-akhir storage
                $projectAkhirPath = storage_path('../project-akhir/storage/app/public/kkn-files/' . $record->file_name);
                
                if (file_exists($projectAkhirPath)) {
                    try {
                        // Copy file from project-akhir to hope-ui
                        $content = file_get_contents($projectAkhirPath);
                        Storage::disk('public')->put($record->file_path, $content);
                        
                        $results['fixed'][] = [
                            'id' => $record->id,
                            'file_name' => $record->file_name,
                            'source' => $projectAkhirPath,
                            'destination' => $record->file_path
                        ];
                    } catch (\Exception $e) {
                        $results['errors'][] = [
                            'id' => $record->id,
                            'file_name' => $record->file_name,
                            'error' => $e->getMessage()
                        ];
                    }
                } else {
                    $results['not_found'][] = [
                        'id' => $record->id,
                        'file_name' => $record->file_name,
                        'searched_path' => $projectAkhirPath
                    ];
                }
            }
        }

        return response()->json($results);
    }

    /**
     * Create sample PDF file for testing
     */
    public function createSampleFile()
    {
        $filename = "1753442349_FP_Tugas Besar_Keamanan Sistem Informasi_Kelompok A1.pdf";
        $filePath = 'kkn-files/' . $filename;
        
        // Create a simple PDF content (this is just for testing)
        $pdfContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n>>\nendobj\n4 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 12 Tf\n72 720 Td\n(Sample PDF File) Tj\nET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000204 00000 n \ntrailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n297\n%%EOF";
        
        try {
            Storage::disk('public')->put($filePath, $pdfContent);
            
            // Update database record if exists
            $record = DB::table('kkn_pendaftar')->where('file_name', $filename)->first();
            if ($record) {
                DB::table('kkn_pendaftar')
                    ->where('id', $record->id)
                    ->update(['file_path' => $filePath]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Sample file created successfully',
                'file_path' => $filePath,
                'file_size' => strlen($pdfContent)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sample file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync files from project-akhir to hope-ui storage
     */
    public function syncFromProjectAkhir()
    {
        try {
            \Log::info('Starting file sync from project-akhir');
            
            // Get all records from database
            $records = DB::table('kkn_pendaftar')
                ->whereNotNull('file_name')
                ->where('file_name', '!=', '')
                ->get();
            
            $synced = 0;
            $errors = [];
            
            foreach ($records as $record) {
                $fileName = $record->file_name;
                $filePath = $record->file_path;
                
                // Check if file already exists in hope-ui storage
                if (Storage::disk('public')->exists('kkn-files/' . $fileName)) {
                    \Log::info('File already exists in hope-ui: ' . $fileName);
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
                            
                            \Log::info('File synced successfully', [
                                'filename' => $fileName,
                                'source' => $sourcePath,
                                'destination' => $destinationPath,
                                'size' => Storage::disk('public')->size($destinationPath)
                            ]);
                            
                            $synced++;
                            $copied = true;
                            break;
                        } catch (\Exception $e) {
                            \Log::error('Failed to copy file', [
                                'filename' => $fileName,
                                'source' => $sourcePath,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
                
                if (!$copied) {
                    $errors[] = "File not found: $fileName";
                    \Log::warning('File not found in project-akhir', [
                        'filename' => $fileName,
                        'searched_paths' => $possiblePaths
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Sync completed. Synced: $synced, Errors: " . count($errors),
                'synced_count' => $synced,
                'error_count' => count($errors),
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            \Log::error('File sync failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check file status in both database and storage
     */
    public function checkFileStatus()
    {
        try {
            $records = DB::table('kkn_pendaftar')
                ->whereNotNull('file_name')
                ->where('file_name', '!=', '')
                ->get();
            
            $status = [];
            
            foreach ($records as $record) {
                $fileName = $record->file_name;
                $inStorage = Storage::disk('public')->exists('kkn-files/' . $fileName);
                
                $status[] = [
                    'id' => $record->id,
                    'file_name' => $fileName,
                    'file_path' => $record->file_path,
                    'in_database' => true,
                    'in_storage' => $inStorage,
                    'storage_size' => $inStorage ? Storage::disk('public')->size('kkn-files/' . $fileName) : null
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $status,
                'total_records' => count($status),
                'files_in_storage' => collect($status)->where('in_storage', true)->count(),
                'files_missing' => collect($status)->where('in_storage', false)->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show PDF file directly from database BLOB
     */
    public function showPdfFromDatabase($filename)
    {
        \Log::info('FileController@showPdfFromDatabase called', [
            'filename' => $filename,
            'request_url' => request()->url()
        ]);
        
        // Get file from database
        $dbRecord = DB::table('kkn_pendaftar')
            ->where('file_name', $filename)
            ->orWhere('file_name', 'LIKE', '%' . $filename . '%')
            ->first();
        
        if (!$dbRecord) {
            \Log::error('File not found in database', ['filename' => $filename]);
            return response()->view('errors.file-not-found', [
                'filename' => $filename,
                'message' => 'File tidak ditemukan di database.'
            ], 404);
        }
        
        // Check if file content exists in database
        if (!$dbRecord->file_content) {
            \Log::error('File content not found in database', [
                'filename' => $filename,
                'record_id' => $dbRecord->id
            ]);
            
            // Fallback to storage if no BLOB content
            return $this->showPdfWithAutoSync($filename);
        }
        
        \Log::info('File found in database BLOB', [
            'filename' => $filename,
            'record_id' => $dbRecord->id,
            'file_size' => $dbRecord->file_size,
            'mime_type' => $dbRecord->file_mime_type
        ]);
        
        // Return file content directly from database
        return response($dbRecord->file_content, 200, [
            'Content-Type' => $dbRecord->file_mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Content-Length' => $dbRecord->file_size,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Download PDF file directly from database BLOB
     */
    public function downloadPdfFromDatabase($filename)
    {
        \Log::info('FileController@downloadPdfFromDatabase called', ['filename' => $filename]);
        
        // Get file from database
        $dbRecord = DB::table('kkn_pendaftar')
            ->where('file_name', $filename)
            ->orWhere('file_name', 'LIKE', '%' . $filename . '%')
            ->first();
        
        if (!$dbRecord) {
            return response()->json(['error' => 'File not found in database'], 404);
        }
        
        // Check if file content exists in database
        if (!$dbRecord->file_content) {
            // Fallback to storage if no BLOB content
            return $this->downloadPdfFromShared($filename);
        }
        
        // Return file content for download
        return response($dbRecord->file_content, 200, [
            'Content-Type' => $dbRecord->file_mime_type ?: 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => $dbRecord->file_size,
        ]);
    }

    /**
     * Check file status in database BLOB
     */
    public function checkFileInDatabase($filename)
    {
        try {
            // Check database
            $dbRecord = DB::table('kkn_pendaftar')
                ->where('file_name', $filename)
                ->orWhere('file_name', 'LIKE', '%' . $filename . '%')
                ->first();
            
            if (!$dbRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found in database',
                    'in_database' => false,
                    'has_blob_content' => false,
                    'in_storage' => false
                ]);
            }
            
            // Check if BLOB content exists
            $hasBlobContent = !empty($dbRecord->file_content);
            
            // Check storage as fallback
            $inStorage = Storage::disk('public')->exists('kkn-files/' . $filename);
            
            return response()->json([
                'success' => true,
                'in_database' => true,
                'has_blob_content' => $hasBlobContent,
                'in_storage' => $inStorage,
                'database_record' => [
                    'id' => $dbRecord->id,
                    'file_name' => $dbRecord->file_name,
                    'file_size' => $dbRecord->file_size,
                    'file_mime_type' => $dbRecord->file_mime_type,
                    'blob_size' => $hasBlobContent ? strlen($dbRecord->file_content) : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync file from storage to database BLOB
     */
    public function syncFileToDatabase($filename)
    {
        \Log::info('FileController@syncFileToDatabase called', ['filename' => $filename]);
        
        try {
            // Get database record
            $dbRecord = DB::table('kkn_pendaftar')
                ->where('file_name', $filename)
                ->orWhere('file_name', 'LIKE', '%' . $filename . '%')
                ->first();
            
            if (!$dbRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found in database'
                ], 404);
            }
            
            // Check if already has BLOB content
            if (!empty($dbRecord->file_content)) {
                return response()->json([
                    'success' => true,
                    'message' => 'File already has BLOB content',
                    'synced' => false
                ]);
            }
            
            // Try to get file from project-akhir storage
            $projectAkhirPath = storage_path('../project-akhir/storage/app/public/');
            $possiblePaths = [
                $projectAkhirPath . 'kkn-files/' . $filename,
                $projectAkhirPath . $filename,
                $projectAkhirPath . $dbRecord->file_path,
            ];
            
            $fileContent = null;
            $fileSize = null;
            $mimeType = null;
            
            foreach ($possiblePaths as $filePath) {
                if (file_exists($filePath) && is_readable($filePath)) {
                    $fileContent = file_get_contents($filePath);
                    $fileSize = filesize($filePath);
                    $mimeType = mime_content_type($filePath) ?: 'application/pdf';
                    break;
                }
            }
            
            // Try hope-ui storage as fallback
            if (!$fileContent && Storage::disk('public')->exists('kkn-files/' . $filename)) {
                $fileContent = Storage::disk('public')->get('kkn-files/' . $filename);
                $fileSize = Storage::disk('public')->size('kkn-files/' . $filename);
                $mimeType = Storage::disk('public')->mimeType('kkn-files/' . $filename) ?: 'application/pdf';
            }
            
            if (!$fileContent) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found in any storage',
                    'searched_paths' => $possiblePaths
                ], 404);
            }
            
            // Update database with BLOB content
            DB::table('kkn_pendaftar')
                ->where('id', $dbRecord->id)
                ->update([
                    'file_content' => $fileContent,
                    'file_mime_type' => $mimeType,
                    'file_size' => $fileSize
                ]);
            
            \Log::info('File synced to database BLOB', [
                'filename' => $filename,
                'record_id' => $dbRecord->id,
                'file_size' => $fileSize,
                'mime_type' => $mimeType
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'File synced to database BLOB successfully',
                'synced' => true,
                'file_size' => $fileSize,
                'mime_type' => $mimeType
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Sync to database failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show PDF from hope-ui storage (simplified method for direct upload)
     */
    public function showPdfFromHopeUi($filename)
    {
        try {
            $filePath = 'kkn-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                \Log::error('File not found in hope-ui storage', [
                    'filename' => $filename,
                    'filepath' => $filePath
                ]);
                
                return view('errors.file-not-found', [
                    'filename' => $filename,
                    'message' => 'File tidak ditemukan di storage hope-ui. File langsung disimpan di hope-ui dari form upload.'
                ]);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error showing PDF from hope-ui storage', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            return view('errors.file-not-found', [
                'filename' => $filename,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download PDF from hope-ui storage (simplified method for direct upload)
     */
    public function downloadPdfFromHopeUi($filename)
    {
        try {
            $filePath = 'kkn-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan di storage hope-ui'
                ], 404);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show PDF from luaran storage (for luaran files)
     */
    public function showPdfFromLuaran($filename)
    {
        try {
            $filePath = 'luaran-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                \Log::error('File not found in luaran storage', [
                    'filename' => $filename,
                    'filepath' => $filePath
                ]);
                
                return view('errors.file-not-found', [
                    'filename' => $filename,
                    'message' => 'File tidak ditemukan di storage luaran. File langsung disimpan di hope-ui dari form upload.'
                ]);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error showing PDF from luaran storage', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            return view('errors.file-not-found', [
                'filename' => $filename,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download PDF from luaran storage (for luaran files)
     */
    public function downloadPdfFromLuaran($filename)
    {
        try {
            $filePath = 'luaran-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan di storage luaran'
                ], 404);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show PDF from proposal storage (for proposal files)
     */
    public function showPdfFromProposal($filename)
    {
        try {
            $filePath = 'proposal-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                \Log::error('File not found in proposal storage', [
                    'filename' => $filename,
                    'filepath' => $filePath
                ]);
                
                return view('errors.file-not-found', [
                    'filename' => $filename,
                    'message' => 'File tidak ditemukan di storage proposal. File langsung disimpan di hope-ui dari form upload.'
                ]);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error showing PDF from proposal storage', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            return view('errors.file-not-found', [
                'filename' => $filename,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download PDF from proposal storage (for proposal files)
     */
    public function downloadPdfFromProposal($filename)
    {
        try {
            $filePath = 'proposal-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan di storage proposal'
                ], 404);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show PDF from laporan akhir storage (for laporan akhir files)
     */
    public function showPdfFromLaporanAkhir($filename)
    {
        try {
            $filePath = 'laporan-akhir-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                \Log::error('File not found in laporan akhir storage', [
                    'filename' => $filename,
                    'filepath' => $filePath
                ]);
                
                return view('errors.file-not-found', [
                    'filename' => $filename,
                    'message' => 'File tidak ditemukan di storage laporan akhir. File langsung disimpan di hope-ui dari form upload.'
                ]);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error showing PDF from laporan akhir storage', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            return view('errors.file-not-found', [
                'filename' => $filename,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download PDF from laporan akhir storage (for laporan akhir files)
     */
    public function downloadPdfFromLaporanAkhir($filename)
    {
        try {
            $filePath = 'laporan-akhir-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan di storage laporan akhir'
                ], 404);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show PDF from peer review storage (for peer review files)
     */
    public function showPdfFromPeerReview($filename)
    {
        try {
            $filePath = 'peer-review-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                \Log::error('File not found in peer review storage', [
                    'filename' => $filename,
                    'filepath' => $filePath
                ]);
                
                return view('errors.file-not-found', [
                    'filename' => $filename,
                    'message' => 'File tidak ditemukan di storage peer review. File langsung disimpan di hope-ui dari form upload.'
                ]);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error showing PDF from peer review storage', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            return view('errors.file-not-found', [
                'filename' => $filename,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download PDF from peer review storage (for peer review files)
     */
    public function downloadPdfFromPeerReview($filename)
    {
        try {
            $filePath = 'peer-review-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan di storage peer review'
                ], 404);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show PDF from form kesediaan storage (for form kesediaan files)
     */
    public function showPdfFromFormKesediaan($filename)
    {
        try {
            $filePath = 'form-kesediaan-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                \Log::error('File not found in form kesediaan storage', [
                    'filename' => $filename,
                    'filepath' => $filePath
                ]);
                
                return view('errors.file-not-found', [
                    'filename' => $filename,
                    'message' => 'File tidak ditemukan di storage form kesediaan. File langsung disimpan di hope-ui dari form upload.'
                ]);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error showing PDF from form kesediaan storage', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            return view('errors.file-not-found', [
                'filename' => $filename,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download PDF from form kesediaan storage (for form kesediaan files)
     */
    public function downloadPdfFromFormKesediaan($filename)
    {
        try {
            $filePath = 'form-kesediaan-files/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan di storage form kesediaan'
                ], 404);
            }
            
            $file = Storage::disk('public')->get($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: 'application/pdf';
            
            return response($file, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }


}

