<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
class SubjectController extends Controller
{
    public function index(Request $r){ $q=$r->get('q'); $items=Subject::when($q,fn($x)=>$x->where('name','like',"%$q%")->orWhere('code','like',"%$q%"))->orderBy('name')->paginate(10)->withQueryString(); return view('admin.subjects.index',compact('items','q')); }
    public function create(){ $departments=\App\Models\Department::orderBy('name')->get(); return view('admin.subjects.create',compact('departments')); }
    public function store(Request $r){ $d=$r->validate(['code'=>'required|unique:subjects','name'=>'required','department_id'=>'nullable|exists:departments,id']); Subject::create($d+['is_active'=>true]); return redirect()->route('admin.subjects.index')->with('success','OK'); }
    public function edit(Subject $subject){ $departments=\App\Models\Department::orderBy('name')->get(); return view('admin.subjects.edit',compact('subject','departments')); }
    public function update(Request $r, Subject $subject){ $d=$r->validate(['code'=>'required|unique:subjects,code,'.$subject->id,'name'=>'required','department_id'=>'nullable|exists:departments,id']); $subject->update($d); return redirect()->route('admin.subjects.index')->with('success','OK'); }
    public function destroy(Subject $subject){ $subject->delete(); return redirect()->route('admin.subjects.index')->with('success','OK'); }
}
