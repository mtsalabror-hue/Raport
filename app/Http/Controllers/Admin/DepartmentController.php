<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $items = Department::when($q, fn($x) => $x->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%"))
            ->orderBy('name')->paginate(10)->withQueryString();
        return view('admin.departments.index', compact('items', 'q'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['code' => 'required|unique:departments', 'name' => 'required', 'description' => 'nullable']);
        Department::create($data);
        return redirect()->route('admin.departments.index')->with('success', 'Berhasil');
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate(['code' => 'required|unique:departments,code,' . $department->id, 'name' => 'required', 'description' => 'nullable']);
        $department->update($data);
        return redirect()->route('admin.departments.index')->with('success', 'Berhasil');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('admin.departments.index')->with('success', 'Berhasil');
    }
}
