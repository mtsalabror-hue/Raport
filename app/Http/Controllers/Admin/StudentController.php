<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
class StudentController extends Controller
{
    public function index(Request $r){ $q=$r->get('q'); $items=Student::when($q,fn($x)=>$x->where('name','like',"%$q%")->orWhere('nis','like',"%$q%"))->orderBy('name')->paginate(10)->withQueryString(); return view('admin.students.index',compact('items','q')); }
    public function create(){ $classes=\App\Models\SchoolClass::orderBy('name')->get(); return view('admin.students.create',compact('classes')); }
    public function store(Request $r){ $d=$r->validate(['nis'=>'required|unique:students','nisn'=>'required|unique:students','name'=>'required','gender'=>'required|in:L,P','class_id'=>'nullable|exists:classes,id','birth_date'=>'nullable|date','address'=>'nullable|string']); Student::create($d); return redirect()->route('admin.students.index')->with('success','OK'); }
    public function edit(Student $student){ $classes=\App\Models\SchoolClass::orderBy('name')->get(); return view('admin.students.edit',compact('student','classes')); }
    public function update(Request $r, Student $student){ $d=$r->validate(['nis'=>'required|unique:students,nis,'.$student->id,'nisn'=>'required|unique:students,nisn,'.$student->id,'name'=>'required','gender'=>'required|in:L,P','class_id'=>'nullable|exists:classes,id','birth_date'=>'nullable|date','address'=>'nullable|string']); $student->update($d); return redirect()->route('admin.students.index')->with('success','OK'); }
    public function destroy(Student $student){ $student->delete(); return redirect()->route('admin.students.index')->with('success','OK'); }
}
