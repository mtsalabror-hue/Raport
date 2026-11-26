<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
class SemesterController extends Controller
{
    public function index(Request $r){ $items=Semester::with('academicYear')->orderBy('id')->paginate(10); return view('admin.semesters.index',compact('items')); }
    public function create(){ $years=AcademicYear::all(); return view('admin.semesters.create',compact('years')); }
    public function store(Request $r){ $d=$r->validate(['academic_year_id'=>'required','name'=>'required']); Semester::create($d+['is_active'=>false]); return redirect()->route('admin.semesters.index')->with('success','OK'); }
    public function edit(Semester $semester){ $years=AcademicYear::all(); return view('admin.semesters.edit',compact('semester','years')); }
    public function update(Request $r, Semester $semester){ $d=$r->validate(['academic_year_id'=>'required','name'=>'required']); $semester->update($d); return redirect()->route('admin.semesters.index')->with('success','OK'); }
    public function destroy(Semester $semester){ $semester->delete(); return redirect()->route('admin.semesters.index')->with('success','OK'); }
}
