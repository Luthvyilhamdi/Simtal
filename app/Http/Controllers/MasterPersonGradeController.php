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

    public function update(Request $request, PersonGrade $personGrade)
    {
        $request->validate([
            'person_grade' => 'required|string|unique:person_grade,person_grade,' . $personGrade->id,
        ]);
        $personGrade->update(['person_grade' => $request->person_grade]);
        return redirect()->route('master.person-grade.index')
            ->with('success', 'Person Grade berhasil diupdate!');
    }

    public function destroy(PersonGrade $personGrade)
    {
        $personGrade->delete();
        return redirect()->route('master.person-grade.index')
            ->with('success', 'Person Grade berhasil dihapus!');
    }
}