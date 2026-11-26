<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Semester;
use App\Services\TeachingCopyService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Fase E (RDM): Salin Mengajar — salin penugasan mengajar dari satu
 * semester ke semester lain (admin saja).
 */
class TeachingCopyController extends Controller
{
    public function __construct(
        private TeachingCopyService $copier,
    ) {
    }

    /**
     * Form salin: pilih semester asal dan semester tujuan.
     */
    public function index()
    {
        $semesters = Semester::with('academicYear')
            ->orderByDesc('academic_year_id')
            ->orderBy('name')
            ->get();

        return view('admin.teaching_assignments.copy', [
            'semesters' => $semesters,
        ]);
    }

    /**
     * Jalankan penyalinan; tampilkan ringkasan tersalin/dilewati.
     */
    public function store(Request $request)
    {
        $d = $request->validate([
            'from_semester_id' => 'required|integer|exists:semesters,id',
            'to_semester_id' => 'required|integer|exists:semesters,id',
        ]);

        try {
            $result = $this->copier->copy($d['from_semester_id'], $d['to_semester_id']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'teaching_assignments_copied',
            'model_type' => null,
            'model_id' => null,
            'metadata' => [
                'from_semester_id' => $d['from_semester_id'],
                'to_semester_id' => $d['to_semester_id'],
                'copied' => $result['copied'],
                'skipped' => $result['skipped'],
            ],
        ]);

        $source = $result['source']->academicYear->name.' — '.$result['source']->name;
        $target = $result['target']->academicYear->name.' — '.$result['target']->name;

        return back()->with('success', "Salin mengajar selesai ({$source} ke {$target}): {$result['copied']} penugasan tersalin, {$result['skipped']} dilewati karena sudah ada di semester tujuan.");
    }
}
