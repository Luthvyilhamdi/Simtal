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
        $data = $this->model::orderBy($this->column)->paginate(15);

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
