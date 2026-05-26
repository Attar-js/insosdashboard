<?php

namespace App\Http\Controllers;

use App\Models\GroupMember;
use App\Models\Penilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class NilaiAkhirController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->get('q', ''));
            $rows = $this->buildMahasiswaRows($search);

            return view('nilai-akhir.index', [
                'rows' => $rows,
                'search' => $search,
                'total' => count($rows),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in NilaiAkhirController::index: ' . $e->getMessage());

            return view('nilai-akhir.index', [
                'rows' => [],
                'search' => $request->get('q', ''),
                'total' => 0,
            ]);
        }
    }

    /**
     * API endpoint for real-time dashboard data
     */
    public function getRealTimeData()
    {
        try {
            // Clear cache to get fresh data
            Cache::forget('nilai_akhir_dashboard_data');
            
            $groups = $this->getGroupsFromMahasiswaBimbingan();
            
            // Log the data for debugging
            \Log::info('Real-time data fetched', [
                'groups_count' => count($groups),
                'timestamp' => now()->toISOString()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $groups,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in NilaiAkhirController::getRealTimeData: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching real-time data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint untuk menerima input nilai dari project-akhir
     */
    public function receiveNilaiInput(Request $request)
    {
        try {
            // Log incoming request
            \Log::info('Received nilai input from project-akhir', [
                'request_data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            // Validate input
            $request->validate([
                'mahasiswa_nim' => 'required|string',
                'dosen_id' => 'required|integer',
                'nilai_akhir' => 'required|numeric|min:0|max:100',
                'proposal_kegiatan' => 'nullable|numeric|min:0|max:100',
                'asistensi' => 'nullable|numeric|min:0|max:100',
                'peer_review' => 'nullable|numeric|min:0|max:100',
                'laporan_akhir' => 'nullable|numeric|min:0|max:100',
                'presentasi_akhir' => 'nullable|numeric|min:0|max:100',
                'pembimbing_lapangan' => 'nullable|numeric|min:0|max:100',
                'tanggal_penilaian' => 'nullable|date'
            ]);

            // Insert/Update penilaian in project_akhir database
            $penilaianData = [
                'mahasiswa_nim' => $request->mahasiswa_nim,
                'dosen_id' => $request->dosen_id,
                'nilai_akhir' => $request->nilai_akhir,
                'proposal_kegiatan' => $request->proposal_kegiatan,
                'asistensi' => $request->asistensi,
                'peer_review' => $request->peer_review,
                'laporan_akhir' => $request->laporan_akhir,
                'presentasi_akhir' => $request->presentasi_akhir,
                'pembimbing_lapangan' => $request->pembimbing_lapangan,
                'tanggal_penilaian' => $request->tanggal_penilaian ?? now(),
                'updated_at' => now()
            ];

            // Check if record exists
            $existingPenilaian = DB::connection('project_akhir')
                ->table('penilaian')
                ->where('mahasiswa_nim', $request->mahasiswa_nim)
                ->where('dosen_id', $request->dosen_id)
                ->first();

            if ($existingPenilaian) {
                // Update existing record
                DB::connection('project_akhir')
                    ->table('penilaian')
                    ->where('mahasiswa_nim', $request->mahasiswa_nim)
                    ->where('dosen_id', $request->dosen_id)
                    ->update($penilaianData);
                
                \Log::info('Updated existing penilaian record', [
                    'mahasiswa_nim' => $request->mahasiswa_nim,
                    'dosen_id' => $request->dosen_id,
                    'nilai_akhir' => $request->nilai_akhir
                ]);
            } else {
                // Insert new record
                $penilaianData['created_at'] = now();
                DB::connection('project_akhir')
                    ->table('penilaian')
                    ->insert($penilaianData);
                
                \Log::info('Inserted new penilaian record', [
                    'mahasiswa_nim' => $request->mahasiswa_nim,
                    'dosen_id' => $request->dosen_id,
                    'nilai_akhir' => $request->nilai_akhir
                ]);
            }

            // Clear cache to ensure fresh data
            Cache::forget('nilai_akhir_dashboard_data');

            // Log successful operation
            \Log::info('Successfully processed nilai input', [
                'mahasiswa_nim' => $request->mahasiswa_nim,
                'nilai_akhir' => $request->nilai_akhir,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil disimpan dan dashboard akan terupdate',
                'data' => [
                    'mahasiswa_nim' => $request->mahasiswa_nim,
                    'nilai_akhir' => $request->nilai_akhir,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in NilaiAkhirController::receiveNilaiInput: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error menyimpan nilai: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint untuk mendapatkan data real-time dengan notifikasi
     */
    public function getRealTimeDataWithNotification()
    {
        try {
            // Clear cache to get fresh data
            Cache::forget('nilai_akhir_dashboard_data');
            
            $groups = $this->getGroupsFromMahasiswaBimbingan();
            
            // Get latest penilaian for notification
            $latestPenilaian = DB::connection('project_akhir')
                ->table('penilaian')
                ->where('dosen_id', 20)
                ->orderBy('updated_at', 'desc')
                ->first();
            
            return response()->json([
                'success' => true,
                'data' => $groups,
                'latest_update' => $latestPenilaian ? [
                    'mahasiswa_nim' => $latestPenilaian->mahasiswa_nim,
                    'nilai_akhir' => $latestPenilaian->nilai_akhir,
                    'updated_at' => $latestPenilaian->updated_at
                ] : null,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in NilaiAkhirController::getRealTimeDataWithNotification: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching real-time data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get groups data from mahasiswa bimbingan
     */
    public function getGroupsFromMahasiswaBimbingan()
    {
        try {
            $groups = [];
            
            // Get group assignments from hope-ui (use default connection)
            $groupAssignments = DB::table('group_assignments')->get();
            
            \Log::info('Found group assignments', ['count' => $groupAssignments->count()]);

            foreach ($groupAssignments as $assignment) {
                // Get anggota for this group using kkn_pendaftar_id (use default connection)
                $anggota = DB::table('kkn_anggota')
                    ->where('kkn_pendaftar_id', $assignment->group_id)
                    ->get();

                // Get NIMs of anggota in this group
                $nimAnggota = $anggota->pluck('nim')->toArray();
                
                \Log::info('Processing group', [
                    'group_id' => $assignment->group_id,
                    'group_name' => $assignment->group_name,
                    'anggota_count' => $anggota->count(),
                    'nim_anggota' => $nimAnggota
                ]);

                // Get penilaian from project-akhir database ONLY for anggota in this group
                $penilaian = DB::connection('project_akhir')->table('penilaian')
                    ->where('dosen_id', $assignment->dosen_id)
                    ->whereIn('mahasiswa_nim', $nimAnggota)
                    ->get();
                
                \Log::info('Found penilaian data', [
                    'group_id' => $assignment->group_id,
                    'penilaian_count' => $penilaian->count(),
                    'penilaian_data' => $penilaian->toArray()
                ]);

                // Create statistics based on actual group members
                $totalAnggota = $anggota->count();
                $totalDinilai = $penilaian->count();
                $averageNilai = $penilaian->count() > 0 ? $penilaian->avg('nilai_akhir') : 0;
                $highestScore = $penilaian->count() > 0 ? $penilaian->max('nilai_akhir') : null;
                $lowestScore = $penilaian->count() > 0 ? $penilaian->min('nilai_akhir') : null;

                // Only add group if it has anggota
                if ($totalAnggota > 0) {
                    $groups[] = [
                        'group_id' => $assignment->group_id,
                        'group_name' => $assignment->group_name,
                        'dosen_id' => $assignment->dosen_id,
                        'pendaftar' => [
                            'mitra' => $assignment->nama_mitra
                        ],
                        'statistics' => [
                            'total_anggota' => $totalAnggota,
                            'total_dinilai' => $totalDinilai,
                            'average_nilai' => $averageNilai,
                            'highest_score' => $highestScore,
                            'lowest_score' => $lowestScore
                        ],
                        'penilaian' => $penilaian->toArray(),
                        'anggota' => $anggota->toArray()
                    ];
                }
            }

            \Log::info('Final groups data', ['groups_count' => count($groups)]);
            return $groups;
        } catch (\Exception $e) {
            \Log::error('Error in getGroupsFromMahasiswaBimbingan: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Manual refresh method to force data reload
     */
    public function refresh()
    {
        try {
            // Clear all caches
            Cache::forget('nilai_akhir_dashboard_data');
            
            // Force fresh data fetch
            $groups = $this->getGroupsFromMahasiswaBimbingan();
            
            return redirect()->route('nilai-akhir.index')
                ->with('success', 'Data berhasil diperbarui! Ditemukan ' . count($groups) . ' kelompok.');
            
        } catch (\Exception $e) {
            \Log::error('Error in NilaiAkhirController::refresh: ' . $e->getMessage());
            
            return redirect()->route('nilai-akhir.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Export nilai akhir data to Excel (per group or all groups)
     */
    public function exportExcel(Request $request)
    {
        try {
            $search = trim((string) $request->get('q', ''));
            $rows = $this->buildMahasiswaRows($search);

            if (empty($rows)) {
                return redirect()->route('nilai-akhir.index')
                    ->with('error', 'Tidak ada data untuk diekspor.');
            }

            return $this->generateExcelFromRows($rows);
        } catch (\Exception $e) {
            \Log::error('Error in NilaiAkhirController::exportExcel: ' . $e->getMessage());

            return redirect()->route('nilai-akhir.index')
                ->with('error', 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage());
        }
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->get('q', ''));
        $rows = $this->buildMahasiswaRows($search);
        $filename = 'Penilaian_Akhir_Mahasiswa_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, [
                'No', 'NIM', 'Nama Mahasiswa', 'Peran', 'Kelompok', 'Judul Kegiatan',
                'Dosen Pembimbing', 'Proposal (20%)', 'Asistensi (10%)', 'Peer Review (15%)',
                'Laporan Akhir (20%)', 'Presentasi (15%)', 'Pembimbing Lapangan (20%)', 'Nilai Akhir',
            ]);

            foreach ($rows as $row) {
                $p = $row['penilaian'] ?? [];
                fputcsv($handle, [
                    $row['no'],
                    $row['nim'],
                    $row['nama'],
                    $row['peran'],
                    $row['nama_kelompok'],
                    $row['judul_kegiatan'],
                    $row['dosen_nama'],
                    $p['proposal_kegiatan'] ?? '',
                    $p['asistensi'] ?? '',
                    $p['peer_review'] ?? '',
                    $p['laporan_akhir'] ?? '',
                    $p['presentasi_akhir'] ?? '',
                    $p['pembimbing_lapangan'] ?? '',
                    $p['nilai_akhir'] ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Get specific group data for export
     */
    private function getSpecificGroupData($groupId)
    {
        try {
            // Get specific group assignment from hope-ui
            $assignment = DB::table('group_assignments')
                ->where('group_id', $groupId)
                ->first();
            
            if (!$assignment) {
                return null;
            }
            
            // Get pendaftar data
            $pendaftar = DB::table('kkn_pendaftar')
                ->where('id', $assignment->group_id)
                ->first();
            
            if (!$pendaftar) {
                return null;
            }
            
            // Get anggota data
            $anggota = DB::table('kkn_anggota')
                ->where('kkn_pendaftar_id', $pendaftar->id)
                ->get();
            
            // Get NIMs of anggota in this group
            $nimAnggota = $anggota->pluck('nim')->toArray();
            
            // Get penilaian data from project_akhir
            $penilaianData = DB::connection('project_akhir')
                ->table('penilaian')
                ->where('dosen_id', $assignment->dosen_id)
                ->whereIn('mahasiswa_nim', $nimAnggota)
                ->get();
            
            // Prepare group data
            return [
                'group_id' => $assignment->group_id,
                'group_name' => $assignment->group_name,
                'nama_mitra' => $pendaftar->mitra ?? 'N/A',
                'anggota' => $anggota->toArray(),
                'penilaian' => $penilaianData->toArray()
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in getSpecificGroupData: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate Excel for specific group
     */
    private function generateExcelForGroup($groupData)
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('KKN Integration System')
            ->setLastModifiedBy('KKN Integration System')
            ->setTitle('Nilai Akhir KKN - ' . $groupData['group_name'])
            ->setSubject('Export Nilai Akhir KKN - ' . $groupData['group_name'])
            ->setDescription('Data nilai akhir kelompok: ' . $groupData['group_name'])
            ->setKeywords('KKN, Nilai, Excel, ' . $groupData['group_name'])
            ->setCategory('KKN Data');

        // Set headers
        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Mahasiswa',
            'C1' => 'NIM',
            'D1' => 'Proposal Kegiatan (20%)',
            'E1' => 'Asistensi (10%)',
            'F1' => 'Peer Review (15%)',
            'G1' => 'Laporan Akhir (20%)',
            'H1' => 'Presentasi Akhir (15%)',
            'I1' => 'Pembimbing Lapangan (20%)',
            'J1' => 'Nilai Akhir',
            'K1' => 'Status Penilaian',
            'L1' => 'Tanggal Penilaian'
        ];

        // Set header values
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ];

        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

        // Set row height for headers
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Set column widths
        $columnWidths = [
            'A' => 5,   // No
            'B' => 25,  // Nama Mahasiswa
            'C' => 15,  // NIM
            'D' => 20,  // Proposal
            'E' => 15,  // Asistensi
            'F' => 15,  // Peer Review
            'G' => 20,  // Laporan
            'H' => 20,  // Presentasi
            'I' => 20,  // Pembimbing
            'J' => 15,  // Nilai Akhir
            'K' => 20,  // Status
            'L' => 20   // Tanggal
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Fill data
        $row = 2;
        $no = 1;

        foreach ($groupData['anggota'] as $member) {
            // Convert member to array if it's an object
            $memberArray = is_object($member) ? (array) $member : $member;
            
            // Find corresponding penilaian data
            $penilaian = null;
            foreach ($groupData['penilaian'] as $p) {
                // Convert penilaian to array if it's an object
                $pArray = is_object($p) ? (array) $p : $p;
                if ($pArray['mahasiswa_nim'] == $memberArray['nim']) {
                    $penilaian = $pArray;
                    break;
                }
            }

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $memberArray['nama'] ?? 'N/A');
            $sheet->setCellValue('C' . $row, $memberArray['nim'] ?? 'N/A');
            $sheet->setCellValue('D' . $row, $penilaian ? number_format($penilaian['proposal_kegiatan'], 1) : '-');
            $sheet->setCellValue('E' . $row, $penilaian ? number_format($penilaian['asistensi'], 1) : '-');
            $sheet->setCellValue('F' . $row, $penilaian ? number_format($penilaian['peer_review'], 1) : '-');
            $sheet->setCellValue('G' . $row, $penilaian ? number_format($penilaian['laporan_akhir'], 1) : '-');
            $sheet->setCellValue('H' . $row, $penilaian ? number_format($penilaian['presentasi_akhir'], 1) : '-');
            $sheet->setCellValue('I' . $row, $penilaian ? number_format($penilaian['pembimbing_lapangan'], 1) : '-');
            $sheet->setCellValue('J' . $row, $penilaian ? number_format($penilaian['nilai_akhir'], 1) : '-');
            $sheet->setCellValue('K' . $row, $penilaian ? 'Sudah Dinilai' : 'Belum Dinilai');
            $sheet->setCellValue('L' . $row, $penilaian ? date('d/m/Y', strtotime($penilaian['tanggal_penilaian'])) : '-');

            // Style data rows
            $dataStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ]
            ];

            $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray($dataStyle);

            $row++;
            $no++;
        }

        // Add group info at the top
        $sheet->insertNewRowBefore(1, 3);
        $sheet->setCellValue('A1', 'Nilai Akhir KKN - ' . $groupData['group_name']);
        $sheet->setCellValue('A2', 'Nama Mitra: ' . $groupData['nama_mitra']);
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));
        
        // Style group info
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);

        // Generate filename
        $filename = 'Nilai_Akhir_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $groupData['group_name']) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Create writer and save
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Generate Excel for all groups (original functionality)
     */
    private function generateExcelForAllGroups($groups)
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('KKN Integration System')
            ->setLastModifiedBy('KKN Integration System')
            ->setTitle('Nilai Akhir KKN - Semua Kelompok')
            ->setSubject('Export Nilai Akhir KKN - Semua Kelompok')
            ->setDescription('Data nilai akhir semua kelompok KKN')
            ->setKeywords('KKN, Nilai, Excel, Semua Kelompok')
            ->setCategory('KKN Data');

        // Set headers
        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Kelompok',
            'C1' => 'Nama Mitra',
            'D1' => 'Nama Mahasiswa',
            'E1' => 'NIM',
            'F1' => 'Proposal Kegiatan (20%)',
            'G1' => 'Asistensi (10%)',
            'H1' => 'Peer Review (15%)',
            'I1' => 'Laporan Akhir (20%)',
            'J1' => 'Presentasi Akhir (15%)',
            'K1' => 'Pembimbing Lapangan (20%)',
            'L1' => 'Nilai Akhir',
            'M1' => 'Status Penilaian',
            'N1' => 'Tanggal Penilaian'
        ];

        // Set header values
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ];

        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Set column widths
        $columnWidths = [
            'A' => 5,   'B' => 25,  'C' => 20,  'D' => 25,  'E' => 15,
            'F' => 20,  'G' => 15,  'H' => 15,  'I' => 20,  'J' => 20,
            'K' => 20,  'L' => 15,  'M' => 20,  'N' => 20
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Fill data
        $row = 2;
        $no = 1;

        foreach ($groups as $group) {
            $groupArray = is_object($group) ? (array) $group : $group;
            
            if (isset($groupArray['anggota']) && is_array($groupArray['anggota'])) {
                foreach ($groupArray['anggota'] as $member) {
                    $memberArray = is_object($member) ? (array) $member : $member;
                    
                    $penilaian = null;
                    if (isset($groupArray['penilaian']) && is_array($groupArray['penilaian'])) {
                        foreach ($groupArray['penilaian'] as $p) {
                            $pArray = is_object($p) ? (array) $p : $p;
                            if ($pArray['mahasiswa_nim'] == $memberArray['nim']) {
                                $penilaian = $pArray;
                                break;
                            }
                        }
                    }

                    $sheet->setCellValue('A' . $row, $no);
                    $sheet->setCellValue('B' . $row, $groupArray['group_name'] ?? 'N/A');
                    $sheet->setCellValue('C' . $row, $groupArray['pendaftar']['mitra'] ?? 'N/A');
                    $sheet->setCellValue('D' . $row, $memberArray['nama'] ?? 'N/A');
                    $sheet->setCellValue('E' . $row, $memberArray['nim'] ?? 'N/A');
                    $sheet->setCellValue('F' . $row, $penilaian ? number_format($penilaian['proposal_kegiatan'], 1) : '-');
                    $sheet->setCellValue('G' . $row, $penilaian ? number_format($penilaian['asistensi'], 1) : '-');
                    $sheet->setCellValue('H' . $row, $penilaian ? number_format($penilaian['peer_review'], 1) : '-');
                    $sheet->setCellValue('I' . $row, $penilaian ? number_format($penilaian['laporan_akhir'], 1) : '-');
                    $sheet->setCellValue('J' . $row, $penilaian ? number_format($penilaian['presentasi_akhir'], 1) : '-');
                    $sheet->setCellValue('K' . $row, $penilaian ? number_format($penilaian['pembimbing_lapangan'], 1) : '-');
                    $sheet->setCellValue('L' . $row, $penilaian ? number_format($penilaian['nilai_akhir'], 1) : '-');
                    $sheet->setCellValue('M' . $row, $penilaian ? 'Sudah Dinilai' : 'Belum Dinilai');
                    $sheet->setCellValue('N' . $row, $penilaian ? date('d/m/Y', strtotime($penilaian['tanggal_penilaian'])) : '-');

                    $dataStyle = [
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                            ]
                        ]
                    ];

                    $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray($dataStyle);
                    $row++;
                    $no++;
                }
            }
        }

        // Add summary sheet
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Ringkasan');
        $summarySheet->setCellValue('A1', 'Ringkasan Nilai Akhir KKN');
        $summarySheet->setCellValue('A3', 'Total Kelompok:');
        $summarySheet->setCellValue('B3', count($groups));
        $summarySheet->setCellValue('A4', 'Total Mahasiswa:');
        $summarySheet->setCellValue('B4', $no - 1);
        $summarySheet->setCellValue('A5', 'Tanggal Export:');
        $summarySheet->setCellValue('B5', date('d/m/Y H:i:s'));

        $summarySheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $summarySheet->getStyle('A3:A5')->getFont()->setBold(true);
        $summarySheet->getColumnDimension('A')->setWidth(20);
        $summarySheet->getColumnDimension('B')->setWidth(15);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Nilai_Akhir_KKN_Semua_Kelompok_' . date('Y-m-d_H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show detail of a specific group with individual grades
     */
    public function detail(string $nim)
    {
        try {
            $member = GroupMember::query()
                ->where('status', 'active')
                ->whereHas('mahasiswa', fn ($q) => $q->where('nim', $nim))
                ->with(['mahasiswa', 'group.dosen'])
                ->first();

            if (!$member || !$member->group) {
                return redirect()->route('nilai-akhir.index')
                    ->with('error', 'Mahasiswa tidak ditemukan.');
            }

            $group = $member->group;
            $penilaian = $this->findPenilaianForMember($nim, $group->dosen_id);

            $student = [
                'nim' => $member->mahasiswa->nim ?? $nim,
                'nama' => $member->mahasiswa->name ?? '-',
                'peran' => $member->peranLabel(),
                'nama_kelompok' => $group->nama_kelompok ?? 'Kelompok KKN',
                'judul_kegiatan' => $group->judul_kegiatan ?? '-',
                'nama_mitra' => $group->nama_mitra ?? '-',
                'lokasi_mitra' => $group->lokasi_mitra ?? $group->lokasi_kkn ?? '-',
                'dosen_nama' => $group->dosen->name ?? 'Belum ditentukan',
                'penilaian' => $penilaian ? $this->formatPenilaianRow($penilaian) : null,
                'sudah_dinilai' => $penilaian !== null && $penilaian->nilai_akhir !== null,
            ];

            return view('nilai-akhir.detail', compact('student'));
        } catch (\Exception $e) {
            \Log::error('Error in NilaiAkhirController::detail: ' . $e->getMessage());

            return redirect()->route('nilai-akhir.index')
                ->with('error', 'Terjadi kesalahan saat memuat detail nilai: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Delete penilaian records for specific dosen_id
            $deleted = DB::connection('project_akhir')
                ->table('penilaian')
                ->where('dosen_id', $id)
                ->delete();
            
            if ($deleted > 0) {
                return redirect()->route('nilai-akhir.index')
                    ->with('success', 'Data penilaian berhasil dihapus');
            } else {
                return redirect()->route('nilai-akhir.index')
                    ->with('error', 'Data tidak ditemukan');
            }
                
        } catch (\Exception $e) {
            \Log::error('Error in NilaiAkhirController::destroy: ' . $e->getMessage());
            
            return redirect()->route('nilai-akhir.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data');
        }
    }

    /**
     * Daftar mahasiswa aktif per kelompok untuk halaman penilaian akhir.
     */
    private function buildMahasiswaRows(?string $search = null): array
    {
        $query = GroupMember::query()
            ->where('status', 'active')
            ->with(['mahasiswa', 'group.dosen']);

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('mahasiswa', function ($m) use ($search) {
                    $m->where('name', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                })
                    ->orWhereHas('group', function ($g) use ($search) {
                        $g->where('nama_kelompok', 'like', "%{$search}%")
                            ->orWhere('judul_kegiatan', 'like', "%{$search}%")
                            ->orWhere('nama_mitra', 'like', "%{$search}%")
                            ->orWhereHas('dosen', fn ($d) => $d->where('name', 'like', "%{$search}%"));
                    });
            });
        }

        $members = $query
            ->get()
            ->sortBy([
                fn ($m) => $m->group?->nama_kelompok ?? '',
                fn ($m) => $m->isLeader() ? 0 : 1,
                fn ($m) => $m->mahasiswa?->name ?? '',
            ])
            ->values();

        $penilaianMap = $this->loadPenilaianMap($members);

        $rows = [];
        $no = 1;

        foreach ($members as $member) {
            $nim = $member->mahasiswa?->nim;
            if (!$nim) {
                continue;
            }

            $dosenId = $member->group?->dosen_id;
            $key = $dosenId ? "{$nim}_{$dosenId}" : $nim;
            $penilaian = $penilaianMap[$key] ?? null;

            $rows[] = [
                'no' => $no++,
                'nim' => $nim,
                'nama' => $member->mahasiswa->name ?? '-',
                'peran' => $member->peranLabel(),
                'is_ketua' => $member->isLeader(),
                'nama_kelompok' => $member->group?->nama_kelompok ?? 'Kelompok KKN',
                'judul_kegiatan' => $member->group?->judul_kegiatan ?? '-',
                'dosen_nama' => $member->group?->dosen?->name ?? '-',
                'group_id' => $member->group_id,
                'sudah_dinilai' => $penilaian && $penilaian->nilai_akhir !== null,
                'nilai_akhir' => $penilaian?->nilai_akhir,
                'penilaian' => $penilaian ? $this->formatPenilaianRow($penilaian) : null,
            ];
        }

        return $rows;
    }

    private function loadPenilaianMap($members): array
    {
        $pairs = [];
        foreach ($members as $member) {
            $nim = $member->mahasiswa?->nim;
            $dosenId = $member->group?->dosen_id;
            if ($nim && $dosenId) {
                $pairs[] = ['nim' => $nim, 'dosen_id' => $dosenId];
            }
        }

        if (empty($pairs)) {
            return [];
        }

        $nims = array_unique(array_column($pairs, 'nim'));
        $dosenIds = array_unique(array_column($pairs, 'dosen_id'));

        $records = $this->penilaianQuery()
            ->whereIn('mahasiswa_nim', $nims)
            ->whereIn('dosen_id', $dosenIds)
            ->get();

        $map = [];
        foreach ($records as $record) {
            $map["{$record->mahasiswa_nim}_{$record->dosen_id}"] = $record;
        }

        return $map;
    }

    private function penilaianQuery()
    {
        try {
            if (Schema::hasTable('penilaian')) {
                return Penilaian::query();
            }
        } catch (\Throwable $e) {
            // fallback ke koneksi project_akhir
        }

        return DB::connection('project_akhir')->table('penilaian');
    }

    private function findPenilaianForMember(string $nim, ?int $dosenId)
    {
        $query = $this->penilaianQuery()->where('mahasiswa_nim', $nim);
        if ($dosenId) {
            $query->where('dosen_id', $dosenId);
        }

        return $query->first();
    }

    private function formatPenilaianRow($penilaian): array
    {
        return [
            'proposal_kegiatan' => $penilaian->proposal_kegiatan,
            'asistensi' => $penilaian->asistensi ?? null,
            'peer_review' => $penilaian->peer_review,
            'laporan_akhir' => $penilaian->laporan_akhir,
            'presentasi_akhir' => $penilaian->presentasi_akhir,
            'pembimbing_lapangan' => $penilaian->pembimbing_lapangan ?? null,
            'nilai_akhir' => $penilaian->nilai_akhir,
            'tanggal_penilaian' => $penilaian->tanggal_penilaian ?? null,
        ];
    }

    private function generateExcelFromRows(array $rows)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penilaian Akhir');

        $headers = [
            'No', 'NIM', 'Nama Mahasiswa', 'Peran', 'Kelompok', 'Judul Kegiatan',
            'Dosen Pembimbing', 'Proposal (20%)', 'Asistensi (10%)', 'Peer Review (15%)',
            'Laporan Akhir (20%)', 'Presentasi (15%)', 'Pembimbing Lapangan (20%)', 'Nilai Akhir',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

        $rowNum = 2;
        foreach ($rows as $row) {
            $p = $row['penilaian'] ?? [];
            $sheet->fromArray([
                $row['no'],
                $row['nim'],
                $row['nama'],
                $row['peran'],
                $row['nama_kelompok'],
                $row['judul_kegiatan'],
                $row['dosen_nama'],
                $p['proposal_kegiatan'] ?? '',
                $p['asistensi'] ?? '',
                $p['peer_review'] ?? '',
                $p['laporan_akhir'] ?? '',
                $p['presentasi_akhir'] ?? '',
                $p['pembimbing_lapangan'] ?? '',
                $p['nilai_akhir'] ?? '',
            ], null, 'A' . $rowNum);
            $rowNum++;
        }

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Penilaian_Akhir_Mahasiswa_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
