<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Fase E (RDM): Backup & Restore (admin saja).
 */
class BackupController extends Controller
{
    public function __construct(
        private BackupService $backup,
    ) {
    }

    /**
     * Halaman Backup & Restore: tombol unduh + form unggah restore.
     */
    public function index()
    {
        return view('admin.backup.index', [
            'tables' => BackupService::TABLES,
        ]);
    }

    /**
     * Unduh cadangan seluruh data sebagai satu file JSON.
     */
    public function download()
    {
        $data = $this->backup->export();

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="'.$this->backup->fileName().'"');
    }

    /**
     * Restore dari file JSON cadangan yang diunggah. File tidak
     * valid ditolak tanpa mengubah data.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup' => ['required', 'file', 'mimes:json,txt', 'max:51200'],
        ]);

        $content = file_get_contents($request->file('backup')->getRealPath());

        $data = json_decode($content, true);

        if (! is_array($data)) {
            return back()->withErrors([
                'backup' => 'File bukan JSON yang valid.',
            ]);
        }

        try {
            $result = $this->backup->restore($data);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'backup_restored',
            'model_type' => null,
            'model_id' => null,
            'metadata' => [
                'restored_tables' => count($result['restored_tables']),
                'restored_rows' => $result['restored_rows'],
            ],
        ]);

        return back()->with('success', "Restore selesai: {$result['restored_rows']} baris dari ".count($result['restored_tables']).' tabel berhasil dipulihkan.');
    }
}
