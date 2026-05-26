<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DebugUploadController extends Controller
{
    /**
     * Debug upload file
     */
    public function debugUpload(Request $request)
    {
        try {
            Log::info('DebugUploadController@debugUpload called', [
                'request_all' => $request->all(),
                'has_file' => $request->hasFile('file'),
                'files' => $request->allFiles()
            ]);

            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded',
                    'debug' => [
                        'request_all' => $request->all(),
                        'files' => $request->allFiles()
                    ]
                ], 400);
            }

            $file = $request->file('file');
            
            Log::info('File details', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'error' => $file->getError(),
                'real_path' => $file->getRealPath(),
                'is_valid' => $file->isValid()
            ]);

            // Check storage directory
            $storagePath = storage_path('app/public/kkn-files');
            $publicPath = public_path('storage/kkn-files');
            
            Log::info('Storage paths', [
                'storage_path' => $storagePath,
                'public_path' => $publicPath,
                'storage_exists' => file_exists($storagePath),
                'public_exists' => file_exists($publicPath),
                'storage_writable' => is_writable($storagePath),
                'public_writable' => is_writable($publicPath)
            ]);

            // Create directory if not exists
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
                Log::info('Created storage directory', ['path' => $storagePath]);
            }

            // Try to store file
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('kkn-files', $fileName, 'public');

            Log::info('File storage attempt', [
                'filename' => $fileName,
                'filepath' => $filePath,
                'storage_exists' => Storage::disk('public')->exists($filePath),
                'storage_size' => Storage::disk('public')->exists($filePath) ? Storage::disk('public')->size($filePath) : null
            ]);

            if (Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'data' => [
                        'filename' => $fileName,
                        'filepath' => $filePath,
                        'size' => Storage::disk('public')->size($filePath),
                        'url' => Storage::disk('public')->url($filePath)
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File storage failed',
                    'debug' => [
                        'filename' => $fileName,
                        'filepath' => $filePath,
                        'storage_exists' => Storage::disk('public')->exists($filePath)
                    ]
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('DebugUploadController error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Check storage status
     */
    public function checkStorage()
    {
        try {
            $storagePath = storage_path('app/public/kkn-files');
            $publicPath = public_path('storage/kkn-files');
            
            // Check if storage link exists
            $storageLink = public_path('storage');
            $storageLinkExists = is_link($storageLink);
            $storageLinkTarget = $storageLinkExists ? readlink($storageLink) : null;

            // List files in storage
            $storageFiles = [];
            if (file_exists($storagePath)) {
                $storageFiles = scandir($storagePath);
            }

            // List files in public
            $publicFiles = [];
            if (file_exists($publicPath)) {
                $publicFiles = scandir($publicPath);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'storage_path' => $storagePath,
                    'public_path' => $publicPath,
                    'storage_exists' => file_exists($storagePath),
                    'public_exists' => file_exists($publicPath),
                    'storage_writable' => is_writable($storagePath),
                    'public_writable' => is_writable($publicPath),
                    'storage_link_exists' => $storageLinkExists,
                    'storage_link_target' => $storageLinkTarget,
                    'storage_files' => $storageFiles,
                    'public_files' => $publicFiles,
                    'storage_disk_works' => Storage::disk('public')->exists('test.txt') || Storage::disk('public')->put('test.txt', 'test'),
                    'app_url' => config('app.url'),
                    'filesystem_driver' => config('filesystems.default')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create storage link
     */
    public function createStorageLink()
    {
        try {
            $target = storage_path('app/public');
            $link = public_path('storage');
            
            if (is_link($link)) {
                unlink($link);
            }
            
            symlink($target, $link);
            
            return response()->json([
                'success' => true,
                'message' => 'Storage link created successfully',
                'data' => [
                    'target' => $target,
                    'link' => $link,
                    'link_exists' => is_link($link),
                    'link_target' => readlink($link)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
} 