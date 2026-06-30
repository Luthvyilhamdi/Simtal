<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryJabatan extends Model
{
    protected $fillable = [
        'karyawan_id', 'jabatan_id', 'jabatan_saat_ini', 'direktorat_id',
        'kompartemen_id', 'departemen_id', 'job_grade_id',
        'person_grade_id', 'kode_struktur_id',
        'tanggal_mulai', 'tanggal_selesai',
        'tipe', 'keterangan',
        'no_sk', 'tanggal_sk',
        'is_current',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_sk'      => 'date',
        'is_current'      => 'boolean',
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
                'direktorat'         => optional($history->direktorat)->nama_direktorat
                                            ?? optional($history->direktorat)->nama,
                'kompartemen'        => optional($history->kompartemen)->nama_kompartemen,
                'departemen'         => optional($history->departemen)->nama_departemen,
                'job_grade'          => optional($history->jobGrade)->job_grade,
                'person_grade'       => optional($history->personGrade)->person_grade,
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