<?php

namespace App\Http\Controllers;

use App\Models\NilaiCpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Added this import for User model

class NilaiCpmkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $nilaiCpmk = NilaiCpmk::orderBy('created_at', 'desc')->paginate(10);
        
        return view('pages.nilai-cpmk.index', compact('nilaiCpmk'));
    }

    /**
     * Halaman create nilai CPMK (assign mahasiswa)
     */
    public function create()
    {
        // Ambil daftar mahasiswa yang bisa diassign
        $mahasiswaList = User::where('role', 'mahasiswa')
            ->select('id', 'nim', 'username', 'first_name', 'last_name', 'email')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nim' => $student->nim ?? $student->username,
                    'name' => trim($student->first_name . ' ' . $student->last_name),
                    'email' => $student->email
                ];
            });

        return view('pages.nilai-cpmk.create', compact('mahasiswaList'));
    }

    /**
     * Simpan assignment nilai CPMK
     */
    public function store(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:users,id',
            'judul_penilaian' => 'required|string|max:500',
            'file_pdf' => 'required|file|mimes:pdf|max:10240', // 10MB
            'catatan' => 'nullable|string|max:1000', // Optional comment field
        ]);

        // Ambil data mahasiswa
        $mahasiswa = User::find($request->mahasiswa_id);
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan');
        }

        // Cek apakah mahasiswa sudah memiliki nilai CPMK
        $nimMahasiswa = $mahasiswa->nim ?? $mahasiswa->username;
        $existingNilai = NilaiCpmk::where('nim_mahasiswa', $nimMahasiswa)->first();
        if ($existingNilai) {
            return redirect()->back()->with('error', 'Mahasiswa ini sudah memiliki nilai CPMK');
        }

        // Handle file upload
        $file = $request->file('file_pdf');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $fileContent = file_get_contents($file->getRealPath());

        // Debug data before create
        $createData = [
            'nim_mahasiswa' => $mahasiswa->nim ?? $mahasiswa->username,
            'nama_mahasiswa' => trim($mahasiswa->first_name . ' ' . $mahasiswa->last_name),
            'judul_kegiatan' => $request->judul_penilaian, // Use input from form
            'file_name' => $fileName,
            'file_content' => $fileContent,
            'file_mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->user()->username ?? auth()->user()->nim ?? 'tim_penciri',
            'status' => 'pending',
            'catatan' => $request->catatan ? trim($request->catatan) : '' // Save comment if provided
            // Removed uploaded_at - let Laravel handle it
        ];
        
        // Log debug info
        \Log::info('NilaiCpmk create data:', $createData);
        
        try {
            // Simpan nilai CPMK
            NilaiCpmk::create($createData);
            
            return redirect()->route('nilai-cpmk.index')
                ->with('success', 'Nilai CPMK berhasil diassign ke mahasiswa');
                
        } catch (\Exception $e) {
            \Log::error('NilaiCpmk create error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $createData
            ]);
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Assign nilai CPMK ke mahasiswa (dari modal)
     */
    public function assignMahasiswa(Request $request)
    {
        $request->validate([
            'nim_mahasiswa' => 'required|string|max:20',
            'nama_mahasiswa' => 'required|string|max:255',
            'judul_kegiatan' => 'required|string|max:500',
            'file_pdf' => 'required|file|mimes:pdf|max:10240',
            'catatan' => 'nullable|string|max:1000',
        ]);

        try {
            // Cek apakah mahasiswa sudah ada nilai CPMK
            $existingNilai = NilaiCpmk::where('nim_mahasiswa', trim($request->nim_mahasiswa))->first();
            if ($existingNilai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mahasiswa ini sudah memiliki nilai CPMK'
                ], 400);
            }

            $file = $request->file('file_pdf');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $fileContent = file_get_contents($file->getRealPath());
            $fileSize = $file->getSize();
            $fileMimeType = $file->getMimeType();

            $nilaiCpmk = NilaiCpmk::create([
                'nim_mahasiswa' => trim($request->nim_mahasiswa),
                'nama_mahasiswa' => trim($request->nama_mahasiswa),
                'judul_kegiatan' => trim($request->judul_kegiatan),
                'file_name' => $fileName,
                'file_content' => $fileContent,
                'file_mime_type' => $fileMimeType,
                'file_size' => $fileSize,
                'uploaded_by' => Auth::user()->username ?? Auth::user()->nim ?? 'tim_penciri',
                'status' => 'pending',
                'catatan' => $request->catatan ? trim($request->catatan) : ''
                // Removed uploaded_at - let Laravel handle it
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nilai CPMK berhasil diassign ke mahasiswa',
                'data' => $nilaiCpmk
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NilaiCpmk $nilaiCpmk)
    {
        // Ambil daftar mahasiswa yang bisa diassign
        $mahasiswaList = User::where('role', 'mahasiswa')
            ->select('id', 'nim', 'username', 'first_name', 'last_name', 'email')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nim' => $student->nim ?? $student->username,
                    'name' => trim($student->first_name . ' ' . $student->last_name),
                    'email' => $student->email
                ];
            });

        // Cari ID mahasiswa saat ini berdasarkan NIM
        $currentMahasiswaId = null;
        if ($nilaiCpmk->nim_mahasiswa) {
            $currentMahasiswa = User::where('role', 'mahasiswa')
                ->where(function($query) use ($nilaiCpmk) {
                    $query->where('nim', $nilaiCpmk->nim_mahasiswa)
                          ->orWhere('username', $nilaiCpmk->nim_mahasiswa);
                })
                ->first();
            $currentMahasiswaId = $currentMahasiswa ? $currentMahasiswa->id : null;
        }

        return view('pages.nilai-cpmk.edit', compact('nilaiCpmk', 'mahasiswaList', 'currentMahasiswaId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NilaiCpmk $nilaiCpmk)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:users,id',
            'judul_kegiatan' => 'required|string|max:500',
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'catatan' => 'nullable|string|max:1000',
        ]);

        try {
            // Ambil data mahasiswa
            $mahasiswa = User::find($request->mahasiswa_id);
            if (!$mahasiswa) {
                return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan');
            }

            $data = [
                'nim_mahasiswa' => $mahasiswa->nim ?? $mahasiswa->username,
                'nama_mahasiswa' => trim($mahasiswa->first_name . ' ' . $mahasiswa->last_name),
                'judul_kegiatan' => trim($request->judul_kegiatan),
                'catatan' => $request->catatan ? trim($request->catatan) : '',
            ];

            // Update file if new file is uploaded
            if ($request->hasFile('file_pdf')) {
                $file = $request->file('file_pdf');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $fileContent = file_get_contents($file->getRealPath());
                $fileSize = $file->getSize();
                $fileMimeType = $file->getMimeType();

                $data['file_name'] = $fileName;
                $data['file_content'] = $fileContent;
                $data['file_mime_type'] = $fileMimeType;
                $data['file_size'] = $fileSize;
            }

            $nilaiCpmk->update($data);

            return redirect()->route('nilai-cpmk.index')
                ->with('success', 'Data nilai CPMK berhasil diupdate!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NilaiCpmk $nilaiCpmk)
    {
        try {
            $nilaiCpmk->delete();
            return redirect()->route('nilai-cpmk.index')
                ->with('success', 'Data nilai CPMK berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Unassign nilai CPMK (remove assignment but keep data)
     */
    public function unassign(NilaiCpmk $nilaiCpmk)
    {
        try {
            // Reset assignment fields but keep the data
            $nilaiCpmk->update([
                'nim_mahasiswa' => '', // Use empty string instead of null
                'nama_mahasiswa' => '', // Use empty string instead of null
                'status' => 'pending' // Use valid enum value
            ]);

            return redirect()->route('nilai-cpmk.index')
                ->with('success', 'Assignment nilai CPMK berhasil dihapus! Data tetap tersimpan.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Download file PDF
     */
    public function download(NilaiCpmk $nilaiCpmk)
    {
        if (!$nilaiCpmk->file_content) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        return response($nilaiCpmk->file_content)
            ->header('Content-Type', $nilaiCpmk->file_mime_type ?? 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $nilaiCpmk->file_name . '"');
    }

    /**
     * View file PDF in browser
     */
    public function view(NilaiCpmk $nilaiCpmk)
    {
        if (!$nilaiCpmk->file_content) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        return response($nilaiCpmk->file_content)
            ->header('Content-Type', $nilaiCpmk->file_mime_type ?? 'application/pdf');
    }

    /**
     * Search students by NIM for autocomplete
     */
    public function searchStudents(Request $request)
    {
        $query = $request->get('q');
        
        try {
            // If no query, show all students (limited to 20)
            if (empty($query)) {
                $students = \App\Models\User::where('role', 'mahasiswa')
                    ->limit(20)
                    ->get(['id', 'nim', 'username', 'first_name', 'last_name', 'email'])
                    ->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'nim' => $student->nim ?? $student->username,
                            'name' => trim($student->first_name . ' ' . $student->last_name),
                            'email' => $student->email,
                            'text' => ($student->nim ?? $student->username) . ' - ' . trim($student->first_name . ' ' . $student->last_name)
                        ];
                    });
            } else {
                // Search with query
                $students = \App\Models\User::where('role', 'mahasiswa')
                    ->where(function($q) use ($query) {
                        $q->where('nim', 'LIKE', "%{$query}%")
                          ->orWhere('username', 'LIKE', "%{$query}%")
                          ->orWhere('first_name', 'LIKE', "%{$query}%")
                          ->orWhere('last_name', 'LIKE', "%{$query}%");
                    })
                    ->limit(20)
                    ->get(['id', 'nim', 'username', 'first_name', 'last_name', 'email'])
                    ->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'nim' => $student->nim ?? $student->username,
                            'name' => trim($student->first_name . ' ' . $student->last_name),
                            'email' => $student->email,
                            'text' => ($student->nim ?? $student->username) . ' - ' . trim($student->first_name . ' ' . $student->last_name)
                        ];
                    });
            }

            return response()->json([
                'status' => 'success',
                'data' => $students,
                'meta' => [
                    'keyword' => $query,
                    'count' => $students->count(),
                    'timestamp' => now()->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mencari mahasiswa: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get all registered students
     */
    public function getAllStudents()
    {
        $students = \App\Models\User::where('role', 'mahasiswa')
            ->orderBy('nim', 'asc')
            ->get(['id', 'nim', 'username', 'first_name', 'last_name', 'email'])
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nim' => $student->nim ?? $student->username,
                    'name' => trim($student->first_name . ' ' . $student->last_name),
                    'email' => $student->email,
                    'text' => ($student->nim ?? $student->username) . ' - ' . trim($student->first_name . ' ' . $student->last_name)
                ];
            });

        return response()->json($students);
    }

    /**
     * Get student details by ID
     */
    public function getStudentDetails($id)
    {
        $student = \App\Models\User::find($id);
        
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json([
            'id' => $student->id,
            'nim' => $student->nim ?? $student->username,
            'name' => trim($student->first_name . ' ' . $student->last_name),
            'email' => $student->email
        ]);
    }

    /**
     * Get student by ID (for dropdown)
     */
    public function getStudentById(Request $request)
    {
        $id = $request->get('id');
        
        if (!$id) {
            return response()->json(null);
        }

        $student = \App\Models\User::find($id);
        
        if (!$student) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $student->id,
            'nim' => $student->nim ?? $student->username,
            'name' => trim($student->first_name . ' ' . $student->last_name),
            'email' => $student->email
        ]);
    }
} 