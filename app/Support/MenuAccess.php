<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Registry menu yang aksesnya bisa diatur per-admin (satu sumber kebenaran).
 *
 * Tiap menu:
 *   'label'  → nama tampil di UI centang
 *   'group'  → pengelompokan tampilan di UI centang
 *   'home'   → nama route landing (untuk redirect setelah login)
 *   'routes' → pola nama route yang DIJAGA middleware (Str::is, '*' = wildcard)
 *
 * Catatan pemetaan: halaman kelola per-karyawan (history jabatan/pendidikan/
 * assessment/toefl milik SATU karyawan) dijaga di bawah 'karyawan' — supaya
 * admin yang boleh mengelola Profil Karyawan bisa mengelola detailnya. Menu
 * "History Jabatan/Pendidikan/Assessment/TOEFL" di sidebar adalah daftar GLOBAL
 * (pakai sufiks _all) dan dijaga terpisah.
 */
class MenuAccess
{
    public static function menus(): array
    {
        return [
            'dashboard'          => ['label' => 'Dashboard',             'group' => 'Umum',              'home' => 'dashboard',                       'routes' => ['dashboard']],
            'karyawan'           => ['label' => 'Profil Karyawan',       'group' => 'Data Karyawan',     'home' => 'karyawan.index',                  'routes' => ['karyawan.*', 'history_jabatan.*', 'riwayat_pendidikan.*', 'history_assessment.*', 'assessment_kompetensi.*', 'toefl.*', 'penilaian_karyawan.*', 'kalibrasi_karyawan.*']],
            'history_jabatan'    => ['label' => 'History Jabatan',       'group' => 'Data Karyawan',     'home' => 'history_karyawan.index',          'routes' => ['history_karyawan.*']],
            'history_pendidikan' => ['label' => 'History Pendidikan',    'group' => 'Data Karyawan',     'home' => 'riwayat_pendidikan_all.index',    'routes' => ['riwayat_pendidikan_all.*']],
            'struktur'           => ['label' => 'Struktur Organisasi',   'group' => 'Data Karyawan',     'home' => 'struktur-organisasi.index',       'routes' => ['struktur-organisasi.*']],
            'talent_pool'        => ['label' => 'Data Talent',          'group' => 'Manajemen Talenta', 'home' => 'talent_pool.index',               'routes' => ['talent_pool.*']],
            'usulan_promosi'     => ['label' => 'Usulan Promosi',       'group' => 'Manajemen Talenta', 'home' => 'usulan_promosi.index',            'routes' => ['usulan_promosi.*']],
            'reminder_promosi'   => ['label' => 'Reminder Promosi',     'group' => 'Manajemen Talenta', 'home' => 'reminder_promosi.index',          'routes' => ['reminder_promosi.*']],
            'usulan_mutasi'      => ['label' => 'Rotasi & Mutasi',      'group' => 'Manajemen Talenta', 'home' => 'usulan_mutasi.index',             'routes' => ['usulan_mutasi.*']],
            'struktur_versi'     => ['label' => 'Riwayat Struktur Organisasi', 'group' => 'Organisasi', 'home' => 'organisasi.struktur.index',  'routes' => ['organisasi.struktur.*', 'organisasi.unit.*']],
            'job_profile'        => ['label' => 'Job Profile',          'group' => 'Organisasi',        'home' => 'organisasi.job-profile.index', 'routes' => ['organisasi.job-profile.*']],
            'kompetensi_teknis'  => ['label' => 'Kompetensi Teknis',    'group' => 'Organisasi',        'home' => 'organisasi.kompetensi-teknis.index', 'routes' => ['organisasi.kompetensi-teknis.*']],
            'job_family'         => ['label' => 'Job Family',           'group' => 'Organisasi',        'home' => 'organisasi.job-family.index', 'routes' => ['organisasi.job-family.*']],
            'history_pejabat'    => ['label' => 'Pejabat Definitif',    'group' => 'Kepejabatan',       'home' => 'history_pejabat.index',           'routes' => ['history_pejabat.*']],
            'pgs_pjs'            => ['label' => 'PGS / PJS',            'group' => 'Kepejabatan',       'home' => 'pgs_pjs.index',                   'routes' => ['pgs_pjs.*']],
            'assessment'         => ['label' => 'History Assessment',   'group' => 'Assessment & Penilaian', 'home' => 'history_assessment_all.index', 'routes' => ['history_assessment_all.*']],
            'kalibrasi'          => ['label' => 'Penilaian & Kalibrasi','group' => 'Assessment & Penilaian', 'home' => 'history_penilaian_kalibrasi.index', 'routes' => ['history_penilaian_kalibrasi.*']],
            'toefl'              => ['label' => 'Nilai TOEFL',          'group' => 'Assessment & Penilaian', 'home' => 'toefl_all.index',          'routes' => ['toefl_all.*']],
            'surat_penting'      => ['label' => 'Surat Penting',        'group' => 'Layanan',           'home' => 'surat_penting.index',             'routes' => ['surat_penting.*']],
            'faq'                => ['label' => 'FAQ & Panduan',        'group' => 'Layanan',           'home' => 'faq',                             'routes' => ['faq']],
            'laporan'            => ['label' => 'Laporan Bulanan',      'group' => 'Lainnya',           'home' => 'laporan.bulanan',                 'routes' => ['laporan.*']],
            'export_builder'     => ['label' => 'Export Builder',       'group' => 'Lainnya',           'home' => 'export_builder.index',            'routes' => ['export_builder.*']],
            // Catatan: Manajemen Akun, Log Aktivitas, dan Backup sengaja TIDAK ada
            // di sini — tetap super_admin-only (alat kontrol sistem, mencegah admin
            // menaikkan aksesnya sendiri).
        ];
    }

    /** Semua key menu yang valid. */
    public static function keys(): array
    {
        return array_keys(static::menus());
    }

    /**
     * Peta jenis notifikasi → key menu terkait. Dipakai untuk memfilter notifikasi
     * agar admin hanya melihat yang relevan dengan menunya. Jenis yang TIDAK
     * terdaftar di sini dianggap umum (tampil untuk semua).
     */
    public static function notifikasiTypeMenu(): array
    {
        return [
            'idp_expire'       => 'assessment',        // Assessment akan expire
            'eligible_grade'   => 'reminder_promosi',  // Eligible kenaikan grade
            'pgs_pjs_berakhir' => 'pgs_pjs',           // PGS/PJS akan berakhir
            'pensiun'          => 'karyawan',          // Mendekati pensiun
            'masa_kerja'       => 'karyawan',          // Milestone masa kerja
        ];
    }

    /** Menu dikelompokkan per 'group' (untuk UI centang). */
    public static function grouped(): array
    {
        $out = [];
        foreach (static::menus() as $key => $def) {
            $out[$def['group']][$key] = $def;
        }
        return $out;
    }

    /**
     * Key menu untuk sebuah nama route; null bila route tidak dijaga
     * (mis. notifikasi, profil, logout — bebas diakses).
     */
    public static function menuForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }
        foreach (static::menus() as $key => $def) {
            foreach ($def['routes'] as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return $key;
                }
            }
        }
        return null;
    }
}
