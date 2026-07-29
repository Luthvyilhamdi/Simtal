<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\HistoryJabatan;
use App\Models\Jabatan;
use App\Models\Direktorat;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobGrade;
use App\Models\PersonGrade;
use App\Models\KodeStruktur;
use App\Models\HistoryPejabat;
use App\Traits\LogsActivity;
use App\Exports\HistoryJabatanExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HistoryJabatanController extends Controller
{
    use LogsActivity;

    public function index(Karyawan $karyawan)
    {
        $karyawan->load(['jabatan', 'departemen', 'direktorat', 'jobGrade', 'personGrade']);
        $histories = $karyawan->historyJabatan()
            ->with(['jabatan', 'direktorat', 'kompartemen', 'departemen', 'jobGrade', 'personGrade', 'kodeStruktur'])
            ->orderBy('is_current', 'desc')   // jabatan saat ini selalu paling atas
            ->orderBy('tanggal_mulai', 'desc') // sisanya: terbaru → lama
            ->get();

        // Periode Masa Dinas Jabatan (MDJ): kelompokkan riwayat, ambil yang berjalan.
        $periodeMdj = HistoryJabatan::ringkasPeriodeMdj($histories);
        $mdjAktif   = collect($periodeMdj)->firstWhere('aktif', true);

        // Masa Dinas Grade (Person Grade): dari tanggal_mulai_pg → sekarang (thn/bln/hari).
        $mdgPg = null;
        if ($karyawan->tanggal_mulai_pg) {
            $d = $karyawan->tanggal_mulai_pg->diff(now());
            $mdgPg = [
                'mulai' => $karyawan->tanggal_mulai_pg,
                'grade' => optional($karyawan->personGrade)->person_grade,
                'tahun' => $d->y,
                'bulan' => $d->m,
                'hari'  => $d->d,
            ];
        }

        return view('history_jabatan.index', compact('karyawan', 'histories', 'periodeMdj', 'mdjAktif', 'mdgPg'));
    }

    public function create(Karyawan $karyawan)
    {
        return view('history_jabatan.create', $this->formData($karyawan));
    }

    public function edit(Karyawan $karyawan, HistoryJabatan $historyJabatan)
    {
        abort_unless((int) $historyJabatan->karyawan_id === (int) $karyawan->id, 404);

        return view('history_jabatan.edit', $this->formData($karyawan) + [
            'historyJabatan' => $historyJabatan,
        ]);
    }

    /**
     * Data bersama untuk form Tambah & Edit (daftar combobox + master select).
     * Saran combobox = nama master (struktur kini) ∪ nama historis yang pernah
     * dipakai, agar nama lama tinggal dipilih ulang (anti-typo).
     */
    private function formData(Karyawan $karyawan): array
    {
        $namaDirektorat = Direktorat::pluck('nama_direktorat')
            ->merge(HistoryJabatan::whereNotNull('direktorat_nama')->distinct()->pluck('direktorat_nama'))
            ->filter()->unique()->sort()->values();
        $namaKompartemen = Kompartemen::pluck('nama_kompartemen')
            ->merge(HistoryJabatan::whereNotNull('kompartemen_nama')->distinct()->pluck('kompartemen_nama'))
            ->filter()->unique()->sort()->values();
        $namaDepartemen = Departemen::pluck('nama_departemen')
            ->merge(HistoryJabatan::whereNotNull('departemen_nama')->distinct()->pluck('departemen_nama'))
            ->filter()->unique()->sort()->values();

        return [
            'karyawan'        => $karyawan,
            'namaDirektorat'  => $namaDirektorat,
            'namaKompartemen' => $namaKompartemen,
            'namaDepartemen'  => $namaDepartemen,
            'namaJobGrade'    => $this->gradeOptions(JobGrade::pluck('job_grade'), 'job_grade_nama'),
            'namaPersonGrade' => $this->gradeOptions(PersonGrade::pluck('person_grade'), 'person_grade_nama'),
            'jabatans'        => Jabatan::all(),
            'kodeStrukturs'   => KodeStruktur::all(),
        ];
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'jabatan_id'       => 'required',
            'direktorat'       => 'required|string|max:255',
            'kompartemen'      => 'required|string|max:255',
            'departemen'       => 'required|string|max:255',
            'job_grade'        => 'required|string|max:50',
            'person_grade'     => 'required|string|max:50',
            'kode_struktur_id' => 'required',
            'tanggal_mulai'    => 'required|date',
            'tanggal_selesai'  => 'nullable|date|after:tanggal_mulai',
            'tipe'             => 'required|in:mutasi,rotasi,promosi,demosi,penempatan',
            'keterangan'       => 'nullable|string',
            'no_sk'            => 'nullable|string',
            'tanggal_sk'       => 'nullable|date',
            'jabatan_saat_ini' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $karyawan) {

            // Snapshot nama unit (apa adanya) + resolve FK bila cocok master.
            // Nama historis yang tak ada di master → FK null, master tak dikotori.
            $dirNama  = trim($request->direktorat);
            $kompNama = trim($request->kompartemen);
            $depNama  = trim($request->departemen);
            $dirId  = Direktorat::where('nama_direktorat', $dirNama)->value('id');
            $kompId = Kompartemen::where('nama_kompartemen', $kompNama)->value('id');
            $depId  = Departemen::where('nama_departemen', $depNama)->value('id');

            // JG & PG: snapshot teks + resolve FK master (untuk hitungan band/MDG).
            // Grade lama yang tak ada di master → FK null, hitungan band dilewati.
            $jgNama = trim($request->job_grade);
            $pgNama = trim($request->person_grade);
            $jgId = JobGrade::where('job_grade', $jgNama)->value('id');
            $pgId = PersonGrade::where('person_grade', $pgNama)->value('id');

            // Simpan JG & PG lama sebelum update (band-date sebelum event sync jalan)
            $jgLama = $karyawan->job_grade_id;
            $pgLama = $karyawan->person_grade_id;
            $bandDateSebelum = $karyawan->tanggal_mulai_band ?? $karyawan->tanggal_mulai_jg;

            // Tutup history lama
            HistoryJabatan::where('karyawan_id', $karyawan->id)
                ->where('is_current', true)
                ->update([
                    'is_current'      => false,
                    'tanggal_selesai' => $request->tanggal_mulai,
                ]);

            // Buat history baru
            // (Sinkronisasi ke Pejabat Definitif ditangani otomatis oleh event model HistoryJabatan)
            HistoryJabatan::create([
                'karyawan_id'      => $karyawan->id,
                'jabatan_id'       => $request->jabatan_id,
                'jabatan_saat_ini' => $request->jabatan_saat_ini,
                'direktorat_id'    => $dirId,
                'kompartemen_id'   => $kompId,
                'departemen_id'    => $depId,
                'direktorat_nama'  => $dirNama,
                'kompartemen_nama' => $kompNama,
                'departemen_nama'  => $depNama,
                'job_grade_id'     => $jgId,
                'person_grade_id'  => $pgId,
                'job_grade_nama'   => $jgNama,
                'person_grade_nama'=> $pgNama,
                'kode_struktur_id' => $request->kode_struktur_id,
                'tanggal_mulai'    => $request->tanggal_mulai,
                'tanggal_selesai'  => $request->tanggal_selesai ?: null,
                'tipe'             => $request->tipe,
                'keterangan'       => $request->keterangan ?: null,
                'no_sk'            => $request->no_sk ?: null,
                'tanggal_sk'       => $request->tanggal_sk ?: null,
                'is_current'       => true,
                'lanjut_mdj'       => $request->boolean('lanjut_mdj'),
            ]);

            // Update profil karyawan
            // Profil (posisi terkini) tetap mengacu master. Bila nama tak cocok
            // master (nama historis), pertahankan FK profil yang ada — jangan
            // set null agar profil aktif tidak kehilangan unit.
            $updateData = [
                'jabatan_id'       => $request->jabatan_id,
                'direktorat_id'    => $dirId  ?? $karyawan->direktorat_id,
                'kompartemen_id'   => $kompId ?? $karyawan->kompartemen_id,
                'departemen_id'    => $depId  ?? $karyawan->departemen_id,
                'job_grade_id'     => $jgId ?? $karyawan->job_grade_id,
                'person_grade_id'  => $pgId ?? $karyawan->person_grade_id,
                'kode_struktur_id' => $request->kode_struktur_id,
                'jabatan_saat_ini' => $request->jabatan_saat_ini,
            ];

            // Auto update TMT JG jika Job Grade berubah (hanya bila grade ke-resolve master)
            if ($jgId !== null && $jgId != $jgLama) {
                $updateData['tanggal_mulai_jg'] = $request->tanggal_mulai;
            }

            // Auto update TMT PG jika Person Grade berubah (hanya bila grade ke-resolve master)
            if ($pgId !== null && $pgId != $pgLama) {
                $updateData['tanggal_mulai_pg'] = $request->tanggal_mulai;

                // Ketentuan MDG: saat Person Grade NAIK (nilai bertambah),
                // TMT Job Grade ikut di-reset. TMT JG tetap bisa diubah manual
                // lewat Edit Karyawan. Turun/tetap → TMT JG tidak diubah di sini.
                $pgLamaVal = (int) optional(PersonGrade::find($pgLama))->person_grade;
                $pgBaruVal = (int) optional(PersonGrade::find($pgId))->person_grade;
                if ($pgBaruVal > $pgLamaVal) {
                    $updateData['tanggal_mulai_jg'] = $request->tanggal_mulai;
                }
            }

            $karyawan->update($updateData);

            // TMT Band otoritatif: reset hanya saat NAIK BAND; band sama/turun →
            // dipertahankan. Query builder agar menang atas event syncTanggalMulaiBand.
            // Dilewati bila JG tak ke-resolve master (grade historis).
            if ($jgId !== null) {
                Karyawan::where('id', $karyawan->id)->update([
                    'tanggal_mulai_band' => Karyawan::tmtBandSetelahPromosi(
                        (int) $jgLama, (int) $jgId, $bandDateSebelum, $request->tanggal_mulai
                    ),
                ]);
            }
        });

        $this->log(
            'tambah',
            'History Jabatan',
            $karyawan->nama,
            ucfirst($request->tipe) . ' jabatan: ' . ($request->jabatan_saat_ini ?? '-')
        );

        return redirect()
            ->route('history_jabatan.index', $karyawan)
            ->with('success', 'History jabatan berhasil ditambahkan & profil karyawan diperbarui!');
    }

    public function update(Request $request, Karyawan $karyawan, HistoryJabatan $historyJabatan)
    {
        abort_unless((int) $historyJabatan->karyawan_id === (int) $karyawan->id, 404);

        $request->validate([
            'jabatan_id'       => 'required',
            'direktorat'       => 'required|string|max:255',
            'kompartemen'      => 'required|string|max:255',
            'departemen'       => 'required|string|max:255',
            'job_grade'        => 'required|string|max:50',
            'person_grade'     => 'required|string|max:50',
            'kode_struktur_id' => 'required',
            'tanggal_mulai'    => 'required|date',
            'tanggal_selesai'  => 'nullable|date|after:tanggal_mulai',
            'tipe'             => 'required|in:mutasi,rotasi,promosi,demosi,penempatan',
            'keterangan'       => 'nullable|string',
            'no_sk'            => 'nullable|string',
            'tanggal_sk'       => 'nullable|date',
            'jabatan_saat_ini' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $karyawan, $historyJabatan) {
            // Snapshot teks + resolve FK master (nama lama → FK null, master bersih)
            $dirNama  = trim($request->direktorat);
            $kompNama = trim($request->kompartemen);
            $depNama  = trim($request->departemen);
            $jgNama   = trim((string) $request->job_grade);
            $pgNama   = trim((string) $request->person_grade);

            $historyJabatan->update([
                'jabatan_id'        => $request->jabatan_id,
                'jabatan_saat_ini'  => $request->jabatan_saat_ini,
                'direktorat_id'     => Direktorat::where('nama_direktorat', $dirNama)->value('id'),
                'kompartemen_id'    => Kompartemen::where('nama_kompartemen', $kompNama)->value('id'),
                'departemen_id'     => Departemen::where('nama_departemen', $depNama)->value('id'),
                'direktorat_nama'   => $dirNama,
                'kompartemen_nama'  => $kompNama,
                'departemen_nama'   => $depNama,
                'job_grade_id'      => JobGrade::where('job_grade', $jgNama)->value('id'),
                'person_grade_id'   => PersonGrade::where('person_grade', $pgNama)->value('id'),
                'job_grade_nama'    => $jgNama,
                'person_grade_nama' => $pgNama,
                'kode_struktur_id'  => $request->kode_struktur_id,
                'tanggal_mulai'     => $request->tanggal_mulai,
                'tanggal_selesai'   => $request->tanggal_selesai ?: null,
                'tipe'              => $request->tipe,
                'keterangan'        => $request->keterangan ?: null,
                'no_sk'             => $request->no_sk ?: null,
                'tanggal_sk'        => $request->tanggal_sk ?: null,
                'lanjut_mdj'        => $request->boolean('lanjut_mdj'),
            ]);

            // Selaraskan Pejabat Definitif yang terhubung (event model tak jalan di update).
            $this->syncPejabatFromHistory($historyJabatan->refresh());

            // Hitung ulang: current, profil, & TMT band untuk karyawan ini.
            $this->recomputeKaryawan($karyawan);
        });

        $this->log(
            'ubah',
            'History Jabatan',
            $karyawan->nama,
            ucfirst($request->tipe) . ' jabatan: ' . ($request->jabatan_saat_ini ?? '-')
        );

        return redirect()
            ->route('history_jabatan.index', $karyawan)
            ->with('success', 'History jabatan berhasil diperbarui & profil karyawan disinkronkan!');
    }

    /**
     * Hitung ulang untuk 1 karyawan setelah edit: jabatan dengan tanggal_mulai
     * TERBARU = is_current (paling atas), sisanya non-current; profil disinkron
     * dari baris current (pakai ?? agar nilai historis FK-null tak meng-null-kan
     * profil); TMT band dihitung ulang otoritatif dari riwayat.
     */
    private function recomputeKaryawan(Karyawan $karyawan): void
    {
        $histories = HistoryJabatan::where('karyawan_id', $karyawan->id)
            ->orderBy('tanggal_mulai', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        if ($histories->isEmpty()) {
            return;
        }

        $current = $histories->first();

        foreach ($histories as $h) {
            $shouldCurrent = $h->id === $current->id;
            if ((bool) $h->is_current !== $shouldCurrent) {
                $h->is_current = $shouldCurrent;
                $h->saveQuietly();
            }
        }

        $karyawan->update([
            'jabatan_id'       => $current->jabatan_id       ?? $karyawan->jabatan_id,
            'jabatan_saat_ini' => $current->jabatan_saat_ini ?? $karyawan->jabatan_saat_ini,
            'direktorat_id'    => $current->direktorat_id    ?? $karyawan->direktorat_id,
            'kompartemen_id'   => $current->kompartemen_id   ?? $karyawan->kompartemen_id,
            'departemen_id'    => $current->departemen_id    ?? $karyawan->departemen_id,
            'job_grade_id'     => $current->job_grade_id     ?? $karyawan->job_grade_id,
            'person_grade_id'  => $current->person_grade_id  ?? $karyawan->person_grade_id,
            'kode_struktur_id' => $current->kode_struktur_id ?? $karyawan->kode_struktur_id,
        ]);

        if (Schema::hasColumn('karyawans', 'tanggal_mulai_band')) {
            $karyawan->tanggal_mulai_band = $karyawan->hitungTanggalMulaiBand();
            $karyawan->saveQuietly();
        }
    }

    /**
     * Selaraskan record Pejabat Definitif yang terhubung ke sebuah history.
     * Bukan pejabat lagi → hapus record; masih/menjadi pejabat → update/buat.
     */
    private function syncPejabatFromHistory(HistoryJabatan $h): void
    {
        $jabatan = $h->jabatan_id ? Jabatan::find($h->jabatan_id) : null;
        $tier    = HistoryPejabat::resolveTier($jabatan, $h->jabatan_saat_ini);
        $existing = HistoryPejabat::where('history_jabatan_id', $h->id)->first();

        if (!$tier) {
            $existing?->delete();
            return;
        }

        $data = [
            'karyawan_id'        => $h->karyawan_id,
            'history_jabatan_id' => $h->id,
            'jabatan'            => $tier,
            'jabatan_saat_ini'   => $h->jabatan_saat_ini,
            'direktorat'         => $h->direktorat_label,
            'kompartemen'        => $h->kompartemen_label,
            'departemen'         => $h->departemen_label,
            'job_grade'          => $h->job_grade_label,
            'person_grade'       => $h->person_grade_label,
            'no_sk'              => $h->no_sk,
            'tanggal_sk'         => $h->tanggal_sk,
            'tanggal_mulai'      => $h->tanggal_mulai,
            'tanggal_selesai'    => $h->tanggal_selesai,
            'keterangan'         => $h->keterangan,
        ];

        $existing ? $existing->update($data) : HistoryPejabat::create($data);
    }

    public function destroy(Karyawan $karyawan, HistoryJabatan $historyJabatan)
    {
        $wasCurrent = $historyJabatan->is_current;

        // Hapus history (record Pejabat Definitif terkait ikut terhapus otomatis via event model)
        $historyJabatan->delete();

        $prev = HistoryJabatan::where('karyawan_id', $karyawan->id)
            ->orderBy('tanggal_mulai', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($prev) {
            // Hapus jabatan CURRENT (mis. batalkan promosi) → jabatan sebelumnya
            // kembali berjalan (tanggal_selesai null) & TMT JG/PG kembali ke
            // tanggal mulainya (TMT band dihitung ulang di bawah).
            if ($wasCurrent) {
                $prev->tanggal_selesai = null;
                $prev->saveQuietly();
                $karyawan->tanggal_mulai_jg = $prev->tanggal_mulai;
                $karyawan->tanggal_mulai_pg = $prev->tanggal_mulai;
                $karyawan->saveQuietly();
            }

            // Hitung ulang current + profil (termasuk jabatan_saat_ini, unit, JG/PG)
            // + TMT band otoritatif. Sama seperti alur edit → konsisten.
            $this->recomputeKaryawan($karyawan);
        }

        $this->log('hapus', 'History Jabatan', $karyawan->nama, 'Hapus data jabatan');

        return redirect()
            ->route('history_jabatan.index', $karyawan)
            ->with('success', 'History jabatan berhasil dihapus!');
    }

    /**
     * Saran combobox grade: gabungan nilai master + nilai historis yang pernah
     * dipakai, diurutkan dari yang TERBESAR ke terkecil (numerik).
     */
    private function gradeOptions($masterValues, string $historyColumn)
    {
        return collect($masterValues)
            ->merge(HistoryJabatan::whereNotNull($historyColumn)->distinct()->pluck($historyColumn))
            ->filter()
            ->unique()
            ->sortByDesc(fn ($v) => (int) $v)
            ->values();
    }

    public function export(Request $request)
    {
        $filename = 'history-jabatan-' . now()->format('d-m-Y') . '.xlsx';
        return (new HistoryJabatanExport())->download($filename);
    }
}