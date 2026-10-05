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

    /** Data bersama form Tambah & Edit (combobox + master select). */
    private function formData(Karyawan $karyawan): array
    {
        // Saran hanya dari master data; isian bebas tetap bisa diketik.
        $namaDirektorat  = $this->opsiMaster(Direktorat::pluck('nama_direktorat'));
        $namaKompartemen = $this->opsiMaster(Kompartemen::pluck('nama_kompartemen'));
        $namaDepartemen  = $this->opsiMaster(Departemen::pluck('nama_departemen'));

        return [
            'karyawan'        => $karyawan,
            'namaDirektorat'  => $namaDirektorat,
            'namaKompartemen' => $namaKompartemen,
            'namaDepartemen'  => $namaDepartemen,
            'namaJobGrade'    => $this->gradeOptions(JobGrade::pluck('job_grade')),
            'namaPersonGrade' => $this->gradeOptions(PersonGrade::pluck('person_grade')),
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
        ], [
            'tipe.required' => 'Tipe perubahan jabatan belum dipilih.',
            'tipe.in'       => 'Tipe perubahan jabatan tidak dikenali.',
        ]);

        DB::transaction(function () use ($request, $karyawan) {

            // Snapshot nama unit + resolve FK bila cocok master.
            $dirNama  = trim($request->direktorat);
            $kompNama = trim($request->kompartemen);
            $depNama  = trim($request->departemen);
            $dirId  = Direktorat::where('nama_direktorat', $dirNama)->value('id');
            $kompId = Kompartemen::where('nama_kompartemen', $kompNama)->value('id');
            $depId  = Departemen::where('nama_departemen', $depNama)->value('id');

            // JG & PG: snapshot teks + resolve FK master.
            $jgNama = trim($request->job_grade);
            $pgNama = trim($request->person_grade);
            $jgId = JobGrade::where('job_grade', $jgNama)->value('id');
            $pgId = PersonGrade::where('person_grade', $pgNama)->value('id');

            // Simpan JG & PG lama sebelum update (band-date sebelum event sync jalan)
            $jgLama = $karyawan->job_grade_id;
            $pgLama = $karyawan->person_grade_id;
            $bandDateSebelum = $karyawan->tanggal_mulai_band ?? $karyawan->tanggal_mulai_jg;

            // Tutup history lama di H-1 TMT jabatan baru.
            $akhirJabatanLama = \Carbon\Carbon::parse($request->tanggal_mulai)->subDay();

            HistoryJabatan::where('karyawan_id', $karyawan->id)
                ->where('is_current', true)
                ->update([
                    'is_current'      => false,
                    'tanggal_selesai' => $akhirJabatanLama,
                ]);

            // Buat history baru
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

            // Update profil karyawan (?? agar FK lama tidak ter-null).
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

            $karyawan->update($updateData);

            $this->terapkanTmt($karyawan, $jgLama, $jgId, $pgLama, $pgId,
                $bandDateSebelum, $request->tanggal_mulai);
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
        ], [
            'tipe.required' => 'Tipe perubahan jabatan belum dipilih.',
            'tipe.in'       => 'Tipe perubahan jabatan tidak dikenali.',
        ]);

        DB::transaction(function () use ($request, $karyawan, $historyJabatan) {
            // Snapshot teks + resolve FK master (nama lama → FK null, master bersih)
            $dirNama  = trim($request->direktorat);
            $kompNama = trim($request->kompartemen);
            $depNama  = trim($request->departemen);
            $jgNama   = trim((string) $request->job_grade);
            $pgNama   = trim((string) $request->person_grade);

            // Keadaan grade sebelum disunting, pembanding aturan TMT.
            $jgLama = $karyawan->job_grade_id;
            $pgLama = $karyawan->person_grade_id;
            $bandDateSebelum = $karyawan->tanggal_mulai_band ?? $karyawan->tanggal_mulai_jg;

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

            // Hitung ulang: current & profil untuk karyawan ini.
            $this->recomputeKaryawan($karyawan);

            // TMT hanya digeser bila jabatan yang disunting sedang berjalan.
            $karyawan->refresh();
            if ($historyJabatan->fresh()->is_current) {
                $this->terapkanTmt($karyawan, $jgLama, $karyawan->job_grade_id,
                    $pgLama, $karyawan->person_grade_id,
                    $bandDateSebelum, $historyJabatan->tanggal_mulai);
            }
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

    /** Geser TMT JG / PG / Band setelah grade karyawan berpindah. */
    private function terapkanTmt(
        Karyawan $karyawan,
        ?int $jgLama,
        ?int $jgBaru,
        ?int $pgLama,
        ?int $pgBaru,
        $bandDateSebelum,
        $tmt
    ): void {
        $tmtBaru = [];

        if ($jgBaru !== null && $jgBaru != $jgLama) {
            $tmtBaru['tanggal_mulai_jg'] = $tmt;
        }

        if ($pgBaru !== null && $pgBaru != $pgLama) {
            $tmtBaru['tanggal_mulai_pg'] = $tmt;

            $pgLamaVal = (int) optional(PersonGrade::find($pgLama))->person_grade;
            $pgBaruVal = (int) optional(PersonGrade::find($pgBaru))->person_grade;
            if ($pgBaruVal > $pgLamaVal) {
                $tmtBaru['tanggal_mulai_jg'] = $tmt;
            }
        }

        if ($jgBaru !== null) {
            $tmtBaru['tanggal_mulai_band'] = Karyawan::tmtBandSetelahPromosi(
                (int) $jgLama, (int) $jgBaru, $bandDateSebelum, $tmt
            );
        }

        if ($tmtBaru) {
            Karyawan::where('id', $karyawan->id)->update($tmtBaru);
        }
    }

    /** Hitung ulang is_current & profil untuk satu karyawan. */
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

        // TMT Band tidak dihitung ulang di sini; lihat terapkanTmt().
    }

    /** Selaraskan Pejabat Definitif yang terhubung ke sebuah history. */
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
            // Hapus jabatan current: jabatan sebelumnya kembali berjalan.
            if ($wasCurrent) {
                $prev->tanggal_selesai = null;
                $prev->saveQuietly();
                $karyawan->tanggal_mulai_jg = $prev->tanggal_mulai;
                $karyawan->tanggal_mulai_pg = $prev->tanggal_mulai;
                $karyawan->saveQuietly();
            }

            // Hitung ulang current + profil + TMT band.
            $this->recomputeKaryawan($karyawan);
        }

        $this->log('hapus', 'History Jabatan', $karyawan->nama, 'Hapus data jabatan');

        return redirect()
            ->route('history_jabatan.index', $karyawan)
            ->with('success', 'History jabatan berhasil dihapus!');
    }

    /** Saran grade: master + nilai historis, urut terbesar ke terkecil. */
    /** Pilihan penampung, selalu di paling bawah daftar saran. */
    private const OPSI_TAMBAHAN = ['-', 'Belum Ditentukan'];

    /** Pasang ulang nilai penampung di posisi paling bawah. */
    private function tempelOpsiTambahan(\Illuminate\Support\Collection $daftar)
    {
        return $daftar
            ->reject(fn ($v) => trim($v) === '-' || preg_match('/^belum\s+ditentukan$/i', trim($v)))
            ->values()
            ->concat(self::OPSI_TAMBAHAN);
    }

    /** Daftar saran unit: master data (urut A-Z) + opsi penampung di bawahnya. */
    private function opsiMaster($masterValues)
    {
        return $this->tempelOpsiTambahan(
            collect($masterValues)->filter()->unique()->sort()
        );
    }

    /** Sama seperti opsiMaster(), tapi grade diurut dari angka terbesar. */
    private function gradeOptions($masterValues)
    {
        return $this->tempelOpsiTambahan(
            collect($masterValues)->filter()->unique()->sortByDesc(fn ($v) => (int) $v)
        );
    }

    public function export(Request $request)
    {
        $filename = 'history-jabatan-' . now()->format('d-m-Y') . '.xlsx';
        return (new HistoryJabatanExport())->download($filename);
    }
}