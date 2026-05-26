<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DebugController extends Controller
{
    public function checkFile($filename = null)
    {
        if (!$filename) {
            $filename = "1753439846_Week 2 - Pengantar PSM.pptx.pdf";
        }

        $results = [
            'target_file' => $filename,
            'database_check' => [],
            'storage_check' => [],
            'storage_disk_check' => []
        ];

        // 1. Check in database
        try {
            $record = DB::table('kkn_pendaftar')
                ->where('file_name', $filename)
                ->orWhere('file_name', 'LIKE', '%' . $filename . '%')
                ->first();
            
            if ($record) {
                $results['database_check'] = [
                    'found' => true,
                    'id' => $record->id,
                    'file_name' => $record->file_name,
                    'file_path' => $record->file_path,
                    'created_at' => $record->created_at
                ];
            } else {
                $results['database_check'] = [
                    'found' => false,
                    'all_records' => DB::table('kkn_pendaftar')->get(['id', 'file_name', 'file_path'])
                ];
            }
        } catch (\Exception $e) {
            $results['database_check'] = [
                'error' => $e->getMessage()
            ];
        }

        // 2. Check in storage directory
        $storagePath = storage_path('app/public/kkn-files');
        if (is_dir($storagePath)) {
            $files = scandir($storagePath);
            $found = false;
            
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    if ($file === $filename) {
                        $filePath = $storagePath . '/' . $file;
                        $results['storage_check'] = [
                            'found' => true,
                            'file' => $file,
                            'size' => filesize($filePath),
                            'path' => $filePath
                        ];
                        $found = true;
                        break;
                    }
                }
            }
            
            if (!$found) {
                $results['storage_check'] = [
                    'found' => false,
                    'all_files' => array_filter($files, function($file) {
                        return $file != '.' && $file != '..';
                    })
                ];
            }
        } else {
            $results['storage_check'] = [
                'error' => 'Storage directory not found: ' . $storagePath
            ];
        }

        // 3. Check via Storage facade
        try {
            $possiblePaths = [
                'kkn-files/' . $filename,
                $filename,
                'public/kkn-files/' . $filename
            ];
            
            foreach ($possiblePaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    $results['storage_disk_check'] = [
                        'found' => true,
                        'path' => $path,
                        'size' => Storage::disk('public')->size($path)
                    ];
                    break;
                }
            }
            
            if (!isset($results['storage_disk_check']['found'])) {
                $results['storage_disk_check'] = [
                    'found' => false,
                    'checked_paths' => $possiblePaths
                ];
            }
        } catch (\Exception $e) {
            $results['storage_disk_check'] = [
                'error' => $e->getMessage()
            ];
        }

        return response()->json($results);
    }
} 