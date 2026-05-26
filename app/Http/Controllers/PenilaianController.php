<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PenilaianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $penilaian = DB::table('penilaian')
                ->join('users as mahasiswa', 'penilaian.mahasiswa_nim', '=', 'mahasiswa.nim')
                ->join('users as dosen', 'penilaian.dosen_id', '=', 'dosen.id')
                ->select(
                    'penilaian.*',
                    'mahasiswa.first_name as mahasiswa_nama',
                    'dosen.first_name as dosen_nama'
                )
                ->where('penilaian.dosen_id', Auth::id())
                ->orderBy('penilaian.created_at', 'desc')
                ->get();

            return view('penilaian.index', compact('penilaian'));
        } catch (\Exception $e) {
            \Log::error('Error in PenilaianController::index: ' . $e->getMessage());
            return view('penilaian.index', compact('penilaian'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get mahasiswa data from kkn_anggota
        $mahasiswa = DB::table('kkn_anggota')
            ->join('users', 'kkn_anggota.nim', '=', 'users.nim')
            ->select('kkn_anggota.nim', 'users.first_name', 'users.last_name')
            ->get();

        return view('penilaian.create', compact('mahasiswa'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'mahasiswa_nim' => 'required|string',
                'nilai_akhir' => 'required|numeric|min:0|max:100',
                'nilai_kehadiran' => 'nullable|numeric|min:0|max:100',
                'nilai_tugas' => 'nullable|numeric|min:0|max:100',
                'nilai_praktikum' => 'nullable|numeric|min:0|max:100',
                'nilai_ujian' => 'nullable|numeric|min:0|max:100',
                'catatan' => 'nullable|string',
                'file_nilai' => 'nullable|file|mimes:pdf|max:2048'
            ]);

            $data = [
                'mahasiswa_nim' => $request->mahasiswa_nim,
                'dosen_id' => Auth::id(),
                'nilai_akhir' => $request->nilai_akhir,
                'nilai_kehadiran' => $request->nilai_kehadiran,
                'nilai_tugas' => $request->nilai_tugas,
                'nilai_praktikum' => $request->nilai_praktikum,
                'nilai_ujian' => $request->nilai_ujian,
                'catatan' => $request->catatan,
                'tanggal_penilaian' => now(),
                'status' => 'pending'
            ];

            // Handle file upload
            if ($request->hasFile('file_nilai')) {
                $file = $request->file('file_nilai');
                $fileName = 'nilai_' . $request->mahasiswa_nim . '_' . time() . '.pdf';
                $filePath = $file->storeAs('penilaian', $fileName, 'public');
                $data['file_nilai'] = $fileName;
            }

            // Insert or update penilaian in hope-ui
            DB::table('penilaian')->updateOrInsert(
                [
                    'mahasiswa_nim' => $request->mahasiswa_nim,
                    'dosen_id' => Auth::id()
                ],
                $data
            );

            // AUTO-SYNC: Also save to project-akhir database
            DB::connection('project_akhir')->table('penilaian')->updateOrInsert(
                [
                    'mahasiswa_nim' => $request->mahasiswa_nim,
                    'dosen_id' => Auth::id()
                ],
                [
                    'mahasiswa_nim' => $request->mahasiswa_nim,
                    'dosen_id' => Auth::id(),
                    'nilai_akhir' => $request->nilai_akhir,
                    'nilai_kehadiran' => $request->nilai_kehadiran,
                    'nilai_tugas' => $request->nilai_tugas,
                    'nilai_praktikum' => $request->nilai_praktikum,
                    'nilai_ujian' => $request->nilai_ujian,
                    'catatan' => $request->catatan,
                    'tanggal_penilaian' => now(),
                    'updated_at' => now()
                ]
            );

            // Clear dashboard cache
            Cache::forget('nilai_akhir_dashboard_data');

            return redirect()->route('penilaian.index')
                ->with('success', 'Nilai berhasil disimpan dan dashboard terupdate!');

        } catch (\Exception $e) {
            \Log::error('Error in PenilaianController::store: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan nilai: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $penilaian = DB::table('penilaian')
                ->join('users as mahasiswa', 'penilaian.mahasiswa_nim', '=', 'mahasiswa.nim')
                ->join('users as dosen', 'penilaian.dosen_id', '=', 'dosen.id')
                ->select(
                    'penilaian.*',
                    'mahasiswa.first_name as mahasiswa_nama',
                    'dosen.first_name as dosen_nama'
                )
                ->where('penilaian.id', $id)
                ->first();

            if (!$penilaian) {
                return redirect()->route('penilaian.index')
                    ->with('error', 'Data penilaian tidak ditemukan');
            }

            return view('penilaian.show', compact('penilaian'));
        } catch (\Exception $e) {
            \Log::error('Error in PenilaianController::show: ' . $e->getMessage());
            return redirect()->route('penilaian.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan data');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $penilaian = DB::table('penilaian')
                ->where('id', $id)
                ->where('dosen_id', Auth::id())
                ->first();

            if (!$penilaian) {
                return redirect()->route('penilaian.index')
                    ->with('error', 'Data penilaian tidak ditemukan');
            }

            // Get mahasiswa data
            $mahasiswa = DB::table('users')
                ->where('nim', $penilaian->mahasiswa_nim)
                ->first();

            return view('penilaian.edit', compact('penilaian', 'mahasiswa'));
        } catch (\Exception $e) {
            \Log::error('Error in PenilaianController::edit: ' . $e->getMessage());
            return redirect()->route('penilaian.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan form edit');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'nilai_akhir' => 'required|numeric|min:0|max:100',
                'nilai_kehadiran' => 'nullable|numeric|min:0|max:100',
                'nilai_tugas' => 'nullable|numeric|min:0|max:100',
                'nilai_praktikum' => 'nullable|numeric|min:0|max:100',
                'nilai_ujian' => 'nullable|numeric|min:0|max:100',
                'catatan' => 'nullable|string',
                'file_nilai' => 'nullable|file|mimes:pdf|max:2048'
            ]);

            $penilaian = DB::table('penilaian')
                ->where('id', $id)
                ->where('dosen_id', Auth::id())
                ->first();

            if (!$penilaian) {
                return redirect()->route('penilaian.index')
                    ->with('error', 'Data penilaian tidak ditemukan');
            }

            $data = [
                'nilai_akhir' => $request->nilai_akhir,
                'nilai_kehadiran' => $request->nilai_kehadiran,
                'nilai_tugas' => $request->nilai_tugas,
                'nilai_praktikum' => $request->nilai_praktikum,
                'nilai_ujian' => $request->nilai_ujian,
                'catatan' => $request->catatan,
                'updated_at' => now()
            ];

            // Handle file upload
            if ($request->hasFile('file_nilai')) {
                // Delete old file if exists
                if ($penilaian->file_nilai) {
                    Storage::disk('public')->delete('penilaian/' . $penilaian->file_nilai);
                }

                $file = $request->file('file_nilai');
                $fileName = 'nilai_' . $penilaian->mahasiswa_nim . '_' . time() . '.pdf';
                $filePath = $file->storeAs('penilaian', $fileName, 'public');
                $data['file_nilai'] = $fileName;
            }

            // Update in hope-ui
            DB::table('penilaian')
                ->where('id', $id)
                ->update($data);

            // AUTO-SYNC: Also update in project-akhir database
            DB::connection('project_akhir')->table('penilaian')
                ->where('mahasiswa_nim', $penilaian->mahasiswa_nim)
                ->where('dosen_id', Auth::id())
                ->update([
                    'nilai_akhir' => $request->nilai_akhir,
                    'nilai_kehadiran' => $request->nilai_kehadiran,
                    'nilai_tugas' => $request->nilai_tugas,
                    'nilai_praktikum' => $request->nilai_praktikum,
                    'nilai_ujian' => $request->nilai_ujian,
                    'catatan' => $request->catatan,
                    'updated_at' => now()
                ]);

            // Clear dashboard cache
            Cache::forget('nilai_akhir_dashboard_data');

            return redirect()->route('penilaian.index')
                ->with('success', 'Nilai berhasil diupdate dan dashboard terupdate!');

        } catch (\Exception $e) {
            \Log::error('Error in PenilaianController::update: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengupdate nilai: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $penilaian = DB::table('penilaian')
                ->where('id', $id)
                ->where('dosen_id', Auth::id())
                ->first();

            if (!$penilaian) {
                return redirect()->route('penilaian.index')
                    ->with('error', 'Data penilaian tidak ditemukan');
            }

            // Delete file if exists
            if ($penilaian->file_nilai) {
                Storage::disk('public')->delete('penilaian/' . $penilaian->file_nilai);
            }

            DB::table('penilaian')->where('id', $id)->delete();

            return redirect()->route('penilaian.index')
                ->with('success', 'Nilai berhasil dihapus!');

        } catch (\Exception $e) {
            \Log::error('Error in PenilaianController::destroy: ' . $e->getMessage());
            return redirect()->route('penilaian.index')
                ->with('error', 'Terjadi kesalahan saat menghapus nilai');
        }
    }

    /**
     * Download file nilai
     */
    public function downloadFile($filename)
    {
        try {
            $filePath = 'penilaian/' . $filename;
            
            if (!Storage::disk('public')->exists($filePath)) {
                return redirect()->back()
                    ->with('error', 'File tidak ditemukan');
            }

            return Storage::disk('public')->download($filePath);
        } catch (\Exception $e) {
            \Log::error('Error in PenilaianController::downloadFile: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengunduh file');
        }
    }
}
