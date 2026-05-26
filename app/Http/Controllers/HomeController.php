<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /*
     * Dashboard Pages Routs
     */
    public function index(Request $request)
    {
        $assets = ['chart', 'animation'];
        return view('dashboards.dashboard', compact('assets'));
    }

    /*
     * Menu Style Routs
     */
    public function horizontal(Request $request)
    {
        $assets = ['chart', 'animation'];
        return view('menu-style.horizontal',compact('assets'));
    }
    public function dualhorizontal(Request $request)
    {
        $assets = ['chart', 'animation'];
        return view('menu-style.dual-horizontal',compact('assets'));
    }
    public function dualcompact(Request $request)
    {
        $assets = ['chart', 'animation'];
        return view('menu-style.dual-compact',compact('assets'));
    }
    public function boxed(Request $request)
    {
        $assets = ['chart', 'animation'];
        return view('menu-style.boxed',compact('assets'));
    }
    public function boxedfancy(Request $request)
    {
        $assets = ['chart', 'animation'];
        return view('menu-style.boxed-fancy',compact('assets'));
    }

    /*
     * Pages Routs
     */
    public function billing(Request $request)
    {
        return view('special-pages.billing');
    }

    public function pendaftar(Request $request)
    {
        // Ambil data dari session jika ada, jika tidak gunakan sample data
        $pendaftar = session('pendaftar', collect([
            (object) [
                'id' => 1,
                'judul_kegiatan' => 'Pelatihan Web Development',
                'mitra' => 'Mitra A',
                'jumlah_anggota' => 15,
                'tanggal_daftar' => '2024-01-15',
                'deskripsi' => 'Pelatihan intensif web development untuk pemula'
            ],
            (object) [
                'id' => 2,
                'judul_kegiatan' => 'Workshop UI/UX Design',
                'mitra' => 'Mitra B',
                'jumlah_anggota' => 8,
                'tanggal_daftar' => '2024-01-20',
                'deskripsi' => 'Workshop desain antarmuka pengguna yang modern'
            ],
            (object) [
                'id' => 3,
                'judul_kegiatan' => 'Seminar Digital Marketing',
                'mitra' => 'Mitra C',
                'jumlah_anggota' => 25,
                'tanggal_daftar' => '2024-01-25',
                'deskripsi' => 'Seminar strategi pemasaran digital terkini'
            ]
        ]));
        
        return view('special-pages.pendaftar', compact('pendaftar'));
    }

    public function pendaftarStore(Request $request)
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

            // Simpan ke session untuk sementara
            $pendaftar = session('pendaftar', collect());
            $newId = $pendaftar->count() + 1;
            
            // Hitung jumlah anggota yang valid
            $namaArray = $request->nama;
            $jumlahAnggota = 0;
            for ($i = 0; $i < count($namaArray); $i++) {
                if (!empty($namaArray[$i]) && !empty($request->nim[$i]) && !empty($request->prodi[$i]) && !empty($request->peran[$i])) {
                    $jumlahAnggota++;
                }
            }

            // Simpan data pendaftar
            $pendaftarData = (object) [
                'id' => $newId,
                'judul_kegiatan' => trim($request->judul_kegiatan),
                'mitra' => trim($request->mitra),
                'lokasi_mitra' => trim($request->lokasi_mitra),
                'jumlah_anggota' => $jumlahAnggota,
                'tanggal_daftar' => now()->format('Y-m-d'),
                'file_path' => $filePath,
                'file_name' => $fileName,
                'created_at' => now()->format('d/m/Y H:i'),
                'status' => 'pending'
            ];

            // Simpan data anggota
            $anggota = collect();
            for ($i = 0; $i < count($namaArray); $i++) {
                if (!empty($namaArray[$i]) && !empty($request->nim[$i]) && !empty($request->prodi[$i]) && !empty($request->peran[$i])) {
                    $anggota->push((object) [
                        'nama' => trim($namaArray[$i]),
                        'nim' => trim($request->nim[$i]),
                        'program_studi' => trim($request->prodi[$i]),
                        'peran' => trim($request->peran[$i])
                    ]);
                }
            }
            
            $pendaftarData->anggota = $anggota;
            $pendaftar->push($pendaftarData);
            
            session(['pendaftar' => $pendaftar]);

            return redirect()->route('special-pages.pendaftar')->with('success', 'Data pendaftar berhasil ditambahkan!');

        } catch (\Exception $e) {
            // Hapus file jika ada error
            if (isset($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function pendaftarUpdate(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'judul_kegiatan' => 'required|string|max:255',
            'mitra' => 'required|string|max:100',
            'jumlah_anggota' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string'
        ], [
            'judul_kegiatan.required' => 'Judul kegiatan harus diisi',
            'judul_kegiatan.max' => 'Judul kegiatan maksimal 255 karakter',
            'mitra.required' => 'Nama mitra harus diisi',
            'mitra.max' => 'Nama mitra maksimal 100 karakter',
            'jumlah_anggota.required' => 'Jumlah anggota harus diisi',
            'jumlah_anggota.integer' => 'Jumlah anggota harus berupa angka',
            'jumlah_anggota.min' => 'Jumlah anggota minimal 1 orang'
        ]);

        // Update data di session
        $pendaftar = session('pendaftar', collect());
        $pendaftar = $pendaftar->map(function($item) use ($request, $id) {
            if ($item->id == $id) {
                $item->judul_kegiatan = trim($request->judul_kegiatan);
                $item->mitra = trim($request->mitra);
                $item->jumlah_anggota = (int) $request->jumlah_anggota;
                $item->deskripsi = trim($request->deskripsi ?? '');
            }
            return $item;
        });
        
        session(['pendaftar' => $pendaftar]);

        return redirect()->route('special-pages.pendaftar')->with('success', 'Data pendaftar berhasil diperbarui!');
    }

    public function pendaftarDestroy($id)
    {
        // Hapus data dari session
        $pendaftar = session('pendaftar', collect());
        $itemToDelete = $pendaftar->where('id', $id)->first();
        
        if ($itemToDelete && isset($itemToDelete->file_path)) {
            // Hapus file dari storage
            Storage::disk('public')->delete($itemToDelete->file_path);
        }
        
        $pendaftar = $pendaftar->filter(function($item) use ($id) {
            return $item->id != $id;
        });
        
        session(['pendaftar' => $pendaftar]);

        return redirect()->route('special-pages.pendaftar')->with('success', 'Data pendaftar berhasil dihapus!');
    }

    public function pendaftarVerifikasi(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'status_verifikasi' => 'required|in:diterima,ditolak,pending',
            'catatan_verifikasi' => 'nullable|string|max:500'
        ]);

        // Update status verifikasi di session
        $pendaftar = session('pendaftar', collect());
        $pendaftar = $pendaftar->map(function($item) use ($request, $id) {
            if ($item->id == $id) {
                $item->status_verifikasi = $request->status_verifikasi;
                $item->catatan_verifikasi = $request->catatan_verifikasi;
                $item->tanggal_verifikasi = now()->format('Y-m-d H:i:s');
            }
            return $item;
        });
        
        session(['pendaftar' => $pendaftar]);

        $statusText = ucfirst($request->status_verifikasi);
        return redirect()->route('special-pages.pendaftar')->with('success', "Data pendaftar berhasil diverifikasi dengan status: {$statusText}!");
    }

    public function calender(Request $request)
    {
        $assets = ['calender'];
        return view('special-pages.calender',compact('assets'));
    }

    public function kanban(Request $request)
    {
        return view('special-pages.kanban');
    }

    public function pricing(Request $request)
    {
        return view('special-pages.pricing');
    }

    public function rtlsupport(Request $request)
    {
        return view('special-pages.rtl-support');
    }

    public function timeline(Request $request)
    {
        return view('special-pages.timeline');
    }


    /*
     * Widget Routs
     */
    public function widgetbasic(Request $request)
    {
        return view('widget.widget-basic');
    }
    public function widgetchart(Request $request)
    {
        $assets = ['chart'];
        return view('widget.widget-chart', compact('assets'));
    }
    public function widgetcard(Request $request)
    {
        return view('widget.widget-card');
    }

    /*
     * Maps Routs
     */
    public function google(Request $request)
    {
        return view('maps.google');
    }
    public function vector(Request $request)
    {
        return view('maps.vector');
    }

    /*
     * Auth Routs
     */
    public function signin(Request $request)
    {
        return view('auth.login');
    }
    public function signup(Request $request)
    {
        return view('auth.register');
    }
    public function confirmmail(Request $request)
    {
        return view('auth.confirm-mail');
    }
    public function lockscreen(Request $request)
    {
        return view('auth.lockscreen');
    }
    public function recoverpw(Request $request)
    {
        return view('auth.recoverpw');
    }
    public function userprivacysetting(Request $request)
    {
        return view('auth.user-privacy-setting');
    }

    /*
     * Error Page Routs
     */

    public function error404(Request $request)
    {
        return view('errors.error404');
    }

    public function error500(Request $request)
    {
        return view('errors.error500');
    }
    public function maintenance(Request $request)
    {
        return view('errors.maintenance');
    }

    /*
     * uisheet Page Routs
     */
    public function uisheet(Request $request)
    {
        return view('uisheet');
    }

    /*
     * Form Page Routs
     */
    public function element(Request $request)
    {
        return view('forms.element');
    }

    public function wizard(Request $request)
    {
        return view('forms.wizard');
    }

    public function validation(Request $request)
    {
        return view('forms.validation');
    }

     /*
     * Table Page Routs
     */
    public function bootstraptable(Request $request)
    {
        return view('table.bootstraptable');
    }

    public function datatable(Request $request)
    {
        return view('table.datatable');
    }

    /*
     * Icons Page Routs
     */

    public function solid(Request $request)
    {
        return view('icons.solid');
    }

    public function outline(Request $request)
    {
        return view('icons.outline');
    }

    public function dualtone(Request $request)
    {
        return view('icons.dualtone');
    }

    public function colored(Request $request)
    {
        return view('icons.colored');
    }

    /*
     * Extra Page Routs
     */
    public function privacypolicy(Request $request)
    {
        return view('privacy-policy');
    }
    public function termsofuse(Request $request)
    {
        return view('terms-of-use');
    }
}
