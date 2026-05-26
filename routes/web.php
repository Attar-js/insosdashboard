<?php

// Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Security\RolePermission;
use App\Http\Controllers\Security\RoleController;
use App\Http\Controllers\Security\PermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
// Packages
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__.'/auth.php';

Route::get('/storage', function () {
    Artisan::call('storage:link');
});

//UI Pages Routs
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::group(['middleware' => 'auth'], function () {
    // Permission Module
    Route::get('/role-permission',[RolePermission::class, 'index'])->name('role.permission.list');
    Route::resource('permission',PermissionController::class);
    Route::resource('role', RoleController::class);

    // Dashboard Routes (Admin Only)
    Route::group(['middleware' => 'admin'], function() {
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/data', [App\Http\Controllers\DashboardController::class, 'getDashboardDataAjax'])->name('dashboard.data');
    });

    // Users Module
    Route::resource('users', UserController::class);
    
    // Special Pages Routes
    Route::group(['prefix' => 'special-pages'], function() {
        Route::get('pendaftar', [App\Http\Controllers\VerifikasiPendaftarController::class, 'index'])->name('special-pages.pendaftar');
        Route::get('pendaftar/{group}', [App\Http\Controllers\VerifikasiPendaftarController::class, 'show'])->name('special-pages.pendaftar.show');
        Route::post('pendaftar/{group}/verifikasi', [App\Http\Controllers\VerifikasiPendaftarController::class, 'verify'])->name('special-pages.pendaftar.verifikasi');
        Route::get('luaran', [App\Http\Controllers\LuaranController::class, 'index'])->name('special-pages.luaran');
        Route::get('proposal', [App\Http\Controllers\ProposalController::class, 'index'])->name('special-pages.proposal');
        Route::get('laporan-akhir', [App\Http\Controllers\LaporanAkhirController::class, 'index'])->name('special-pages.laporan-akhir');
        Route::get('peer-review', [App\Http\Controllers\PeerReviewController::class, 'index'])->name('special-pages.peer-review');
        Route::get('form-kesediaan', [App\Http\Controllers\FormKesediaanController::class, 'index'])->name('special-pages.form-kesediaan');
    });
    
    // Pendaftar CRUD Routes
    Route::group(['prefix' => 'pendaftar'], function() {
        Route::post('store', [App\Http\Controllers\PendaftarController::class, 'store'])->name('pendaftar.store');
        Route::put('update/{id}', [App\Http\Controllers\PendaftarController::class, 'update'])->name('pendaftar.update');
        Route::put('verifikasi/{id}', [App\Http\Controllers\PendaftarController::class, 'verifikasi'])->name('pendaftar.verifikasi');
        Route::delete('destroy/{id}', [App\Http\Controllers\PendaftarController::class, 'destroy'])->name('pendaftar.destroy');
    });
    
    // Luaran CRUD Routes
    Route::group(['prefix' => 'luaran'], function() {
        Route::post('store', [App\Http\Controllers\LuaranController::class, 'store'])->name('luaran.store');
        Route::put('update/{id}', [App\Http\Controllers\LuaranController::class, 'update'])->name('luaran.update');
        Route::put('verifikasi/{id}', [App\Http\Controllers\LuaranController::class, 'verifikasi'])->name('luaran.verifikasi');
        Route::delete('destroy/{id}', [App\Http\Controllers\LuaranController::class, 'destroy'])->name('luaran.destroy');
    });
    
    // Proposal CRUD Routes
    Route::group(['prefix' => 'proposal'], function() {
        Route::post('store', [App\Http\Controllers\ProposalController::class, 'store'])->name('proposal.store');
        Route::put('update/{id}', [App\Http\Controllers\ProposalController::class, 'update'])->name('proposal.update');
        Route::put('verifikasi/{id}', [App\Http\Controllers\ProposalController::class, 'verifikasi'])->name('proposal.verifikasi');
        Route::delete('destroy/{id}', [App\Http\Controllers\ProposalController::class, 'destroy'])->name('proposal.destroy');
    });
    
    // Laporan Akhir CRUD Routes
    Route::group(['prefix' => 'laporan-akhir'], function() {
        Route::post('store', [App\Http\Controllers\LaporanAkhirController::class, 'store'])->name('laporan-akhir.store');
        Route::put('update/{id}', [App\Http\Controllers\LaporanAkhirController::class, 'update'])->name('laporan-akhir.update');
        Route::put('verifikasi/{id}', [App\Http\Controllers\LaporanAkhirController::class, 'verifikasi'])->name('laporan-akhir.verifikasi');
        Route::delete('destroy/{id}', [App\Http\Controllers\LaporanAkhirController::class, 'destroy'])->name('laporan-akhir.destroy');
    });
    
    // File Routes
    Route::group(['prefix' => 'files'], function() {
        Route::get('pdf/{filename}', [App\Http\Controllers\FileController::class, 'showPdf'])->name('files.pdf.show');
        Route::get('pdf/{filename}/download', [App\Http\Controllers\FileController::class, 'downloadPdf'])->name('files.pdf.download');
        Route::get('check/{filename}', [App\Http\Controllers\FileController::class, 'checkFile'])->name('files.check');
        Route::get('list', [App\Http\Controllers\FileController::class, 'listFiles'])->name('files.list');
        Route::get('status', [App\Http\Controllers\FileController::class, 'checkFileStatus'])->name('files.status');
        Route::post('fix-missing', [App\Http\Controllers\FileController::class, 'fixMissingFiles'])->name('files.fix-missing');
        Route::post('create-sample', [App\Http\Controllers\FileController::class, 'createSampleFile'])->name('files.create-sample');
        Route::post('sync-from-project', [App\Http\Controllers\FileController::class, 'syncFromProjectAkhir'])->name('files.sync');
        
        // Database BLOB Routes
        Route::get('pdf-db/{filename}', [App\Http\Controllers\FileController::class, 'showPdfFromDatabase'])->name('files.pdf.db');
        Route::get('pdf-db/{filename}/download', [App\Http\Controllers\FileController::class, 'downloadPdfFromDatabase'])->name('files.pdf.db.download');
    });
    
    // Nilai CPMK Routes (Tim Penciri)
    Route::group(['prefix' => 'nilai-cpmk'], function() {
        Route::get('/', [App\Http\Controllers\NilaiCpmkController::class, 'index'])->name('nilai-cpmk.index');
        Route::get('create', [App\Http\Controllers\NilaiCpmkController::class, 'create'])->name('nilai-cpmk.create');
        Route::post('store', [App\Http\Controllers\NilaiCpmkController::class, 'store'])->name('nilai-cpmk.store');
        Route::get('edit/{nilaiCpmk}', [App\Http\Controllers\NilaiCpmkController::class, 'edit'])->name('nilai-cpmk.edit');
        Route::put('update/{nilaiCpmk}', [App\Http\Controllers\NilaiCpmkController::class, 'update'])->name('nilai-cpmk.update');
        Route::delete('destroy/{nilaiCpmk}', [App\Http\Controllers\NilaiCpmkController::class, 'destroy'])->name('nilai-cpmk.destroy');
        Route::patch('unassign/{nilaiCpmk}', [App\Http\Controllers\NilaiCpmkController::class, 'unassign'])->name('nilai-cpmk.unassign');
        Route::post('unassign/{nilaiCpmk}', [App\Http\Controllers\NilaiCpmkController::class, 'unassign']);
        Route::get('download/{nilaiCpmk}', [App\Http\Controllers\NilaiCpmkController::class, 'download'])->name('nilai-cpmk.download');
        Route::get('view/{nilaiCpmk}', [App\Http\Controllers\NilaiCpmkController::class, 'view'])->name('nilai-cpmk.view');
        
        // API Routes for student search
        Route::get('api/search-students', [App\Http\Controllers\NilaiCpmkController::class, 'searchStudents'])->name('nilai-cpmk.api.search-students');
        Route::get('api/all-students', [App\Http\Controllers\NilaiCpmkController::class, 'getAllStudents'])->name('nilai-cpmk.api.all-students');
        Route::get('api/student-details/{id}', [App\Http\Controllers\NilaiCpmkController::class, 'getStudentDetails'])->name('nilai-cpmk.api.student-details');
        Route::get('api/get-student-by-id', [App\Http\Controllers\NilaiCpmkController::class, 'getStudentById'])->name('nilai-cpmk.api.get-student-by-id');
        Route::post('api/assign-mahasiswa', [App\Http\Controllers\NilaiCpmkController::class, 'assignMahasiswa'])->name('nilai-cpmk.api.assign-mahasiswa');
    });
    
    // File Routes
    Route::group(['prefix' => 'files'], function() {
        Route::get('check-db/{filename}', [App\Http\Controllers\FileController::class, 'checkFileInDatabase'])->name('files.check.db');
        Route::post('sync-to-db/{filename}', [App\Http\Controllers\FileController::class, 'syncFileToDatabase'])->name('files.sync.to.db');
        
        // Direct Upload Routes (file langsung disimpan di dashboard)
        Route::get('pdf-dashboard/{filename}', [App\Http\Controllers\FileController::class, 'showPdfFromHopeUi'])->name('files.pdf.dashboard');
        Route::get('pdf-dashboard/{filename}/download', [App\Http\Controllers\FileController::class, 'downloadPdfFromHopeUi'])->name('files.pdf.dashboard.download');
        
        // Luaran PDF Routes (file disimpan di luaran-files/)
        Route::get('pdf-luaran/{filename}', [App\Http\Controllers\FileController::class, 'showPdfFromLuaran'])->name('files.pdf.luaran');
        Route::get('pdf-luaran/{filename}/download', [App\Http\Controllers\FileController::class, 'downloadPdfFromLuaran'])->name('files.pdf.luaran.download');
        
        // Proposal PDF Routes (file disimpan di proposal-files/)
        Route::get('pdf-proposal/{filename}', [App\Http\Controllers\FileController::class, 'showPdfFromProposal'])->name('files.pdf.proposal');
        Route::get('pdf-proposal/{filename}/download', [App\Http\Controllers\FileController::class, 'downloadPdfFromProposal'])->name('files.pdf.proposal.download');
        
        // Laporan Akhir PDF Routes (file disimpan di laporan-akhir-files/)
        Route::get('pdf-laporan-akhir/{filename}', [App\Http\Controllers\FileController::class, 'showPdfFromLaporanAkhir'])->name('files.pdf.laporan-akhir');
        Route::get('pdf-laporan-akhir/{filename}/download', [App\Http\Controllers\FileController::class, 'downloadPdfFromLaporanAkhir'])->name('files.pdf.laporan-akhir.download');
        
        // Peer Review PDF Routes (file disimpan di peer-review-files/)
        Route::get('pdf-peer-review/{filename}', [App\Http\Controllers\FileController::class, 'showPdfFromPeerReview'])->name('files.pdf.peer-review');
        Route::get('pdf-peer-review/{filename}/download', [App\Http\Controllers\FileController::class, 'downloadPdfFromPeerReview'])->name('files.pdf.peer-review.download');
        
        // Form Kesediaan PDF Routes (file disimpan di form-kesediaan-files/)
        Route::get('pdf-form-kesediaan/{filename}', [App\Http\Controllers\FileController::class, 'showPdfFromFormKesediaan'])->name('files.pdf.form-kesediaan');
        Route::get('pdf-form-kesediaan/{filename}/download', [App\Http\Controllers\FileController::class, 'downloadPdfFromFormKesediaan'])->name('files.pdf.form-kesediaan.download');
    });
    
    // Assign Kelompok Routes
    Route::group(['prefix' => 'assign-kelompok'], function() {
        Route::get('/', [App\Http\Controllers\AssignKelompokController::class, 'index'])->name('assign-kelompok.index');
        Route::post('/assign/{group_id}', [App\Http\Controllers\AssignKelompokController::class, 'assignDosen'])->name('assign-kelompok.assign-dosen');
        Route::delete('/hapus/{group_id}', [App\Http\Controllers\AssignKelompokController::class, 'hapusAssignment'])->name('assign-kelompok.hapus');
        Route::get('/assigned-groups', [App\Http\Controllers\AssignKelompokController::class, 'assignedGroups'])->name('assign-kelompok.assigned-groups');
    });

    // Nilai Akhir Routes
    Route::get('/nilai-akhir', [App\Http\Controllers\NilaiAkhirController::class, 'index'])->name('nilai-akhir.index');
    Route::get('/nilai-akhir/export/csv', [App\Http\Controllers\NilaiAkhirController::class, 'exportCsv'])->name('nilai-akhir.export.csv');
    Route::get('/nilai-akhir/export', [App\Http\Controllers\NilaiAkhirController::class, 'exportExcel'])->name('nilai-akhir.export');
    Route::get('/nilai-akhir/detail/{nim}', [App\Http\Controllers\NilaiAkhirController::class, 'detail'])->name('nilai-akhir.detail');
    Route::delete('/nilai-akhir/{id}', [App\Http\Controllers\NilaiAkhirController::class, 'destroy'])->name('nilai-akhir.destroy');
    
    // Tambah Dosen Routes
    Route::group(['prefix' => 'tambah-dosen'], function() {
        Route::get('/', [App\Http\Controllers\TambahDosenController::class, 'index'])->name('tambah-dosen.index');
        Route::post('/', [App\Http\Controllers\TambahDosenController::class, 'store'])->name('tambah-dosen.store');
        Route::get('/{id}/edit', [App\Http\Controllers\TambahDosenController::class, 'edit'])->name('tambah-dosen.edit');
        Route::put('/{id}', [App\Http\Controllers\TambahDosenController::class, 'update'])->name('tambah-dosen.update');
        Route::delete('/{id}', [App\Http\Controllers\TambahDosenController::class, 'destroy'])->name('tambah-dosen.destroy');
    });
    
    // Tambah Admin Routes (Admin Only)
    Route::group(['prefix' => 'tambah-admin', 'middleware' => 'admin'], function() {
        Route::get('/', [App\Http\Controllers\TambahAdminController::class, 'index'])->name('tambah-admin.index');
        Route::post('/', [App\Http\Controllers\TambahAdminController::class, 'store'])->name('tambah-admin.store');
        Route::get('/{id}/edit', [App\Http\Controllers\TambahAdminController::class, 'edit'])->name('tambah-admin.edit');
        Route::put('/{id}', [App\Http\Controllers\TambahAdminController::class, 'update'])->name('tambah-admin.update');
        Route::delete('/{id}', [App\Http\Controllers\TambahAdminController::class, 'destroy'])->name('tambah-admin.destroy');
    });
    
    // Debug Routes
    Route::get('debug-pdf', function() {
        return view('debug-pdf');
    })->name('debug.pdf');
    
    Route::get('debug-file/{filename?}', [App\Http\Controllers\DebugController::class, 'checkFile'])->name('debug.file');
    
    Route::get('check-file', function() {
        return view('check-file');
    })->name('check.file');
    
    Route::post('create-storage-link', function() {
        try {
            $target = storage_path('app/public');
            $link = public_path('storage');
            
            if (is_link($link)) {
                unlink($link);
            }
            
            symlink($target, $link);
            return response()->json(['success' => true, 'message' => 'Storage link created']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    })->name('create.storage.link');
    
    Route::get('fix-missing-files', function() {
        return view('fix-missing-files');
    })->name('fix.missing.files');
    
    Route::get('test-pdf-view', function() {
        return view('test-pdf-view');
    })->name('test.pdf.view');
    
    Route::get('test-simple-pdf', function() {
        return view('test-simple-pdf');
    })->name('test.simple.pdf');
    
    Route::get('file-sync-manager', function() {
        return view('file-sync-manager');
    })->name('file.sync.manager');
    
    Route::get('dashboard-config', function() {
        return view('dashboard-config');
    })->name('dashboard.config');
    
    Route::get('pendaftar/config', [App\Http\Controllers\PendaftarController::class, 'getDashboardConfig'])->name('pendaftar.config');
    
    // Debug Routes
    Route::get('debug/storage', [App\Http\Controllers\DebugUploadController::class, 'checkStorage'])->name('debug.storage');
    Route::post('debug/upload', [App\Http\Controllers\DebugUploadController::class, 'debugUpload'])->name('debug.upload');
    Route::get('debug/create-storage-link', [App\Http\Controllers\DebugUploadController::class, 'createStorageLink'])->name('debug.create-storage-link');
    
    Route::get('debug-upload', function() {
        return view('debug-upload');
    })->name('debug.upload.page');
});

//App Details Page => 'Dashboard'], function() {
Route::group(['prefix' => 'menu-style'], function() {
    //MenuStyle Page Routs
    Route::get('horizontal', [HomeController::class, 'horizontal'])->name('menu-style.horizontal');
    Route::get('dual-horizontal', [HomeController::class, 'dualhorizontal'])->name('menu-style.dualhorizontal');
    Route::get('dual-compact', [HomeController::class, 'dualcompact'])->name('menu-style.dualcompact');
    Route::get('boxed', [HomeController::class, 'boxed'])->name('menu-style.boxed');
    Route::get('boxed-fancy', [HomeController::class, 'boxedfancy'])->name('menu-style.boxedfancy');
});

//App Details Page => 'special-pages'], function() {
Route::group(['prefix' => 'special-pages'], function() {
    //Example Page Routs
    Route::get('billing', [HomeController::class, 'billing'])->name('special-pages.billing');
    Route::get('calender', [HomeController::class, 'calender'])->name('special-pages.calender');
    Route::get('kanban', [HomeController::class, 'kanban'])->name('special-pages.kanban');
    Route::get('pricing', [HomeController::class, 'pricing'])->name('special-pages.pricing');
    Route::get('rtl-support', [HomeController::class, 'rtlsupport'])->name('special-pages.rtlsupport');
    Route::get('timeline', [HomeController::class, 'timeline'])->name('special-pages.timeline');
});



//Widget Routs
Route::group(['prefix' => 'widget'], function() {
    Route::get('widget-basic', [HomeController::class, 'widgetbasic'])->name('widget.widgetbasic');
    Route::get('widget-chart', [HomeController::class, 'widgetchart'])->name('widget.widgetchart');
    Route::get('widget-card', [HomeController::class, 'widgetcard'])->name('widget.widgetcard');
});

//Maps Routs
Route::group(['prefix' => 'maps'], function() {
    Route::get('google', [HomeController::class, 'google'])->name('maps.google');
    Route::get('vector', [HomeController::class, 'vector'])->name('maps.vector');
});

//Auth pages Routs
Route::group(['prefix' => 'auth'], function() {
    Route::get('signin', [HomeController::class, 'signin'])->name('auth.signin');
    Route::get('signup', [HomeController::class, 'signup'])->name('auth.signup');
    Route::get('confirmmail', [HomeController::class, 'confirmmail'])->name('auth.confirmmail');
    Route::get('lockscreen', [HomeController::class, 'lockscreen'])->name('auth.lockscreen');
    Route::get('recoverpw', [HomeController::class, 'recoverpw'])->name('auth.recoverpw');
    Route::get('userprivacysetting', [HomeController::class, 'userprivacysetting'])->name('auth.userprivacysetting');
});

//Error Page Route
Route::group(['prefix' => 'errors'], function() {
    Route::get('error404', [HomeController::class, 'error404'])->name('errors.error404');
    Route::get('error500', [HomeController::class, 'error500'])->name('errors.error500');
    Route::get('maintenance', [HomeController::class, 'maintenance'])->name('errors.maintenance');
});


//Forms Pages Routs
Route::group(['prefix' => 'forms'], function() {
    Route::get('element', [HomeController::class, 'element'])->name('forms.element');
    Route::get('wizard', [HomeController::class, 'wizard'])->name('forms.wizard');
    Route::get('validation', [HomeController::class, 'validation'])->name('forms.validation');
});


//Table Page Routs
Route::group(['prefix' => 'table'], function() {
    Route::get('bootstraptable', [HomeController::class, 'bootstraptable'])->name('table.bootstraptable');
    Route::get('datatable', [HomeController::class, 'datatable'])->name('table.datatable');
});

//Icons Page Routs
Route::group(['prefix' => 'icons'], function() {
    Route::get('solid', [HomeController::class, 'solid'])->name('icons.solid');
    Route::get('outline', [HomeController::class, 'outline'])->name('icons.outline');
    Route::get('dualtone', [HomeController::class, 'dualtone'])->name('icons.dualtone');
    Route::get('colored', [HomeController::class, 'colored'])->name('icons.colored');
});
//Extra Page Routs
Route::get('privacy-policy', [HomeController::class, 'privacypolicy'])->name('pages.privacy-policy');
Route::get('terms-of-use', [HomeController::class, 'termsofuse'])->name('pages.term-of-use');

// KKN Routes
Route::get('/pendaftaran-kkn', [App\Http\Controllers\KknController::class, 'showForm'])->name('kkn.form');
Route::post('/pendaftaran-kkn', [App\Http\Controllers\KknController::class, 'store'])->name('kkn.store');
Route::get('/status-pendaftaran', [App\Http\Controllers\KknController::class, 'status'])->name('kkn.status');
Route::get('/detail-pendaftaran/{id}', [App\Http\Controllers\KknController::class, 'detail'])->name('kkn.detail');

// Test route untuk PDF viewer
Route::get('/test-pdf-viewer', function () {
    return view('test-pdf-viewer');
})->name('test.pdf.viewer');

// Test route untuk button
Route::get('/test-button', function () {
    return view('test-button');
})->name('test.button');

// Test route untuk proposal PDF
Route::get('/test-proposal-pdf', function () {
    return view('test-proposal-pdf');
})->name('test.proposal.pdf');

// Test route untuk JavaScript
Route::get('/test-js', function () {
    return view('test-js');
})->name('test.js');

Route::get('/test-modal', function () {
    return view('test-modal');
})->name('test.modal');

// Peer Review routes
Route::get('pdf-peer-review/{filename}', [App\Http\Controllers\FileController::class, 'showPdfFromPeerReview'])->name('files.pdf.peer-review');
Route::get('pdf-peer-review/{filename}/download', [App\Http\Controllers\FileController::class, 'downloadPdfFromPeerReview'])->name('files.pdf.peer-review.download');

// Nilai Akhir API Routes (tanpa auth untuk integrasi)
Route::group(['prefix' => 'nilai-akhir'], function() {
    Route::post('/api/receive-nilai', [App\Http\Controllers\NilaiAkhirController::class, 'receiveNilaiInput'])->name('nilai-akhir.api.receive-nilai');
});

Route::middleware(['auth'])->group(function () {
    Route::get('peer-review', [App\Http\Controllers\PeerReviewController::class, 'index'])->name('special-pages.peer-review');
    // Peer Review CRUD Routes
    Route::group(['prefix' => 'peer-review'], function() {
        Route::post('store', [App\Http\Controllers\PeerReviewController::class, 'store'])->name('peer-review.store');
        Route::put('update/{id}', [App\Http\Controllers\PeerReviewController::class, 'update'])->name('peer-review.update');
        Route::put('verifikasi/{id}', [App\Http\Controllers\PeerReviewController::class, 'verifikasi'])->name('peer-review.verifikasi');
        Route::delete('destroy/{id}', [App\Http\Controllers\PeerReviewController::class, 'destroy'])->name('peer-review.destroy');
    });
    
    // Form Kesediaan CRUD Routes
    Route::group(['prefix' => 'form-kesediaan'], function() {
        Route::post('store', [App\Http\Controllers\FormKesediaanController::class, 'store'])->name('form-kesediaan.store');
        Route::put('update/{id}', [App\Http\Controllers\FormKesediaanController::class, 'update'])->name('form-kesediaan.update');
        Route::put('verifikasi/{id}', [App\Http\Controllers\FormKesediaanController::class, 'verifikasi'])->name('form-kesediaan.verifikasi');
        Route::delete('destroy/{id}', [App\Http\Controllers\FormKesediaanController::class, 'destroy'])->name('form-kesediaan.destroy');
    });
    
    // Nilai Akhir Routes (dengan auth)
    Route::group(['prefix' => 'nilai-akhir'], function() {
        Route::get('/', [App\Http\Controllers\NilaiAkhirController::class, 'index'])->name('nilai-akhir.index');
        Route::get('/refresh', [App\Http\Controllers\NilaiAkhirController::class, 'refresh'])->name('nilai-akhir.refresh');
        Route::get('/export/csv', [App\Http\Controllers\NilaiAkhirController::class, 'exportCsv'])->name('nilai-akhir.export.csv');
        Route::get('/export', [App\Http\Controllers\NilaiAkhirController::class, 'exportExcel'])->name('nilai-akhir.export');
        Route::get('/detail/{nim}', [App\Http\Controllers\NilaiAkhirController::class, 'detail'])->name('nilai-akhir.detail');
        Route::delete('/{id}', [App\Http\Controllers\NilaiAkhirController::class, 'destroy'])->name('nilai-akhir.destroy');
        Route::get('/api/real-time-data', [App\Http\Controllers\NilaiAkhirController::class, 'getRealTimeData'])->name('nilai-akhir.api.real-time');
        Route::get('/api/real-time-with-notification', [App\Http\Controllers\NilaiAkhirController::class, 'getRealTimeDataWithNotification'])->name('nilai-akhir.api.real-time-notification');
    });
    
    // Penilaian Routes
    Route::group(['prefix' => 'penilaian'], function() {
        Route::get('/', [App\Http\Controllers\PenilaianController::class, 'index'])->name('penilaian.index');
        Route::get('/create', [App\Http\Controllers\PenilaianController::class, 'create'])->name('penilaian.create');
        Route::post('/', [App\Http\Controllers\PenilaianController::class, 'store'])->name('penilaian.store');
        Route::get('/{id}', [App\Http\Controllers\PenilaianController::class, 'show'])->name('penilaian.show');
        Route::get('/{id}/edit', [App\Http\Controllers\PenilaianController::class, 'edit'])->name('penilaian.edit');
        Route::put('/{id}', [App\Http\Controllers\PenilaianController::class, 'update'])->name('penilaian.update');
        Route::delete('/{id}', [App\Http\Controllers\PenilaianController::class, 'destroy'])->name('penilaian.destroy');
        Route::get('/download/{filename}', [App\Http\Controllers\PenilaianController::class, 'downloadFile'])->name('penilaian.download');
    });
});

