<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KknPendaftar;
use App\Models\KknAnggota;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\DashboardHelper;

class PendaftarController extends Controller
{
    /**
     * Menampilkan halaman pendaftar
     */
    public function index()
    {
        // Debug: Log untuk melihat data yang diambil
        \Log::info('PendaftarController@index called');
        
        $pendaftar = KknPendaftar::with('anggota')
            ->orderBy('created_at', 'desc')
            ->get();

        // Debug: Log jumlah data yang ditemukan
        \Log::info('Pendaftar data found', [
            'count' => $pendaftar->count(),
            'data' => $pendaftar->map(function($item) {
                return [
                    'id' => $item->id,
                    'file_name' => $item->file_name,
                    'file_path' => $item->file_path,
                    'created_at' => $item->created_at
                ];
            })
        ]);

        return view('special-pages.pendaftar', compact('pendaftar'));
    }

    /**
     * Redirect to dashboard with success message
     */
    public function redirectToDashboard($message = '')
    {
        $dashboardUrl = DashboardHelper::getDashboardUrl('special-pages/pendaftar');
        
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
            'pendaftar_url' => DashboardHelper::getPendaftarUrl(),
            'file_manager_url' => DashboardHelper::getFileManagerUrl(),
            'app_name' => config('app.name'),
            'app_url' => config('app.url')
        ]);
    }

    /**
     * Menyimpan data pendaftar baru
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'mitra' => 'required|string|max:100',
            'lokasi_mitra' => 'required|string|max:255',
            'user_nim' => 'required|string|max:20',
            'nama' => 'required|array|min:1',
            'nama.*' => 'required|string|max:100',
            'nim' => 'required|array|min:1',
            'nim.*' => 'required|string|max:20',
            'prodi' => 'required|array|min:1',
            'prodi.*' => 'required|string|max:100',
            'peran' => 'required|array|min:1',
            'peran.*' => 'required|in:Ketua,Anggota',
            'file' => 'required|file|mimes:pdf|max:10240' // 10MB max
        ], [
            'judul_kegiatan.required' => 'Judul kegiatan harus diisi',
            'mitra.required' => 'Nama mitra harus diisi',
            'lokasi_mitra.required' => 'Lokasi mitra harus diisi',
            'user_nim.required' => 'NIM user input harus diisi',
            'nama.required' => 'Data anggota harus diisi',
            'nama.min' => 'Minimal harus ada 1 anggota',
            'nim.required' => 'NIM anggota harus diisi',
            'prodi.required' => 'Program studi harus dipilih',
            'peran.required' => 'Peran anggota harus dipilih',
            'file.required' => 'File CPMK harus diupload',
            'file.mimes' => 'File harus berformat PDF',
            'file.max' => 'Ukuran file maksimal 10MB'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Upload file
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('kkn-files', $fileName, 'public');

            // Simpan data pendaftar
            $pendaftar = KknPendaftar::create([
                'judul_kegiatan' => trim($request->judul_kegiatan),
                'mitra' => trim($request->mitra),
                'lokasi_mitra' => trim($request->lokasi_mitra),
                'file_path' => $filePath,
                'file_name' => $fileName,
                'status' => 'pending',
                'status_verifikasi' => 'pending',
                'user_nim' => $request->user_nim ?? auth()->user()->nim ?? null
            ]);

            // Simpan data anggota
            $namaArray = $request->nama;
            $nimArray = $request->nim;
            $prodiArray = $request->prodi;
            $peranArray = $request->peran;

            for ($i = 0; $i < count($namaArray); $i++) {
                if (!empty($namaArray[$i]) && !empty($nimArray[$i]) && !empty($prodiArray[$i]) && !empty($peranArray[$i])) {
                    KknAnggota::create([
                        'kkn_pendaftar_id' => $pendaftar->id,
                        'nama' => trim($namaArray[$i]),
                        'nim' => trim($nimArray[$i]),
                        'program_studi' => trim($prodiArray[$i]),
                        'peran' => trim($peranArray[$i])
                    ]);
                }
            }

            return redirect()->route('special-pages.pendaftar')->with('success', 'Pendaftar berhasil ditambahkan!');

        } catch (\Exception $e) {
            // Hapus file jika ada error
            if (isset($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail pendaftar
     */
    public function show($id)
    {
        $pendaftar = KknPendaftar::with('anggota')->findOrFail($id);
        return response()->json($pendaftar);
    }

    /**
     * Update data pendaftar
     */
    public function update(Request $request, $id)
    {
        $pendaftar = KknPendaftar::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'mitra' => 'required|string|max:100',
            'lokasi_mitra' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $pendaftar->update([
                'judul_kegiatan' => $request->judul_kegiatan,
                'mitra' => $request->mitra,
                'lokasi_mitra' => $request->lokasi_mitra,
            ]);

            return redirect()->route('special-pages.pendaftar')->with('success', 'Data pendaftar berhasil diupdate!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Verifikasi pendaftar
     */
    public function verifikasi(Request $request, $id)
    {
        $pendaftar = KknPendaftar::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status_verifikasi' => 'required|in:diterima,ditolak,pending',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $pendaftar->update([
                'status_verifikasi' => $request->status_verifikasi,
                'catatan_verifikasi' => $request->catatan_verifikasi,
                'tanggal_verifikasi' => now(),
            ]);

            return redirect()->route('special-pages.pendaftar')->with('success', 'Status verifikasi berhasil diupdate!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Hapus data pendaftar
     */
    public function destroy($id)
    {
        try {
            $pendaftar = KknPendaftar::findOrFail($id);
            
            // Hapus file jika ada
            if ($pendaftar->file_path) {
                Storage::disk('public')->delete($pendaftar->file_path);
            }
            
            // Hapus data pendaftar (anggota akan terhapus otomatis karena cascade)
            $pendaftar->delete();

            return redirect()->route('special-pages.pendaftar')->with('success', 'Data pendaftar berhasil dihapus!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
} 