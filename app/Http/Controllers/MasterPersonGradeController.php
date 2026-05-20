<?php

namespace App\Http\Controllers;

use App\Models\PersonGrade;
use Illuminate\Http\Request;

class MasterPersonGradeController extends Controller
{
    public function index()
    {
        $data = PersonGrade::orderBy('person_grade')->paginate(15);
        return view('master.person_grade', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'person_grade' => 'required|string|unique:person_grade,person_grade',
        ]);
        PersonGrade::create(['person_grade' => $request->person_grade]);
        return redirect()->route('master.person-grade.index')
            ->with('success', 'Person Grade berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $personGrade = PersonGrade::findOrFail($id);
        $request->validate([
            'person_grade' => 'required|string|unique:person_grade,person_grade,' . $id,
        ]);
        $personGrade->update(['person_grade' => $request->person_grade]);
        return redirect()->route('master.person-grade.index')
            ->with('success', 'Person Grade berhasil diupdate!');
    }

    public function destroy($id)
{
    try {
        PersonGrade::findOrFail($id)->delete();
        return redirect()->route('master.person-grade.index')
            ->with('success', 'Person Grade berhasil dihapus!');
    } catch (\Illuminate\Database\QueryException $e) {
        return redirect()->route('master.person-grade.index')
            ->with('error', 'Person Grade tidak bisa dihapus karena masih digunakan di data lain!');
    }
}
}