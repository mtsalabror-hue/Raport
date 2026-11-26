<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
class CurriculumController extends Controller
{
    public function index(Request $r){ $items=Curriculum::with('academicYear')->orderBy('name')->paginate(10); return view('admin.curricula.index',compact('items')); }
    public function create(){ $years=AcademicYear::all(); return view('admin.curricula.create',compact('years')); }
    public function store(Request $r){ $d=$r->validate(['name'=>'required','academic_year_id'=>'nullable']); Curriculum::create($d); return redirect()->route('admin.curricula.index')->with('success','OK'); }
    public function edit(Curriculum $curriculum){ $years=AcademicYear::all(); return view('admin.curricula.edit',compact('curriculum','years')); }
    public function update(Request $r, Curriculum $curriculum){ $d=$r->validate(['name'=>'required','academic_year_id'=>'nullable']); $curriculum->update($d); return redirect()->route('admin.curricula.index')->with('success','OK'); }
    public function destroy(Curriculum $curriculum){ $curriculum->delete(); return redirect()->route('admin.curricula.index')->with('success','OK'); }
}
