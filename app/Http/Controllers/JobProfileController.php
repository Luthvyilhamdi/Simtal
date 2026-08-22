<?php

namespace App\Http\Controllers;

use App\Models\JobProfile;
use App\Models\StrukturOrganisasiVersi;
use App\Services\SnapshotDfsOrderer;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobProfileController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $versiList = StrukturOrganisasiVersi::where('status', 'final')
            ->withCount('unitOrganisasiSnapshots')
            ->orderByDesc('tanggal_mulai_berlaku')
            ->get();

        // COUNT DISTINCT unit_organisasi_id yg punya minimal 1 job_profile, per versi.
        $unitWithProfileCountByVersi = JobProfile::whereIn('struktur_organisasi_versi_id', $versiList->pluck('id'))
            ->select('struktur_organisasi_versi_id', 'unit_organisasi_id')
            ->distinct()
            ->get()
            ->groupBy('struktur_organisasi_versi_id')
            ->map->count();

        return view('organisasi.job-profile.index', compact('versiList', 'unitWithProfileCountByVersi'));
    }

    public function show(StrukturOrganisasiVersi $versi)
    {
        if (!$versi->isFinal()) {
            return redirect()->route('organisasi.job-profile.index')
                ->with('error', 'Job Profile hanya tersedia untuk versi struktur organisasi yang sudah final.');
        }

        [$unitsOrdered, $profiles, $totalUnit, $unitWithProfileCount] = $this->loadUnitsAndProfiles($versi);

        // Filter Direktorat/Kompartemen/Departemen (halaman View saja, TIDAK dipakai edit())
        // — utk tiap unit, cari nama leluhur (atau dirinya sendiri) yg levelnya PERSIS
        // 'direktorat'/'kompartemen'/'departemen' dgn jalan ke atas rantai parent_unit_
        // organisasi_id. null kalau unit itu tidak punya leluhur di level tsb (mis. unit
        // level 'kompartemen' tidak punya leluhur 'departemen', krn departemen ada di
        // BAWAH kompartemen bukan di atasnya; atau "Hukum" yg departemen tapi langsung
        // di bawah Direktorat, tidak punya leluhur 'kompartemen' sama sekali).
        $byId = collect($unitsOrdered)->pluck('node')->keyBy('unit_organisasi_id');
        $ancestorByUnitId = [];
        foreach ($byId as $unit) {
            $ancestorByUnitId[$unit->unit_organisasi_id] = [
                'direktorat'  => $this->findAncestorNameAtLevel($unit, 'direktorat', $byId),
                'kompartemen' => $this->findAncestorNameAtLevel($unit, 'kompartemen', $byId),
                'departemen'  => $this->findAncestorNameAtLevel($unit, 'departemen', $byId),
            ];
        }

        $direktoratOptions  = $byId->where('level', 'direktorat')->pluck('nama_unit')->unique()->sort()->values();
        $kompartemenOptions = $byId->where('level', 'kompartemen')->pluck('nama_unit')->unique()->sort()->values();
        $departemenOptions  = $byId->where('level', 'departemen')->pluck('nama_unit')->unique()->sort()->values();

        return view('organisasi.job-profile.show', compact(
            'versi', 'unitsOrdered', 'profiles', 'totalUnit', 'unitWithProfileCount',
            'ancestorByUnitId', 'direktoratOptions', 'kompartemenOptions', 'departemenOptions'
        ));
    }

    /**
     * Jalan ke atas rantai parent_unit_organisasi_id dari $unit, cari node pertama yg
     * level-nya PERSIS $targetLevel (termasuk $unit itu sendiri kalau levelnya sudah
     * cocok). null kalau rantai habis (sampai root) tanpa ketemu.
     */
    private function findAncestorNameAtLevel($unit, string $targetLevel, $byId): ?string
    {
        $current = $unit;
        while ($current !== null) {
            if ($current->level === $targetLevel) {
                return $current->nama_unit;
            }
            $current = $current->parent_unit_organisasi_id ? $byId->get($current->parent_unit_organisasi_id) : null;
        }
        return null;
    }

    /**
     * Halaman kelola (tambah/ganti/hapus) — data sumber SAMA PERSIS dgn show() (reuse
     * loadUnitsAndProfiles(), 0 query tambahan), cuma view & aksi simpannya beda: batch
     * lewat storeBatch(), bukan store() satu-satu lagi.
     */
    public function edit(StrukturOrganisasiVersi $versi)
    {
        if (!$versi->isFinal()) {
            return redirect()->route('organisasi.job-profile.index')
                ->with('error', 'Job Profile hanya tersedia untuk versi struktur organisasi yang sudah final.');
        }

        [$unitsOrdered, $profiles, $totalUnit, $unitWithProfileCount] = $this->loadUnitsAndProfiles($versi);

        return view('organisasi.job-profile.edit', compact(
            'versi', 'unitsOrdered', 'profiles', 'totalUnit', 'unitWithProfileCount'
        ));
    }

    /**
     * @return array{0: array, 1: \Illuminate\Support\Collection, 2: int, 3: int}
     */
    private function loadUnitsAndProfiles(StrukturOrganisasiVersi $versi): array
    {
        $units = $versi->unitOrganisasiSnapshots()->get();

        // Urutan sama seperti Detail Versi (DFS top-down), reuse SnapshotDfsOrderer.
        $unitsOrdered = SnapshotDfsOrderer::order($units);

        $profiles = JobProfile::where('struktur_organisasi_versi_id', $versi->id)
            ->orderBy('nama_jabatan')
            ->get()
            ->groupBy('unit_organisasi_id');

        $totalUnit            = $units->count();
        $unitWithProfileCount = $profiles->count();

        return [$unitsOrdered, $profiles, $totalUnit, $unitWithProfileCount];
    }

    public function store(Request $request, StrukturOrganisasiVersi $versi)
    {
        if (!$versi->isFinal()) {
            return back()->with('error', 'Job Profile hanya tersedia untuk versi struktur organisasi yang sudah final.');
        }

        $data = $request->validate([
            'unit_organisasi_id' => 'required|integer',
            'nama_jabatan'        => 'required|string|max:255',
            'file'                => 'required|file|mimes:pdf,doc,docx|max:10240',
            'keterangan'          => 'nullable|string',
        ]);

        $unitSnapshot = $versi->unitOrganisasiSnapshots()
            ->where('unit_organisasi_id', $data['unit_organisasi_id'])
            ->first();

        if (!$unitSnapshot) {
            return back()->withInput()->with('error', 'Unit organisasi tidak ditemukan pada versi ini.');
        }

        $file = $request->file('file');
        $dir      = "job_profiles/{$versi->id}";
        $filename = $data['unit_organisasi_id'] . '_' . Str::slug($data['nama_jabatan']) . '.' . $file->getClientOriginalExtension();

        DB::transaction(function () use ($data, $versi, $file, $dir, $filename, $unitSnapshot) {
            $existing = JobProfile::where('unit_organisasi_id', $data['unit_organisasi_id'])
                ->where('struktur_organisasi_versi_id', $versi->id)
                ->where('nama_jabatan', $data['nama_jabatan'])
                ->first();

            // Replace: hapus file lama dulu supaya tidak menumpuk file orphan di storage.
            if ($existing && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
                Storage::disk('public')->delete($existing->file_path);
            }

            $storedPath = Storage::disk('public')->putFileAs($dir, $file, $filename);

            JobProfile::updateOrCreate(
                [
                    'unit_organisasi_id'           => $data['unit_organisasi_id'],
                    'struktur_organisasi_versi_id' => $versi->id,
                    'nama_jabatan'                  => $data['nama_jabatan'],
                ],
                [
                    'file_path'           => $storedPath,
                    'file_original_name' => $file->getClientOriginalName(),
                    'keterangan'          => $data['keterangan'] ?? null,
                ]
            );

            $aksi   = $existing ? 'ubah' : 'tambah';
            $target = $unitSnapshot->nama_unit . ' — ' . $data['nama_jabatan'];
            $desc   = ($existing ? 'Ganti file Job Profile' : 'Tambah Job Profile baru')
                . " untuk unit \"{$unitSnapshot->nama_unit}\" posisi \"{$data['nama_jabatan']}\" (SK {$versi->nomor_sk})";

            $this->log($aksi, 'Job Profile', $target, $desc);
        });

        return redirect()->route('organisasi.job-profile.show', $versi)
            ->with('success', 'Job Profile berhasil disimpan.');
    }

    /**
     * Simpan banyak entry Job Profile sekaligus (halaman Kelola — tombol "Simpan Semua
     * Perubahan"). Cuma entry yg field "file"-nya benar2 terisi yg diproses — card yg
     * tidak disentuh user diabaikan (juga tidak pernah ikut ke-submit sama sekali di sisi
     * HTML/JS, lihat edit.blade.php: field non-file per slot "disabled" sampai file itu
     * dipilih, spy tidak membengkak jadi ribuan field kosong di 1 request).
     *
     * ALL-OR-NOTHING: seluruh entry divalidasi (struktur + unit harus ada di snapshot versi
     * ini) SEBELUM ada file/DB yg ditulis sama sekali — kalau 1 entry saja tidak valid,
     * batch dibatalkan total (redirect balik dgn pesan error entry mana yg bermasalah,
     * tanpa ada yg tersimpan). Ini konsisten dgn pola project: 1 aksi user = 1 hasil bersih
     * (store()/destroy() single-row juga each-or-nothing lewat DB::transaction()) —
     * partial-success utk 1 klik tombol "Simpan Semua" akan bikin user bingung mana yg
     * kesimpan mana yg tidak, jadi all-or-nothing lebih aman & lebih gampang direset user
     * (tinggal submit ulang) drpd partial state yg susah dilacak.
     */
    public function storeBatch(Request $request, StrukturOrganisasiVersi $versi)
    {
        if (!$versi->isFinal()) {
            return back()->with('error', 'Job Profile hanya tersedia untuk versi struktur organisasi yang sudah final.');
        }

        $request->validate([
            'entries'                    => 'required|array|min:1',
            'entries.*.unit_organisasi_id' => 'required|integer',
            'entries.*.nama_jabatan'       => 'nullable|string|max:255',
            'entries.*.file'               => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'entries.*.keterangan'         => 'nullable|string',
        ]);

        // Hanya entry yg field file-nya benar2 diisi yg diproses (lihat catatan class doc).
        $keysToProcess = collect($request->input('entries', []))
            ->keys()
            ->filter(fn ($key) => $request->hasFile("entries.{$key}.file"))
            ->values();

        if ($keysToProcess->isEmpty()) {
            return back()->with('error', 'Tidak ada perubahan yang dipilih untuk disimpan.');
        }

        $snapshotByUnitId = $versi->unitOrganisasiSnapshots()->get()->keyBy('unit_organisasi_id');

        // Validasi SEMUA entry dulu SEBELUM ada satu pun file/DB yg ditulis (all-or-nothing).
        $errors = [];
        foreach ($keysToProcess as $key) {
            $entry   = $request->input("entries.{$key}");
            $unitId  = $entry['unit_organisasi_id'] ?? null;
            $jabatan = trim((string) ($entry['nama_jabatan'] ?? ''));

            if ($jabatan === '') {
                $errors[] = "Unit #{$unitId}: nama jabatan wajib diisi kalau file dipilih.";
                continue;
            }
            if (!$snapshotByUnitId->has((int) $unitId)) {
                $errors[] = "Unit #{$unitId}: tidak ditemukan pada versi ini.";
            }
        }

        if (!empty($errors)) {
            return back()->withInput()->with('error', 'Batch dibatalkan, ada entry tidak valid: ' . implode(' | ', $errors));
        }

        $savedCount = 0;
        $unitNames  = [];

        DB::transaction(function () use ($request, $keysToProcess, $versi, $snapshotByUnitId, &$savedCount, &$unitNames) {
            foreach ($keysToProcess as $key) {
                $entry      = $request->input("entries.{$key}");
                $file       = $request->file("entries.{$key}.file");
                $unitId     = (int) $entry['unit_organisasi_id'];
                $jabatan    = trim($entry['nama_jabatan']);
                $keterangan = $entry['keterangan'] ?? null;
                $unitSnapshot = $snapshotByUnitId->get($unitId);

                $dir      = "job_profiles/{$versi->id}";
                $filename = $unitId . '_' . Str::slug($jabatan) . '.' . $file->getClientOriginalExtension();

                $existing = JobProfile::where('unit_organisasi_id', $unitId)
                    ->where('struktur_organisasi_versi_id', $versi->id)
                    ->where('nama_jabatan', $jabatan)
                    ->first();

                if ($existing && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
                    Storage::disk('public')->delete($existing->file_path);
                }

                $storedPath = Storage::disk('public')->putFileAs($dir, $file, $filename);

                JobProfile::updateOrCreate(
                    [
                        'unit_organisasi_id'           => $unitId,
                        'struktur_organisasi_versi_id' => $versi->id,
                        'nama_jabatan'                  => $jabatan,
                    ],
                    [
                        'file_path'           => $storedPath,
                        'file_original_name' => $file->getClientOriginalName(),
                        'keterangan'          => $keterangan,
                    ]
                );

                $savedCount++;
                $unitNames[$unitSnapshot->nama_unit] = true;
            }

            $this->log('import', 'Job Profile', "Batch {$versi->nomor_sk}",
                "Simpan batch Job Profile: {$savedCount} entry disimpan untuk " . count($unitNames) . " unit berbeda (SK {$versi->nomor_sk}).");
        });

        return redirect()->route('organisasi.job-profile.edit', $versi)
            ->with('success', "{$savedCount} Job Profile berhasil disimpan.");
    }

    public function destroy(JobProfile $jobProfile)
    {
        $versi        = $jobProfile->strukturOrganisasiVersi;
        $unitSnapshot = $versi->unitOrganisasiSnapshots()
            ->where('unit_organisasi_id', $jobProfile->unit_organisasi_id)
            ->first();
        $namaUnit = $unitSnapshot->nama_unit ?? "Unit #{$jobProfile->unit_organisasi_id}";

        DB::transaction(function () use ($jobProfile, $versi, $namaUnit) {
            if ($jobProfile->file_path && Storage::disk('public')->exists($jobProfile->file_path)) {
                Storage::disk('public')->delete($jobProfile->file_path);
            }

            $namaJabatan = $jobProfile->nama_jabatan;
            $jobProfile->delete();

            $this->log('hapus', 'Job Profile', $namaUnit . ' — ' . $namaJabatan,
                "Hapus Job Profile untuk unit \"{$namaUnit}\" posisi \"{$namaJabatan}\" (SK {$versi->nomor_sk})");
        });

        return redirect()->route('organisasi.job-profile.show', $versi)
            ->with('success', 'Job Profile berhasil dihapus.');
    }
}
