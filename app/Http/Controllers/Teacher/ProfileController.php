<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Profil Saya (Fase F / RDM): guru dan wali kelas (keduanya guru)
 * mengubah profilnya sendiri — nama, telepon, alamat (tabel
 * teachers + nama user terkait) — serta ganti password.
 *
 * Foto sengaja tidak dibuat (Fase F): kolom photo sudah ada di
 * tabel teachers, tetapi upload + tampil foto belum dipakai di
 * halaman mana pun, jadi ditunda sampai ada kebutuhan tampilan.
 * Admin tidak butuh halaman ini (sudah ada CRUD pengguna).
 */
class ProfileController extends Controller
{
    private function resolveTeacher(Request $request): Teacher
    {
        $user = $request->user();

        if ($user && $user->teacher_id) {
            return Teacher::findOrFail($user->teacher_id);
        }

        if (Teacher::count() === 1) {
            return Teacher::first();
        }

        abort(403, 'Akun Anda belum terhubung ke data guru. Minta admin menghubungkan akun ini di data pengguna.');
    }

    private function urls(Request $request): array
    {
        $isWali = $request->user()?->hasRole('wali_kelas');

        return [
            'action_url' => $isWali ? route('wali.profile.update') : route('guru.profile.update'),
            'password_url' => $isWali ? route('wali.profile.password.update') : route('guru.profile.password.update'),
            'back_url' => $isWali ? route('wali.dashboard') : url('/guru/dashboard'),
        ];
    }

    public function edit(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        return view('teacher.profile.edit', array_merge(
            $this->urls($request),
            ['teacher' => $teacher, 'user' => $request->user()]
        ));
    }

    public function update(Request $request)
    {
        $d = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:2000',
        ]);

        $teacher = $this->resolveTeacher($request);
        $user = $request->user();

        DB::transaction(function () use ($teacher, $user, $d, $request) {
            $teacher->update([
                'name' => $d['name'],
                'phone' => $d['phone'] ?? null,
                'address' => $d['address'] ?? null,
            ]);

            // Nama akun diselaraskan dengan nama guru.
            if ($user) {
                $user->update(['name' => $d['name']]);
            }

            ActivityLog::create([
                'user_id' => $user?->id,
                'action' => 'teacher_profile.updated',
                'model_type' => Teacher::class,
                'model_id' => $teacher->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['teacher_id' => $teacher->id],
            ]);
        });

        return back()->with('success', 'Profil Anda diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $d = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $teacher = $this->resolveTeacher($request);
        $user = $request->user();

        if (! $user || ! Hash::check($d['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama salah.',
            ]);
        }

        DB::transaction(function () use ($user, $teacher, $d, $request) {
            $user->update(['password' => $d['password']]);

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'teacher_profile.password_changed',
                'model_type' => Teacher::class,
                'model_id' => $teacher->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['teacher_id' => $teacher->id],
            ]);
        });

        return back()->with('success', 'Password Anda berhasil diubah.');
    }
}
