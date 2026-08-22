<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UnitOrganisasiSnapshot extends Model
{
    protected $table = 'unit_organisasi_snapshot';

    protected $fillable = [
        'unit_organisasi_id', 'struktur_organisasi_versi_id',
        'nama_unit', 'level', 'parent_unit_organisasi_id',
        'mc_formasi', 'keterangan',
    ];

    public function unitOrganisasi()
    {
        return $this->belongsTo(UnitOrganisasi::class);
    }

    public function strukturOrganisasiVersi()
    {
        return $this->belongsTo(StrukturOrganisasiVersi::class);
    }

    // parent_unit_organisasi_id menunjuk ke identitas permanen unit (unit_organisasi), bukan ke snapshot lain
    public function parentUnit()
    {
        return $this->belongsTo(UnitOrganisasi::class, 'parent_unit_organisasi_id');
    }

    /**
     * Jumlah mc_formasi seluruh turunan (anak, cucu, dst — rekursif) dalam versi yang sama,
     * TIDAK termasuk mc_formasi unit ini sendiri. Null kalau unit ini tidak punya anak sama
     * sekali (dipakai caller untuk membedakan "tanpa anak" dari "anak ada tapi formasinya 0").
     *
     * Dipakai utk 1 unit lepas (bukan loop). Kalau caller perlu total ini utk BANYAK/SEMUA
     * unit sekaligus (tabel show, tree, export, preview) — pakai totalFormasiBawahanBatch()
     * sebagai gantinya: memanggil method ini per-unit di dalam loop me-regroup ulang seluruh
     * $snapshotsDalamVersi tiap kali (O(n) per panggilan × n unit = O(n²) total), sedangkan
     * versi batch menghitung semuanya sekali jalan (O(n) total).
     */
    public function totalFormasiBawahan(?Collection $snapshotsDalamVersi = null): ?int
    {
        $snapshots = $snapshotsDalamVersi ?? self::where('struktur_organisasi_versi_id', $this->struktur_organisasi_versi_id)->get();

        return self::totalFormasiBawahanBatch($snapshots)[$this->unit_organisasi_id] ?? null;
    }

    /**
     * Versi batch dari totalFormasiBawahan() utk SEMUA unit di $snapshots sekaligus, dalam
     * SATU pass O(n) (bottom-up, tiap node dihitung tepat sekali & di-memoize) — dipakai
     * ganti totalFormasiBawahan() di dalam loop/rekursi per-node (tabel show, <x-org-tree-node>,
     * export Excel/PDF, preview import lanjutan) yg sebelumnya O(n²) krn tiap panggilan
     * me-regroup ulang seluruh koleksi dari nol.
     *
     * Dilengkapi jaring pengaman cycle-detection (status 'visiting'/'done' per node): pada
     * operasi normal graph parent-child selalu berupa tree/forest (tidak circular) krn sudah
     * divalidasi sebelum sampai sini, TAPI kalau toh ada data cacat yg lolos (mis. 2 baris
     * import resolve ke unit_organisasi_id lama yg sama, membuat sebuah node jadi leluhurnya
     * sendiri), method ini menghentikan rekursi di titik itu (kontribusi cycle dihitung 0)
     * alih-alih rekursi tak terbatas sampai memory/waktu habis.
     *
     * @return array<int|string, int|null> unit_organisasi_id => total (null kalau unit itu tidak punya anak sama sekali)
     */
    public static function totalFormasiBawahanBatch(Collection $snapshots): array
    {
        $childrenByParent = $snapshots->groupBy('parent_unit_organisasi_id');
        $memo = [];
        $state = [];

        $compute = function ($unitOrganisasiId) use (&$compute, &$memo, &$state, $childrenByParent): int {
            if (isset($memo[$unitOrganisasiId])) {
                return $memo[$unitOrganisasiId];
            }
            if (($state[$unitOrganisasiId] ?? null) === 'visiting') {
                // Cycle terdeteksi (seharusnya sudah dicegah validasi upstream) — jaring
                // pengaman spy tidak infinite-loop, bukan alur normal.
                return 0;
            }
            $state[$unitOrganisasiId] = 'visiting';

            $total = 0;
            foreach ($childrenByParent->get($unitOrganisasiId, collect()) as $anak) {
                $total += $anak->mc_formasi + $compute($anak->unit_organisasi_id);
            }

            $state[$unitOrganisasiId] = 'done';
            return $memo[$unitOrganisasiId] = $total;
        };

        $result = [];
        foreach ($snapshots as $s) {
            $anakLangsung = $childrenByParent->get($s->unit_organisasi_id);
            $result[$s->unit_organisasi_id] = (!$anakLangsung || $anakLangsung->isEmpty())
                ? null
                : $compute($s->unit_organisasi_id);
        }

        return $result;
    }
}
