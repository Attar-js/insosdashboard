<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeerReview;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\DashboardHelper;

class PeerReviewController extends Controller
{
    /**
     * Menampilkan halaman peer review
     */
    public function index()
    {
        $peerReviews = PeerReview::orderBy('created_at', 'desc')->get();
        
        // Transform data untuk API
        $transformedData = $peerReviews->map(function ($item) {
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
        return view('special-pages.peer-review', compact('peerReviews'));
    }

    /**
     * Redirect to dashboard with success message
     */
    public function redirectToDashboard($message = '')
    {
        $dashboardUrl = DashboardHelper::getDashboardUrl('special-pages/peer-review');
        
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
            'peer_review_url' => DashboardHelper::getPeerReviewUrl(),
            'file_manager_url' => DashboardHelper::getFileManagerUrl(),
            'app_name' => config('app.name'),
            'app_url' => config('app.url')
        ]);
    }

    /**
     * Menyimpan data peer review baru
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
            'file.required' => 'File peer review harus diupload',
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
            
            // Simpan file ke storage peer-review-files
            Storage::disk('public')->put('peer-review-files/' . $fileName, file_get_contents($file));
            
            // Simpan data ke database
            PeerReview::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $fileName,
                'file_path' => 'peer-review-files/' . $fileName,
                'user_nim' => $request->user_nim,
                'status' => 'pending',
                'catatan' => null,
            ]);

            return redirect()->route('special-pages.peer-review')->with('success', 'Peer Review berhasil ditambahkan!');
        } catch (\Exception $e) {
            \Log::error('Error storing peer review: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan peer review: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan detail peer review
     */
    public function show($id)
    {
        $peerReview = PeerReview::findOrFail($id);
        return response()->json($peerReview);
    }

    /**
     * Update data peer review
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
            $peerReview = PeerReview::findOrFail($id);
            
            $peerReview->judul_kegiatan = $request->judul_kegiatan;
            $peerReview->user_nim = $request->user_nim;
            
            // Update file jika ada
            if ($request->hasFile('file')) {
                // Hapus file lama
                if ($peerReview->file_path && Storage::disk('public')->exists($peerReview->file_path)) {
                    Storage::disk('public')->delete($peerReview->file_path);
                }
                
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                // Simpan file baru
                Storage::disk('public')->put('peer-review-files/' . $fileName, file_get_contents($file));
                
                $peerReview->file_name = $fileName;
                $peerReview->file_path = 'peer-review-files/' . $fileName;
            }
            
            $peerReview->save();

            return redirect()->route('special-pages.peer-review')->with('success', 'Peer Review berhasil diupdate!');
        } catch (\Exception $e) {
            \Log::error('Error updating peer review: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengupdate peer review: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Verifikasi peer review
     */
    public function verifikasi(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'catatan' => 'nullable|string|max:1000'
        ]);

        try {
            $peerReview = PeerReview::findOrFail($id);
            $peerReview->status = $request->status;
            $peerReview->catatan = $request->catatan;
            $peerReview->save();

            return redirect()->route('special-pages.peer-review')->with('success', 'Status peer review berhasil diupdate!');
        } catch (\Exception $e) {
            \Log::error('Error updating peer review status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate status: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data peer review
     */
    public function destroy($id)
    {
        try {
            $peerReview = PeerReview::findOrFail($id);
            
            // Hapus file dari storage
            if ($peerReview->file_path && Storage::disk('public')->exists($peerReview->file_path)) {
                Storage::disk('public')->delete($peerReview->file_path);
            }
            
            $peerReview->delete();

            return redirect()->route('special-pages.peer-review')->with('success', 'Peer Review berhasil dihapus!');
        } catch (\Exception $e) {
            \Log::error('Error deleting peer review: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus peer review: ' . $e->getMessage());
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
            Storage::disk('public')->put('peer-review-files/' . $request->file_name, $fileContent);
            
            // Simpan data ke database
            PeerReview::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $request->file_name,
                'file_path' => 'peer-review-files/' . $request->file_name,
                'user_nim' => $request->user_nim,
                'status' => 'pending',
                'catatan' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Peer Review berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error storing peer review from external: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
