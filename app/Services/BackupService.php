<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Fase E (RDM): Backup & Restore seluruh data aplikasi.
 *
 * Backup adalah SATU file JSON berisi semua tabel aplikasi (kecuali
 * tabel infrastruktur Laravel: cache, jobs, sessions,
 * password_reset_tokens). Format:
 * {"app":"e-rapor-smk","version":1,"exported_at":"...","tables":{...}}
 *
 * Password hash user ikut tercadangkan apa adanya (itu cadangan) dan
 * tidak pernah ditampilkan di UI. Restore mengganti seluruh data:
 * tiap tabel yang ada di cadangan dikosongkan lalu diisi ulang dalam
 * satu transaction, dengan foreign key constraints dimatikan selama
 * impor. Cadangan yang tidak berisi akun admin aktif ditolak, supaya
 * admin tidak terkunci keluar dari aplikasinya sendiri.
 */
class BackupService
{
    public const APP = 'e-rapor-smk';

    public const VERSION = 1;

    /**
     * Semua tabel aplikasi yang ikut dicadangkan, diurutkan dari
     * tabel induk ke tabel anak (dipakai juga saat restore; urutan
     * tidak kritis karena FK dimatikan selama impor).
     */
    public const TABLES = [
        'roles',
        'users',
        'departments',
        'academic_years',
        'semesters',
        'teachers',
        'classes',
        'students',
        'subjects',
        'curricula',
        'learning_outcomes',
        'learning_objectives',
        'teaching_assignments',
        'homeroom_assignments',
        'assessment_components',
        'assessment_scores',
        'score_revisions',
        'teacher_descriptions',
        'student_attendances',
        'student_achievements',
        'extracurriculars',
        'student_extracurriculars',
        'report_cards',
        'report_card_subjects',
        'report_card_notes',
        'report_card_approvals',
        'internships',
        'internship_assessments',
        'competency_exams',
        'student_attitudes',
        'p5_projects',
        'p5_scores',
        'promotion_decisions',
        'system_settings',
        'activity_logs',
    ];

    /**
     * Ekspor seluruh data sebagai array siap json_encode.
     * Query builder dipakai (bukan Eloquent) supaya nilai kolom JSON
     * (system_settings.value, activity_logs.metadata) dan hash
     * password tersimpan apa adanya tanpa cast ulang.
     */
    public function export(): array
    {
        $tables = [];

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $tables[$table] = DB::table($table)->orderBy('id')->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        }

        return [
            'app' => self::APP,
            'version' => self::VERSION,
            'exported_at' => now()->toIso8601String(),
            'tables' => $tables,
        ];
    }

    /**
     * Nama file unduhan: backup-rapor-YYYY-MM-DD-HHmm.json.
     */
    public function fileName(): string
    {
        return 'backup-rapor-'.now()->format('Y-m-d-Hi').'.json';
    }

    /**
     * Validasi struktur cadangan. Mengembalikan daftar tabel yang
     * ada di cadangan. Melempar ValidationException bila format
     * tidak valid (bukan JSON cadangan aplikasi ini, tabel tidak
     * dikenal, atau isi tabel bukan array baris).
     */
    public function validate(array $data): array
    {
        if (($data['app'] ?? null) !== self::APP) {
            throw ValidationException::withMessages([
                'backup' => 'File bukan cadangan e-Rapor SMK (penanda aplikasi tidak ditemukan).',
            ]);
        }

        if (($data['version'] ?? null) !== self::VERSION) {
            throw ValidationException::withMessages([
                'backup' => 'Versi format cadangan tidak didukung (diharapkan versi '.self::VERSION.').',
            ]);
        }

        if (! isset($data['tables']) || ! is_array($data['tables']) || empty($data['tables'])) {
            throw ValidationException::withMessages([
                'backup' => 'File cadangan tidak berisi data tabel.',
            ]);
        }

        foreach ($data['tables'] as $table => $rows) {
            if (! in_array($table, self::TABLES, true)) {
                throw ValidationException::withMessages([
                    'backup' => "Tabel tidak dikenal di cadangan: {$table}.",
                ]);
            }

            if (! is_array($rows)) {
                throw ValidationException::withMessages([
                    'backup' => "Isi tabel {$table} bukan daftar baris.",
                ]);
            }

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    throw ValidationException::withMessages([
                        'backup' => "Baris pada tabel {$table} bukan objek data.",
                    ]);
                }
            }
        }

        return array_keys($data['tables']);
    }

    /**
     * Pastikan cadangan berisi minimal satu akun admin aktif, kalau
     * tidak restore akan mengunci admin keluar. Mengembalikan true
     * bila ada user aktif yang role-nya berslug 'admin' menurut
     * tabel roles di cadangan. Bila tabel users/roles tidak ada di
     * cadangan, dianggap tidak ada admin.
     */
    public function hasActiveAdmin(array $data): bool
    {
        $tables = $data['tables'] ?? [];

        if (! isset($tables['users']) || ! isset($tables['roles'])) {
            return false;
        }

        $adminRoleIds = collect($tables['roles'])
            ->where('slug', 'admin')
            ->pluck('id')
            ->all();

        if (empty($adminRoleIds)) {
            return false;
        }

        foreach ($tables['users'] as $user) {
            if (in_array($user['role_id'] ?? null, $adminRoleIds, false)
                && ! empty($user['is_active'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Restore dari array cadangan yang sudah di-decode. Seluruh
     * impor berjalan dalam satu transaction. Bila format tidak valid
     * atau tidak ada admin aktif, data tidak diubah sama sekali.
     *
     * Foreign key constraints dimatikan selama impor (di MySQL dan
     * PostgreSQL ini bekerja di dalam transaction). Di SQLite pragma
     * FK adalah no-op di dalam transaction, jadi sebagai pengaman
     * pengosongan memakai urutan anak-dulu (kebalikan daftar tabel)
     * dan pengisian memakai urutan induk-dulu. Satu-satunya siklus
     * FK (users.teacher_id <-> teachers.user_id) ditangani eksplisit:
     * users dimasukkan dulu dengan teacher_id null, lalu teacher_id
     * dipulihkan setelah teachers dimasukkan.
     *
     * @return array{restored_tables: string[], restored_rows: int}
     */
    public function restore(array $data): array
    {
        $tables = $this->validate($data);

        if (! $this->hasActiveAdmin($data)) {
            throw ValidationException::withMessages([
                'backup' => 'Cadangan ditolak: tidak berisi akun admin yang aktif. Restore akan mengganti seluruh data termasuk akun admin, sehingga Anda bisa terkunci keluar.',
            ]);
        }

        $restoredRows = 0;
        $userTeacherMap = [];

        // teacher_id users ditunda sampai teachers terisi (siklus FK).
        if (isset($data['tables']['users'])) {
            foreach ($data['tables']['users'] as &$user) {
                if (! empty($user['teacher_id'])) {
                    $userTeacherMap[$user['id']] = $user['teacher_id'];
                    $user['teacher_id'] = null;
                }
            }
            unset($user);
        }

        Schema::disableForeignKeyConstraints();

        try {
            DB::transaction(function () use ($data, $tables, $userTeacherMap, &$restoredRows) {
                // Kosongkan anak-dulu supaya FK restrict tidak menolak.
                foreach (array_reverse($tables) as $table) {
                    DB::table($table)->truncate();
                }

                // Isi induk-dulu supaya FK terpenuhi saat insert.
                foreach ($tables as $table) {
                    foreach ($data['tables'][$table] as $row) {
                        DB::table($table)->insert($row);
                        $restoredRows++;
                    }
                }

                // Pulihkan kaitan users.teacher_id setelah teachers terisi.
                foreach ($userTeacherMap as $userId => $teacherId) {
                    DB::table('users')->where('id', $userId)->update(['teacher_id' => $teacherId]);
                }
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return [
            'restored_tables' => $tables,
            'restored_rows' => $restoredRows,
        ];
    }
}
