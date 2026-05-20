<?php

namespace App\Http\Controllers;

use App\Models\JobGrade;
use Illuminate\Http\Request;

class MasterJobGradeController extends Controller
{
    public function index()
    {
        $data = JobGrade::orderBy('job_grade')->paginate(15);
        return view('master.job_grade', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'job_grade' => 'required|string|unique:job_grade,job_grade',
        ]);
        JobGrade::create(['job_grade' => $request->job_grade]);
        return redirect()->route('master.job-grade.index')
            ->with('success', 'Job Grade berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $jobGrade = JobGrade::findOrFail($id);
        $request->validate([
            'job_grade' => 'required|string|unique:job_grade,job_grade,' . $id,
        ]);
        $jobGrade->update(['job_grade' => $request->job_grade]);
        return redirect()->route('master.job-grade.index')
            ->with('success', 'Job Grade berhasil diupdate!');
    }

   public function destroy($id)
    {
    try {
        JobGrade::findOrFail($id)->delete();
        return redirect()->route('master.job-grade.index')
            ->with('success', 'Job Grade berhasil dihapus!');
    } catch (\Illuminate\Database\QueryException $e) {
        return redirect()->route('master.job-grade.index')
            ->with('error', 'Job Grade tidak bisa dihapus karena masih digunakan di data lain!');
    }
    }
}