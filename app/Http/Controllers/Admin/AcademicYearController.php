<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
class AcademicYearController extends Controller
{
    public function index(Request $r){ $items=AcademicYear::orderBy('name')->paginate(10); return view('admin.academic_years.index',compact('items')); }
    public function create(){ return view('admin.academic_years.create'); }
    public function store(Request $r){ $d=$r->validate(['name'=>'required|unique:academic_years','start_date'=>'required|date','end_date'=>'required|date','is_active'=>'boolean']); AcademicYear::create($d); return redirect()->route('admin.academic_years.index')->with('success','OK'); }
    public function edit(AcademicYear $academicYear){ return view('admin.academic_years.edit',compact('academicYear')); }
    public function update(Request $r, AcademicYear $academicYear){ $d=$r->validate(['name'=>'required|unique:academic_years,name,'.$academicYear->id,'start_date'=>'required|date','end_date'=>'required|date','is_active'=>'boolean']); $academicYear->update($d); return redirect()->route('admin.academic_years.index')->with('success','OK'); }
    public function destroy(AcademicYear $academicYear){ $academicYear->delete(); return redirect()->route('admin.academic_years.index')->with('success','OK'); }
}
