<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormKesediaan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\DashboardHelper;

class FormKesediaanController extends Controller
{
    /**
     * Menampilkan halaman form kesediaan
     */
    public function index()
    {
        $formKesediaan = FormKesediaan::orderBy('created_at', 'desc')->get();
        
        // Transform data untuk API
        $transformedData = $formKesediaan->map(function ($item) {
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
        return view('special-pages.form-kesediaan', compact('formKesediaan'));
    }

    /**
     * Redirect to dashboard with success message
     */
    public function redirectToDashboard($message = '')
    {
        $dashboardUrl = DashboardHelper::getDashboardUrl('special-pages/form-kesediaan');
        
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
            'form_kesediaan_url' => DashboardHelper::getFormKesediaanUrl(),
            'file_manager_url' => DashboardHelper::getFileManagerUrl(),
            'app_name' => config('app.name'),
            'app_url' => config('app.url')
        ]);
    }

    /**
     * Menyimpan data form kesediaan baru
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
            'file.required' => 'File form kesediaan harus diupload',
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
            
            // Simpan file ke storage form-kesediaan-files
            Storage::disk('public')->put('form-kesediaan-files/' . $fileName, file_get_contents($file));
            
            // Simpan data ke database
            FormKesediaan::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $fileName,
                'file_path' => 'form-kesediaan-files/' . $fileName,
                'user_nim' => $request->user_nim,
                'status' => 'pending',
                'catatan' => null,
            ]);

            return redirect()->route('special-pages.form-kesediaan')->with('success', 'Form Kesediaan berhasil ditambahkan!');
        } catch (\Exception $e) {
            \Log::error('Error storing form kesediaan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan form kesediaan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan detail form kesediaan
     */
    public function show($id)
    {
        $formKesediaan = FormKesediaan::findOrFail($id);
        return response()->json($formKesediaan);
    }

    /**
     * Update data form kesediaan
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'user_nim' => 'required|string|max:20',
            'file' => 'nullable|file|mimes:pdf|max:10240' // 10MB max
        ], [
            'judul_kegiatan.required' => 'Judul kegiatan harus diisi',
            'user_nim.required' => 'NIM user input harus diisi',
            'file.mimes' => 'File harus berformat PDF',
            'file.max' => 'Ukuran file maksimal 10MB'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $formKesediaan = FormKesediaan::findOrFail($id);
            
            $formKesediaan->judul_kegiatan = $request->judul_kegiatan;
            $formKesediaan->user_nim = $request->user_nim;
            
            // Update file jika ada
            if ($request->hasFile('file')) {
                // Hapus file lama
                if ($formKesediaan->file_path && Storage::disk('public')->exists($formKesediaan->file_path)) {
                    Storage::disk('public')->delete($formKesediaan->file_path);
                }
                
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                // Simpan file baru
                Storage::disk('public')->put('form-kesediaan-files/' . $fileName, file_get_contents($file));
                
                $formKesediaan->file_name = $fileName;
                $formKesediaan->file_path = 'form-kesediaan-files/' . $fileName;
            }
            
            $formKesediaan->save();

            return redirect()->route('special-pages.form-kesediaan')->with('success', 'Form Kesediaan berhasil diupdate!');
        } catch (\Exception $e) {
            \Log::error('Error updating form kesediaan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengupdate form kesediaan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Verifikasi form kesediaan
     */
    public function verifikasi(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'catatan' => 'nullable|string|max:1000'
        ]);

        try {
            $formKesediaan = FormKesediaan::findOrFail($id);
            $formKesediaan->status = $request->status;
            $formKesediaan->catatan = $request->catatan;
            $formKesediaan->save();

            return redirect()->route('special-pages.form-kesediaan')->with('success', 'Status form kesediaan berhasil diupdate!');
        } catch (\Exception $e) {
            \Log::error('Error updating form kesediaan status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate status: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data form kesediaan
     */
    public function destroy($id)
    {
        try {
            $formKesediaan = FormKesediaan::findOrFail($id);
            
            // Hapus file dari storage
            if ($formKesediaan->file_path && Storage::disk('public')->exists($formKesediaan->file_path)) {
                Storage::disk('public')->delete($formKesediaan->file_path);
            }
            
            $formKesediaan->delete();

            return redirect()->route('special-pages.form-kesediaan')->with('success', 'Form Kesediaan berhasil dihapus!');
        } catch (\Exception $e) {
            \Log::error('Error deleting form kesediaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus form kesediaan: ' . $e->getMessage());
        }
    }

    /**
     * API untuk menerima data dari project-akhir
     */
    public function storeFromExternal(Request $request)
    {
        try {
            $request->validate([
                'judul_kegiatan' => 'required|string|max:255',
                'user_nim' => 'required|string|max:20',
                'file_content' => 'required|string',
                'file_name' => 'required|string|max:255',
                'file_mime_type' => 'required|string',
                'file_size' => 'required|integer'
            ]);

            // Decode base64 file content
            $fileContent = base64_decode($request->file_content);
            
            // Simpan file ke storage
            Storage::disk('public')->put('form-kesediaan-files/' . $request->file_name, $fileContent);
            
            // Simpan data ke database
            FormKesediaan::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $request->file_name,
                'file_path' => 'form-kesediaan-files/' . $request->file_name,
                'user_nim' => $request->user_nim,
                'status' => 'pending',
                'catatan' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Form Kesediaan berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error storing form kesediaan from external: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
