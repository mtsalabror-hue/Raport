<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $r)
    {
        $q = $r->get('q');
        $items = User::with('role')
            ->when($q, fn ($x) => $x->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%"))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('items', 'q'));
    }

    public function create()
    {
        return view('admin.users.create', $this->options());
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'is_active' => 'nullable|boolean',
        ]);
        $d['password'] = Hash::make($d['password']);
        $d['is_active'] = $r->boolean('is_active', true);
        User::create($d);

        return redirect()->route('admin.users.index')->with('success', 'OK');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', array_merge(['user' => $user], $this->options()));
    }

    public function update(Request $r, User $user)
    {
        $d = $r->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'is_active' => 'nullable|boolean',
        ]);
        if (empty($d['password'])) {
            unset($d['password']);
        } else {
            $d['password'] = Hash::make($d['password']);
        }
        $d['is_active'] = $r->boolean('is_active', true);
        $user->update($d);

        return redirect()->route('admin.users.index')->with('success', 'OK');
    }

    public function destroy(User $user)
    {
        // Admin tidak boleh menghapus akunnya sendiri.
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Tidak bisa menghapus akun sendiri.');
        }
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'OK');
    }

    private function options(): array
    {
        return [
            'roles' => Role::orderBy('name')->get(),
            'teachers' => Teacher::orderBy('name')->get(),
        ];
    }
}
