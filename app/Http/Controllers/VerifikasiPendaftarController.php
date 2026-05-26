<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupCpmkRubric;
use App\Models\KknPendaftar;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerifikasiPendaftarController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'semua');
        $search = trim((string) $request->get('q', ''));

        $query = Group::with(['dosen', 'groupLeader', 'cpmkRubric'])
            ->orderByDesc('created_at');

        $query = match ($filter) {
            'menunggu_dosen' => $query->whereNull('supervisor_approved_at')
                ->where(function ($q) {
                    $q->whereNull('proposal_review_status')
                        ->orWhere('proposal_review_status', '!=', 'rejected');
                })
                ->where('status', '!=', 'rejected'),
            'perlu_diverifikasi' => $query->whereNotNull('supervisor_approved_at')
                ->where('progress_verifikasi', '<', 100),
            'terverifikasi' => $query->where('progress_verifikasi', '>=', 100),
            default => $query,
        };

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelompok', 'like', "%{$search}%")
                    ->orWhere('judul_kegiatan', 'like', "%{$search}%")
                    ->orWhere('nama_mitra', 'like', "%{$search}%")
                    ->orWhere('lokasi_mitra', 'like', "%{$search}%")
                    ->orWhereHas('dosen', fn ($d) => $d->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('groupLeader', fn ($l) => $l->where('name', 'like', "%{$search}%"));
            });
        }

        $groups = $query->get();

        $leaderNims = $groups->map(fn ($g) => $g->leaderNim())->filter()->unique()->values();
        $proposals = Proposal::whereIn('user_nim', $leaderNims)
            ->orderByDesc('created_at')
            ->get()
            ->unique('user_nim')
            ->keyBy('user_nim');

        $rows = $groups->map(function (Group $group) use ($proposals) {
            $nim = $group->leaderNim();
            $proposal = $nim ? $proposals->get($nim) : null;
            $rubric = $group->cpmkRubric;

            return [
                'group' => $group,
                'proposal' => $proposal,
                'ketua' => $group->groupLeader?->name ?? '-',
                'dosen_status' => $this->dosenVerificationStatus($group),
                'penciri_status' => $group->isTimPenciriVerified() ? 'disetujui' : 'menunggu',
                'skor' => $rubric && $rubric->hasSkor() ? $rubric->rubrik_total : null,
                'can_verify' => $group->isSupervisorApproved() && !$group->isTimPenciriVerified(),
            ];
        });

        return view('verifikasi-pendaftar.index', [
            'rows' => $rows,
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    public function show(Group $group)
    {
        $isVerified = $group->isTimPenciriVerified();

        if (!$isVerified && !$group->isSupervisorApproved()) {
            return redirect()
                ->route('special-pages.pendaftar')
                ->with('error', 'Kelompok belum disetujui dosen pembimbing.');
        }

        $group->load([
            'dosen',
            'groupLeader',
            'members' => fn ($q) => $q->where('status', 'active')->with('mahasiswa'),
            'cpmkRubric',
        ]);

        $members = $group->members
            ->sortBy(fn ($m) => $m->isLeader() ? 0 : 1)
            ->values();

        $leaderNim = $group->leaderNim();
        $proposal = $leaderNim
            ? Proposal::where('user_nim', $leaderNim)->orderByDesc('created_at')->first()
            : null;

        $proposalSizeKb = null;
        if ($proposal?->file_name) {
            $path = storage_path('app/public/proposal-files/' . $proposal->file_name);
            if (is_file($path)) {
                $proposalSizeKb = (int) round(filesize($path) / 1024);
            }
        }

        $rubric = $group->cpmkRubric ?? new GroupCpmkRubric(['group_id' => $group->id]);

        return view('verifikasi-pendaftar.show', [
            'group' => $group,
            'members' => $members,
            'proposal' => $proposal,
            'proposalSizeKb' => $proposalSizeKb,
            'proposalDisplayName' => $proposal?->file_name
                ? 'Proposal_' . ($group->nama_kelompok ?? 'Kelompok') . '.pdf'
                : null,
            'rubric' => $rubric,
            'rubrikTotal' => $rubric->hasSkor() ? $rubric->rubrik_total : 0,
            'isVerified' => $isVerified,
            'canEditSkor' => !$isVerified && $group->isSupervisorApproved(),
            'dosenStatus' => $this->dosenVerificationStatus($group),
        ]);
    }

    public function verify(Request $request, Group $group)
    {
        if (!$group->isSupervisorApproved()) {
            return redirect()->back()->with('error', 'Kelompok belum disetujui dosen pembimbing.');
        }

        $validated = $request->validate([
            'skor_p5' => 'required|numeric|min:0|max:100',
            'skor_c3' => 'required|numeric|min:0|max:100',
            'skor_a2' => 'required|numeric|min:0|max:100',
        ]);

        $rubric = GroupCpmkRubric::firstOrNew(['group_id' => $group->id]);

        DB::transaction(function () use ($group, $rubric, $validated) {
            $rubric->fill([
                'skor_p5' => $validated['skor_p5'],
                'skor_c3' => $validated['skor_c3'],
                'skor_a2' => $validated['skor_a2'],
                'skor_filled_by' => Auth::id(),
            ]);
            $rubric->save();

            $group->update(['progress_verifikasi' => 100]);

            $nim = $group->leaderNim();
            if ($nim) {
                KknPendaftar::where('user_nim', $nim)->update([
                    'status_verifikasi' => 'diterima',
                    'tanggal_verifikasi' => now(),
                ]);
            }
        });

        return redirect()
            ->route('special-pages.pendaftar')
            ->with('success', 'Kelompok "' . ($group->nama_kelompok ?? 'KKN') . '" berhasil diverifikasi.');
    }

    private function dosenVerificationStatus(Group $group): string
    {
        if ($group->proposal_review_status === 'rejected' || $group->status === 'rejected') {
            return 'ditolak';
        }

        if ($group->isSupervisorApproved()) {
            return 'disetujui';
        }

        return 'menunggu';
    }
}
