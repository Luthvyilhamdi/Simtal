<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryJabatan extends Model
{
    protected $fillable = [
        'karyawan_id', 'jabatan_id', 'jabatan_saat_ini', 'direktorat_id',
        'kompartemen_id', 'departemen_id',
        'direktorat_nama', 'kompartemen_nama', 'departemen_nama',
        'job_grade_id',
        'person_grade_id', 'kode_struktur_id',
        'job_grade_nama', 'person_grade_nama',
        'tanggal_mulai', 'tanggal_selesai',
        'tipe', 'keterangan',
        'no_sk', 'tanggal_sk',
        'is_current', 'lanjut_mdj',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_sk'      => 'date',
        'is_current'      => 'boolean',
        'lanjut_mdj'      => 'boolean',
    ];

    public function karyawan()    { return $this->belongsTo(Karyawan::class); }
    public function jabatan()     { return $this->belongsTo(Jabatan::class); }
    public function direktorat()  { return $this->belongsTo(Direktorat::class); }
    public function kompartemen() { return $this->belongsTo(Kompartemen::class); }
    public function departemen()  { return $this->belongsTo(Departemen::class); }
    public function jobGrade()    { return $this->belongsTo(JobGrade::class); }
    public function personGrade() { return $this->belongsTo(PersonGrade::class); }
    public function kodeStruktur(){ return $this->belongsTo(KodeStruktur::class); }

    /**
     * Label unit untuk ditampilkan: utamakan snapshot teks (nama saat itu),
     * fallback ke master via FK bila kolom teks kosong (data lama pra-migrasi).
     */
    public function getDirektoratLabelAttribute(): ?string
    {
        return filled($this->direktorat_nama) ? $this->direktorat_nama : optional($this->direktorat)->nama_direktorat;
    }

    public function getKompartemenLabelAttribute(): ?string
    {
        return filled($this->kompartemen_nama) ? $this->kompartemen_nama : optional($this->kompartemen)->nama_kompartemen;
    }

    public function getDepartemenLabelAttribute(): ?string
    {
        return filled($this->departemen_nama) ? $this->departemen_nama : optional($this->departemen)->nama_departemen;
    }

    public function getJobGradeLabelAttribute(): ?string
    {
        return filled($this->job_grade_nama) ? $this->job_grade_nama : optional($this->jobGrade)->job_grade;
    }

    public function getPersonGradeLabelAttribute(): ?string
    {
        return filled($this->person_grade_nama) ? $this->person_grade_nama : optional($this->personGrade)->person_grade;
    }

    /**
     * Kelompokkan riwayat jabatan menjadi PERIODE Masa Dinas Jabatan (MDJ).
     * Satu periode = deretan entri berurutan (tanggal_mulai naik) di mana entri
     * lanjutan ditandai lanjut_mdj = true ("jabatan sama dengan sebelumnya",
     * mis. hanya ganti nama karena SO). Entri lanjut_mdj = false memulai periode
     * baru (jabatan baru / promosi / pindah band / beda ranah).
     *
     * @param  iterable $histories  koleksi HistoryJabatan (urutan bebas)
     * @return array<int,array>     tiap periode: mulai/selesai/aktif/jabatan/bulan/label/count
     */
    public static function ringkasPeriodeMdj($histories): array
    {
        $rows = collect($histories)
            ->sortBy(fn ($r) => optional($r->tanggal_mulai)->timestamp . '-' . str_pad((string) $r->id, 12, '0', STR_PAD_LEFT))
            ->values();

        $periods  = [];
        $prevBand = null; // band terakhir yang diketahui
        foreach ($rows as $row) {
            $band = self::bandDariRow($row);
            // Pindah BAND = reset otomatis (menang atas centang "sama"): band beda
            // pasti jabatan/level baru, walau namanya kebetulan mirip.
            $bandBerubah = ($prevBand !== null && $band !== null && $band !== $prevBand);

            if (!empty($periods) && $row->lanjut_mdj && !$bandBerubah) {
                $periods[count($periods) - 1][] = $row;
            } else {
                $periods[] = [$row];
            }

            if ($band !== null) {
                $prevBand = $band;
            }
        }

        return collect($periods)->map(function ($group) {
            $g       = collect($group);
            $first   = $g->first();
            $last    = $g->last();
            $mulai   = $first->tanggal_mulai;
            $aktif   = $g->contains(fn ($r) => $r->is_current);
            $selesai = $aktif ? null : $last->tanggal_selesai;
            $akhir   = $selesai ?? now();
            $bulan   = $mulai ? (int) $mulai->copy()->startOfDay()->diffInMonths($akhir->copy()->startOfDay()) : 0;
            $diff    = $mulai ? $mulai->copy()->startOfDay()->diff($akhir->copy()->startOfDay()) : null;

            return [
                'mulai'   => $mulai,
                'selesai' => $selesai,
                'aktif'   => $aktif,
                'jabatan' => $g->pluck('jabatan_saat_ini')->filter()->unique()->values()->all(),
                'bulan'   => $bulan,
                'label'   => self::formatDurasiBulan($bulan),
                'tahun'   => $diff?->y ?? 0,
                'sisa_bulan' => $diff?->m ?? 0,
                'hari'    => $diff?->d ?? 0,
                'count'   => $g->count(),
            ];
        })->all();
    }

    /** Band dari job grade sebuah entri; null bila grade tak dikenal band-nya. */
    public static function bandDariRow($row): ?string
    {
        $jg = (int) ($row->job_grade_label ?? 0);
        if ($jg <= 0) {
            return null;
        }
        $band = Karyawan::getBandFromGrade($jg);
        return $band === '-' ? null : $band;
    }

    /** Format lama-bulan menjadi "X tahun Y bulan". */
    public static function formatDurasiBulan(int $bulan): string
    {
        $th = intdiv($bulan, 12);
        $bl = $bulan % 12;
        $parts = [];
        if ($th > 0) $parts[] = $th . ' tahun';
        if ($bl > 0) $parts[] = $bl . ' bulan';
        return empty($parts) ? '0 bulan' : implode(' ', $parts);
    }

    /**
     * Sinkronisasi otomatis ke Pejabat Definitif (history_pejabats).
     * Berlaku untuk SEMUA jalur pembuatan history jabatan:
     * form manual, Terbit SK Promosi, dan Terbit SK Rotasi/Mutasi.
     */
    protected static function booted(): void
    {
        // Saat history jabatan baru dibuat
        static::created(function (HistoryJabatan $history) {
            // Perbarui tanggal masuk band karyawan secara otomatis.
            // Selalu dijalankan (di awal) agar tetap berlaku walau jabatan ini
            // bukan jabatan pejabat (yang akan memicu return lebih awal di bawah).
            self::syncTanggalMulaiBand($history->karyawan_id);

            // Tutup jabatan pejabat yang masih aktif untuk karyawan ini (kalau ada)
            HistoryPejabat::where('karyawan_id', $history->karyawan_id)
                ->whereNull('tanggal_selesai')
                ->update(['tanggal_selesai' => $history->tanggal_mulai]);

            // Tentukan tingkat pejabat dari jabatan master (level_pejabat) → fallback teks
            $jabatan = $history->jabatan_id ? Jabatan::find($history->jabatan_id) : null;
            $tier    = HistoryPejabat::resolveTier($jabatan, $history->jabatan_saat_ini);

            if (!$tier) {
                return; // bukan jabatan pejabat, tidak perlu dicatat
            }

            // Ambil nama unit dari snapshot FK milik history ini (akurat untuk semua jalur)
            $history->loadMissing(['direktorat', 'kompartemen', 'departemen', 'jobGrade', 'personGrade']);

            HistoryPejabat::create([
                'karyawan_id'        => $history->karyawan_id,
                'history_jabatan_id' => $history->id,
                'jabatan'            => $tier,
                'jabatan_saat_ini'   => $history->jabatan_saat_ini,
                'direktorat'         => $history->direktorat_label,
                'kompartemen'        => $history->kompartemen_label,
                'departemen'         => $history->departemen_label,
                'job_grade'          => $history->job_grade_label,
                'person_grade'       => $history->person_grade_label,
                'no_sk'              => $history->no_sk,
                'tanggal_sk'         => $history->tanggal_sk,
                'tanggal_mulai'      => $history->tanggal_mulai,
                'tanggal_selesai'    => $history->tanggal_selesai, // null = masih menjabat
                'keterangan'         => $history->keterangan,
            ]);
        });

        // Saat history jabatan dihapus → bersihkan record pejabat yang terhubung
        static::deleted(function (HistoryJabatan $history) {
            HistoryPejabat::where('history_jabatan_id', $history->id)->delete();

            // Riwayat berubah → hitung ulang tanggal masuk band karyawan.
            self::syncTanggalMulaiBand($history->karyawan_id);
        });
    }

    /**
     * Hitung ulang & simpan tanggal_mulai_band karyawan dari Riwayat Jabatan.
     * Dipanggil otomatis tiap riwayat jabatan dibuat/dihapus (termasuk lewat
     * Terbit SK Promosi/Mutasi). saveQuietly() dipakai agar tidak memicu
     * observer/event Karyawan lain.
     */
    /** Cache pengecekan keberadaan kolom (sekali per proses). */
    protected static ?bool $bandColumnExists = null;

    protected static function syncTanggalMulaiBand(?int $karyawanId): void
    {
        if (!$karyawanId) {
            return;
        }

        // Aman jika migration belum dijalankan: lewati bila kolom belum ada.
        if (self::$bandColumnExists === null) {
            self::$bandColumnExists = \Illuminate\Support\Facades\Schema::hasColumn('karyawans', 'tanggal_mulai_band');
        }
        if (! self::$bandColumnExists) {
            return;
        }

        $karyawan = Karyawan::find($karyawanId);
        if (!$karyawan) {
            return;
        }

        $baru = $karyawan->hitungTanggalMulaiBand();

        // Hindari penyimpanan sia-sia: bandingkan sebagai tanggal Y-m-d.
        $lama = optional($karyawan->tanggal_mulai_band)->format('Y-m-d');
        $baruStr = optional($baru)->format('Y-m-d');

        if ($lama !== $baruStr) {
            $karyawan->tanggal_mulai_band = $baru;
            $karyawan->saveQuietly();
        }
    }
}