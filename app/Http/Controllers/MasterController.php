<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Base controller CRUD untuk data master sederhana (satu kolom utama).
 * Controller anak cukup mendefinisikan properti konfigurasi di bawah;
 * seluruh logika index/store/update/destroy diwariskan dari sini.
 */
abstract class MasterController extends Controller
{
    /** @var class-string<\Illuminate\Database\Eloquent\Model> Model Eloquent */
    protected string $model;

    /** Nama kolom utama (mis. 'nama_jabatan', 'job_grade'). */
    protected string $column;

    /** Kolom berisi angka (mis. 'job_grade') → diurutkan numerik, bukan alfabetis. */
    protected bool $columnIsNumeric = false;

    /** Nilai placeholder / "belum ditentukan" agar selalu tampil paling bawah. */
    protected array $placeholderValues = [
        '', '-', '–', '—', 'belum ditentukan', 'belum ada', 'tidak ada', 'n/a', 'na', 'undefined', 'null',
    ];

    /** Prefix nama route (mis. 'master.jabatan'). */
    protected string $routeName;

    /** Nama view (mis. 'master.jabatan'). */
    protected string $view;

    /** Label untuk pesan sukses/error (mis. 'Jabatan'). */
    protected string $label;

    /** Nama tabel model — dipakai untuk aturan validasi unique. */
    protected function tabel(): string
    {
        return (new $this->model)->getTable();
    }

    public function index()
    {
        $col   = $this->column;
        $query = $this->model::query();

        if ($this->columnIsNumeric) {
            // Angka diurutkan numerik (8, 9, 10 … 22); nilai non-angka (mis. "-") didorong ke bawah
            $query->orderByRaw("(CASE WHEN `{$col}` REGEXP '^[0-9]+$' THEN 0 ELSE 1 END)")
                  ->orderByRaw("CAST(`{$col}` AS UNSIGNED)")
                  ->orderBy($col);
        } else {
            // Alfabetis; placeholder / "belum ditentukan" / "-" selalu di paling bawah
            $marks = implode(',', array_fill(0, count($this->placeholderValues), '?'));
            $query->orderByRaw("(CASE WHEN LOWER(TRIM(`{$col}`)) IN ({$marks}) THEN 1 ELSE 0 END)", $this->placeholderValues)
                  ->orderBy($col);
        }

        $data = $query->paginate(15);

        return view($this->view, compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            $this->column => 'required|string|unique:' . $this->tabel() . ',' . $this->column,
        ]);

        $this->model::create([$this->column => $request->input($this->column)]);

        return redirect()->route($this->routeName . '.index')
            ->with('success', $this->label . ' berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $item = $this->model::findOrFail($id);

        $request->validate([
            $this->column => 'required|string|unique:' . $this->tabel() . ',' . $this->column . ',' . $id,
        ]);

        $item->update([$this->column => $request->input($this->column)]);

        return redirect()->route($this->routeName . '.index')
            ->with('success', $this->label . ' berhasil diupdate!');
    }

    public function destroy($id)
    {
        try {
            $this->model::findOrFail($id)->delete();

            return redirect()->route($this->routeName . '.index')
                ->with('success', $this->label . ' berhasil dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route($this->routeName . '.index')
                ->with('error', $this->label . ' tidak bisa dihapus karena masih digunakan di data lain!');
        }
    }
}
