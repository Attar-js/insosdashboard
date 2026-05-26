<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KknPendaftar;
use App\Models\KknAnggota;
use Illuminate\Support\Facades\Storage;

class KknController extends Controller
{
    /**
     * Menampilkan form pendaftaran KKN
     */
    public function showForm()
    {
        return view('pages.formkonversi');
    }

    /**
     * Menyimpan data pendaftaran KKN
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'judul_kegiatan' => 'required|string|max:255',
            'mitra' => 'required|string|max:100',
            'lokasi_mitra' => 'required|string|max:255',
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
            'nama.required' => 'Data anggota harus diisi',
            'nama.min' => 'Minimal harus ada 1 anggota',
            'nim.required' => 'NIM anggota harus diisi',
            'prodi.required' => 'Program studi harus dipilih',
            'peran.required' => 'Peran anggota harus dipilih',
            'file.required' => 'File CPMK harus diupload',
            'file.mimes' => 'File harus berformat PDF',
            'file.max' => 'Ukuran file maksimal 10MB'
        ]);

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
                'status' => 'pending'
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

            return redirect()->back()->with('success', 'Pendaftaran KKN berhasil disimpan! Data akan diverifikasi oleh admin.');

        } catch (\Exception $e) {
            // Hapus file jika ada error
            if (isset($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan status pendaftaran
     */
    public function status()
    {
        $pendaftar = KknPendaftar::with('anggota')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.status-pendaftaran', compact('pendaftar'));
    }

    /**
     * Menampilkan detail pendaftaran
     */
    public function detail($id)
    {
        $pendaftar = KknPendaftar::with('anggota')->findOrFail($id);
        return view('pages.detail-pendaftaran', compact('pendaftar'));
    }
} 