<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\CsvImportService;
use Illuminate\Http\Request;

/**
 * Fase E (RDM): Impor CSV siswa dan guru (admin saja).
 * Template bisa diunduh; unggahan divalidasi per baris.
 */
class CsvImportController extends Controller
{
    public function __construct(
        private CsvImportService $importer,
    ) {
    }

    /**
     * Halaman Impor Data: template + form unggah siswa dan guru.
     */
    public function index()
    {
        return view('admin.import.index');
    }

    /**
     * Unduh template CSV siswa.
     */
    public function templateStudents()
    {
        return response($this->importer->studentTemplate(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template-siswa.csv"',
        ]);
    }

    /**
     * Unduh template CSV guru.
     */
    public function templateTeachers()
    {
        return response($this->importer->teacherTemplate(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template-guru.csv"',
        ]);
    }

    /**
     * Unggah dan impor CSV siswa.
     */
    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        try {
            $result = $this->importer->importStudents($request->file('file')->getRealPath());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        $this->logImport($request, 'students', $result);

        return back()->with('import_result', array_merge($result, ['type' => 'siswa']));
    }

    /**
     * Unggah dan impor CSV guru.
     */
    public function importTeachers(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        try {
            $result = $this->importer->importTeachers($request->file('file')->getRealPath());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        $this->logImport($request, 'teachers', $result);

        return back()->with('import_result', array_merge($result, ['type' => 'guru']));
    }

    private function logImport(Request $request, string $type, array $result): void
    {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'csv_import_'.$type,
            'model_type' => null,
            'model_id' => null,
            'metadata' => [
                'created' => $result['created'],
                'failed' => $result['failed'],
            ],
        ]);
    }
}
