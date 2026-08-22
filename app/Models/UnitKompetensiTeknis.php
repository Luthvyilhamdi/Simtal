<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitKompetensiTeknis extends Model
{
    protected $table = 'unit_kompetensi_teknis';

    protected $fillable = [
        'unit_organisasi_id',
        'struktur_organisasi_versi_id',
        'jenjang_jabatan',
        'urutan_jenjang',
        'grade',
        'nama_jobs',
        'managerial',
        'kompetensi_teknis_id',
        'level',
        'asal',
        'prioritas',
    ];

    protected $casts = [
        'managerial' => 'boolean',
    ];

    /**
     * Label badge tunggal utk kombinasi asal(native/generic) + prioritas(primary/secondary)
     * — kolom DB tidak berubah, ini murni presentasi. "Primary (Generic)" khusus kasus
     * generic+primary (kompetensi pinjaman rumpun lain yg levelnya tetap primary) supaya
     * tetap kelihatan beda dari Primary biasa (native+primary). Accessor dipusatkan di sini
     * (bukan diulang tiap view) — dipakai unit-overlay & posisi-overlay blade partial.
     */
    public function getPrioritasLabelAttribute(): string
    {
        if ($this->prioritas === 'primary') {
            return $this->asal === 'generic' ? 'Primary (Generic)' : 'Primary';
        }

        return $this->asal === 'generic' ? 'Generic' : 'Secondary';
    }

    /**
     * Modifier class CSS pasangan getPrioritasLabelAttribute() — dipakai sbg
     * `class="komtek-badge-tipe {{ $item->prioritas_badge_class }}"` (lihat CSS-nya di
     * kompetensi_teknis/partials/overlay-shell.blade.php).
     */
    public function getPrioritasBadgeClassAttribute(): string
    {
        if ($this->prioritas === 'primary') {
            return $this->asal === 'generic' ? 'primary-generic' : 'primary';
        }

        return $this->asal === 'generic' ? 'generic' : 'secondary';
    }

    public function kompetensiTeknis()
    {
        return $this->belongsTo(KompetensiTeknis::class);
    }

    public function unitOrganisasi()
    {
        return $this->belongsTo(UnitOrganisasi::class);
    }

    public function strukturOrganisasiVersi()
    {
        return $this->belongsTo(StrukturOrganisasiVersi::class);
    }
}
