<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanAkhir;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\DashboardHelper;

class LaporanAkhirController extends Controller
{
    /**
     * Menampilkan halaman laporan akhir
     */
    public function index()
    {
        $laporanAkhir = LaporanAkhir::orderBy('created_at', 'desc')->get();
        
        // Transform data untuk API
        $transformedData = $laporanAkhir->map(function ($item) {
            return [
                'id' => $item->id,
                'judul_kegiatan' => $item->judul_kegiatan,
                'user_nim' => $item->user_nim,
                'status' => $item->status,
                'catatan' => $item->catatan,
                'file_name' => $item->file_name,
                'file_path' => $item->file_path,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at
            ];
        });
        
        // Check if request wants JSON (API call)
        if (request()->expectsJson()) {
            return response()->json($transformedData);
        }
        
        // Return view for web request
        return view('special-pages.laporan-akhir', compact('laporanAkhir'));
    }

    /**
     * Redirect to dashboard with success message
     */
    public function redirectToDashboard($message = '')
    {
        $dashboardUrl = DashboardHelper::getDashboardUrl('special-pages/laporan-akhir');
        
        if ($message) {
            return redirect($dashboardUrl)->with('success', $message);
        }
        
        return redirect($dashboardUrl);
    }

    /**
     * Get dashboard configuration
     */
    public function getDashboardConfig()
    {
        return response()->json([
            'dashboard_url' => DashboardHelper::getDashboardUrl(),
            'laporan_akhir_url' => DashboardHelper::getLaporanAkhirUrl(),
            'file_manager_url' => DashboardHelper::getFileManagerUrl(),
            'app_name' => config('app.name'),
            'app_url' => config('app.url')
        ]);
    }

    /**
     * Menyimpan data laporan akhir baru
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'user_nim' => 'required|string|max:20',
            'file' => 'required|file|mimes:pdf|max:10240' // 10MB max
        ], [
            'judul_kegiatan.required' => 'Judul kegiatan harus diisi',
            'user_nim.required' => 'NIM user input harus diisi',
            'file.required' => 'File laporan akhir harus diupload',
            'file.mimes' => 'File harus berformat PDF',
            'file.max' => 'Ukuran file maksimal 10MB'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Simpan file ke storage laporan-akhir-files
            Storage::disk('public')->put('laporan-akhir-files/' . $fileName, file_get_contents($file));
            
            // Simpan data ke database
            LaporanAkhir::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $fileName,
                'file_path' => 'laporan-akhir-files/' . $fileName,
                'user_nim' => $request->user_nim,
                'status' => 'pending'
            ]);

            return redirect()->back()->with('success', 'Laporan akhir berhasil ditambahkan!');
        } catch (\Exception $e) {
            \Log::error('Error storing laporan akhir: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan laporan akhir: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan detail laporan akhir
     */
    public function show($id)
    {
        $laporanAkhir = LaporanAkhir::findOrFail($id);
        return view('special-pages.laporan-akhir', compact('laporanAkhir'));
    }

    /**
     * Update data laporan akhir
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'user_nim' => 'required|string|max:20',
            'file' => 'nullable|file|mimes:pdf|max:10240'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $laporanAkhir = LaporanAkhir::findOrFail($id);
            $laporanAkhir->judul_kegiatan = $request->judul_kegiatan;
            $laporanAkhir->user_nim = $request->user_nim;

            if ($request->hasFile('file')) {
                // Hapus file lama jika ada
                if ($laporanAkhir->file_name) {
                    Storage::disk('public')->delete('laporan-akhir-files/' . $laporanAkhir->file_name);
                }
                
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                Storage::disk('public')->put('laporan-akhir-files/' . $fileName, file_get_contents($file));
                $laporanAkhir->file_name = $fileName;
            }

            $laporanAkhir->save();
            return redirect()->back()->with('success', 'Laporan akhir berhasil diupdate!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate laporan akhir: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Verifikasi laporan akhir
     */
    public function verifikasi(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected',
            'catatan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $laporanAkhir = LaporanAkhir::findOrFail($id);
            $laporanAkhir->status = $request->status;
            $laporanAkhir->catatan = $request->catatan;
            $laporanAkhir->save();

            $statusText = $request->status == 'approved' ? 'disetujui' : 
                         ($request->status == 'rejected' ? 'ditolak' : 'pending');

            return redirect()->back()->with('success', "Laporan akhir berhasil $statusText!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate status laporan akhir: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hapus laporan akhir
     */
    public function destroy($id)
    {
        try {
            $laporanAkhir = LaporanAkhir::findOrFail($id);
            
            // Hapus file dari storage
            if ($laporanAkhir->file_name) {
                Storage::disk('public')->delete('laporan-akhir-files/' . $laporanAkhir->file_name);
            }
            
            $laporanAkhir->delete();
            
            // Redirect ke dashboard dengan pesan sukses
            return redirect()->route('special-pages.laporan-akhir')->with('success', 'Laporan akhir berhasil dihapus!');
            
        } catch (\Exception $e) {
            return redirect()->route('special-pages.laporan-akhir')->with('error', 'Gagal menghapus laporan akhir: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan data laporan akhir dari external (project-akhir)
     */
    public function storeFromExternal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'user_nim' => 'required|string|max:20',
            'file_content' => 'required|string',
            'file_name' => 'required|string',
            'file_mime_type' => 'required|string',
            'file_size' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Decode base64 content
            $fileContent = base64_decode($request->file_content);
            
            // Create directory if not exists
            $storagePath = storage_path('app/public/laporan-akhir-files');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            // Save file to storage
            $filePath = 'laporan-akhir-files/' . $request->file_name;
            Storage::disk('public')->put($filePath, $fileContent);

            // Save to database with file content
            $laporanAkhir = LaporanAkhir::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $request->file_name,
                'file_path' => $filePath,
                'file_content' => $request->file_content, // Store base64 content
                'file_mime_type' => $request->file_mime_type,
                'file_size' => $request->file_size,
                'user_nim' => $request->user_nim,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan akhir berhasil disimpan!',
                'data' => $laporanAkhir
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
