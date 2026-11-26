<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
class SubjectController extends Controller
{
    public function index(Request $r){ $q=$r->get('q'); $items=Subject::when($q,fn($x)=>$x->where('name','like',"%$q%")->orWhere('code','like',"%$q%"))->orderBy('name')->paginate(10)->withQueryString(); return view('admin.subjects.index',compact('items','q')); }
    public function create(){ return view('admin.subjects.create'); }
    public function store(Request $r){ $d=$r->validate(['code'=>'required|unique:subjects','name'=>'required']); Subject::create($d+['is_active'=>true]); return redirect()->route('admin.subjects.index')->with('success','OK'); }
    public function edit(Subject $subject){ return view('admin.subjects.edit',compact('subject')); }
    public function update(Request $r, Subject $subject){ $d=$r->validate(['code'=>'required|unique:subjects,code,'.$subject->id,'name'=>'required']); $subject->update($d); return redirect()->route('admin.subjects.index')->with('success','OK'); }
    public function destroy(Subject $subject){ $subject->delete(); return redirect()->route('admin.subjects.index')->with('success','OK'); }
}
