<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Department;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
class SchoolClassController extends Controller
{
    public function index(Request $r){ $items=SchoolClass::with(['department','academicYear'])->orderBy('name')->paginate(10); return view('admin.classes.index',compact('items')); }
    public function create(){ $deps=Department::all(); $years=AcademicYear::all(); return view('admin.classes.create',compact('deps','years')); }
    public function store(Request $r){ $d=$r->validate(['department_id'=>'required','academic_year_id'=>'required','name'=>'required','level'=>'required']); SchoolClass::create($d); return redirect()->route('admin.classes.index')->with('success','OK'); }
    public function edit(SchoolClass $class){ $deps=Department::all(); $years=AcademicYear::all(); return view('admin.classes.edit',compact('class','deps','years')); }
    public function update(Request $r, SchoolClass $class){ $d=$r->validate(['department_id'=>'required','academic_year_id'=>'required','name'=>'required','level'=>'required']); $class->update($d); return redirect()->route('admin.classes.index')->with('success','OK'); }
    public function destroy(SchoolClass $class){ $class->delete(); return redirect()->route('admin.classes.index')->with('success','OK'); }
}
