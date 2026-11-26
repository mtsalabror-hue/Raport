<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
class TeacherController extends Controller
{
    public function index(Request $r){ $q=$r->get('q'); $items=Teacher::when($q,fn($x)=>$x->where('name','like',"%$q%")->orWhere('nip','like',"%$q%"))->orderBy('name')->paginate(10)->withQueryString(); return view('admin.teachers.index',compact('items','q')); }
    public function create(){ return view('admin.teachers.create'); }
    public function store(Request $r){ $d=$r->validate(['nip'=>'required|unique:teachers','name'=>'required','gender'=>'required','phone'=>'nullable','address'=>'nullable']); Teacher::create($d); return redirect()->route('admin.teachers.index')->with('success','OK'); }
    public function edit(Teacher $teacher){ return view('admin.teachers.edit',compact('teacher')); }
    public function update(Request $r, Teacher $teacher){ $d=$r->validate(['nip'=>'required|unique:teachers,nip,'.$teacher->id,'name'=>'required','gender'=>'required','phone'=>'nullable','address'=>'nullable']); $teacher->update($d); return redirect()->route('admin.teachers.index')->with('success','OK'); }
    public function destroy(Teacher $teacher){ $teacher->delete(); return redirect()->route('admin.teachers.index')->with('success','OK'); }
}
