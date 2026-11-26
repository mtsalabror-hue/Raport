<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Extracurricular;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Master ekstrakurikuler untuk admin (Fase F / RDM): menetapkan
 * guru pembina tiap jenis ekstrakurikuler. CRUD master tetap di
 * halaman wali (Wali\HomeroomController); halaman ini khusus
 * penugasan pembina.
 */
class ExtracurricularController extends Controller
{
    public function index()
    {
        return view('admin.extracurriculars.index', [
            'masters' => Extracurricular::with('supervisor')->orderBy('name')->get(),
            'teachers' => Teacher::orderBy('name')->get(),
        ]);
    }

    public function updateSupervisor(Request $request, Extracurricular $extracurricular)
    {
        $d = $request->validate([
            'supervisor_id' => 'nullable|integer|exists:teachers,id',
        ]);

        DB::transaction(function () use ($extracurricular, $d, $request) {
            $extracurricular->update(['supervisor_id' => $d['supervisor_id'] ?? null]);

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'extracurricular.supervisor_assigned',
                'model_type' => Extracurricular::class,
                'model_id' => $extracurricular->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'name' => $extracurricular->name,
                    'supervisor_id' => $d['supervisor_id'] ?? null,
                ],
            ]);
        });

        $name = $d['supervisor_id'] ?? null
            ? Teacher::find($d['supervisor_id'])?->name
            : null;

        return back()->with('success', $name
            ? "Pembina {$extracurricular->name} ditetapkan: {$name}."
            : "Pembina {$extracurricular->name} dikosongkan.");
    }
}
