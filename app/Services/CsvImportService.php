<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

/**
 * Fase E (RDM): Impor CSV siswa dan guru tanpa dependensi baru
 * (fungsi CSV bawaan PHP: fgetcsv/fputcsv).
 *
 * Tiap baris divalidasi; baris valid dibuat, baris gagal dilaporkan
 * dengan nomor baris + alasan. Nomor baris dihitung dari file
 * (baris 1 = header). Baris kosong dilewati tanpa dihitung gagal.
 */
class CsvImportService
{
    public const STUDENT_HEADERS = ['nis', 'nisn', 'nama', 'gender', 'kelas', 'tanggal_lahir', 'alamat'];

    public const TEACHER_HEADERS = ['nip', 'nama', 'gender', 'telepon', 'alamat'];

    /**
     * Template CSV siswa (header saja).
     */
    public function studentTemplate(): string
    {
        return $this->buildCsv([self::STUDENT_HEADERS]);
    }

    /**
     * Template CSV guru (header saja).
     */
    public function teacherTemplate(): string
    {
        return $this->buildCsv([self::TEACHER_HEADERS]);
    }

    /**
     * Impor siswa dari file CSV.
     *
     * @return array{created: int, failed: int, errors: array<int, array{row: int, message: string}>}
     */
    public function importStudents(string $path): array
    {
        $rows = $this->readRows($path, self::STUDENT_HEADERS);

        $created = 0;
        $errors = [];
        $seenNis = [];
        $seenNisn = [];

        foreach ($rows as $row) {
            $line = $row['_line'];
            $data = $row['data'];

            $nis = trim((string) ($data['nis'] ?? ''));
            $nisn = trim((string) ($data['nisn'] ?? ''));
            $name = trim((string) ($data['nama'] ?? ''));
            $gender = strtoupper(trim((string) ($data['gender'] ?? '')));
            $className = trim((string) ($data['kelas'] ?? ''));
            $birthDate = trim((string) ($data['tanggal_lahir'] ?? ''));
            $address = trim((string) ($data['alamat'] ?? ''));

            $fail = function (string $message) use (&$errors, $line) {
                $errors[] = ['row' => $line, 'message' => $message];
            };

            if ($nis === '') {
                $fail('NIS wajib diisi.');

                continue;
            }

            if (isset($seenNis[$nis]) || Student::where('nis', $nis)->exists()) {
                $fail("NIS {$nis} sudah digunakan.");

                continue;
            }

            if ($nisn === '') {
                $fail('NISN wajib diisi.');

                continue;
            }

            if (isset($seenNisn[$nisn]) || Student::where('nisn', $nisn)->exists()) {
                $fail("NISN {$nisn} sudah digunakan.");

                continue;
            }

            if ($name === '') {
                $fail('Nama wajib diisi.');

                continue;
            }

            if (! in_array($gender, ['L', 'P'], true)) {
                $fail("Gender harus L atau P (diisi: {$gender}).");

                continue;
            }

            $class = null;

            if ($className === '') {
                $fail('Kelas wajib diisi.');

                continue;
            }

            $class = SchoolClass::where('name', $className)->first()
                ?? SchoolClass::whereRaw('LOWER(name) = ?', [mb_strtolower($className)])->first();

            if (! $class) {
                $fail("Kelas \"{$className}\" tidak ditemukan.");

                continue;
            }

            $birth = null;

            if ($birthDate !== '') {
                $parsed = $this->parseDate($birthDate);

                if ($parsed === null) {
                    $fail("Tanggal lahir \"{$birthDate}\" tidak valid (format: YYYY-MM-DD).");

                    continue;
                }

                $birth = $parsed;
            }

            try {
                DB::transaction(function () use ($nis, $nisn, $name, $gender, $class, $birth, $address) {
                    Student::create([
                        'nis' => $nis,
                        'nisn' => $nisn,
                        'name' => $name,
                        'gender' => $gender,
                        'class_id' => $class->id,
                        'birth_date' => $birth,
                        'address' => $address !== '' ? $address : null,
                    ]);
                });
            } catch (\Throwable $e) {
                $fail('Gagal menyimpan: '.$e->getMessage());

                continue;
            }

            $seenNis[$nis] = true;
            $seenNisn[$nisn] = true;
            $created++;
        }

        return ['created' => $created, 'failed' => count($errors), 'errors' => $errors];
    }

    /**
     * Impor guru dari file CSV. Hanya membuat data guru (tanpa akun
     * login).
     *
     * @return array{created: int, failed: int, errors: array<int, array{row: int, message: string}>}
     */
    public function importTeachers(string $path): array
    {
        $rows = $this->readRows($path, self::TEACHER_HEADERS);

        $created = 0;
        $errors = [];
        $seenNip = [];

        foreach ($rows as $row) {
            $line = $row['_line'];
            $data = $row['data'];

            $nip = trim((string) ($data['nip'] ?? ''));
            $name = trim((string) ($data['nama'] ?? ''));
            $gender = strtoupper(trim((string) ($data['gender'] ?? '')));
            $phone = trim((string) ($data['telepon'] ?? ''));
            $address = trim((string) ($data['alamat'] ?? ''));

            $fail = function (string $message) use (&$errors, $line) {
                $errors[] = ['row' => $line, 'message' => $message];
            };

            if ($nip === '') {
                $fail('NIP wajib diisi.');

                continue;
            }

            if (isset($seenNip[$nip]) || Teacher::where('nip', $nip)->exists()) {
                $fail("NIP {$nip} sudah digunakan.");

                continue;
            }

            if ($name === '') {
                $fail('Nama wajib diisi.');

                continue;
            }

            if (! in_array($gender, ['L', 'P'], true)) {
                $fail("Gender harus L atau P (diisi: {$gender}).");

                continue;
            }

            try {
                DB::transaction(function () use ($nip, $name, $gender, $phone, $address) {
                    Teacher::create([
                        'nip' => $nip,
                        'name' => $name,
                        'gender' => $gender,
                        'phone' => $phone !== '' ? $phone : null,
                        'address' => $address !== '' ? $address : null,
                    ]);
                });
            } catch (\Throwable $e) {
                $fail('Gagal menyimpan: '.$e->getMessage());

                continue;
            }

            $seenNip[$nip] = true;
            $created++;
        }

        return ['created' => $created, 'failed' => count($errors), 'errors' => $errors];
    }

    /**
     * Baca file CSV menjadi daftar ['_line' => int, 'data' => array].
     * Header wajib sama dengan $expectedHeaders (urutan bebas,
     * huruf besar/kecil bebas). Baris kosong dilewati.
     *
     * @throws \InvalidArgumentException bila file tidak bisa dibaca
     *         atau header tidak sesuai.
     */
    private function readRows(string $path, array $expectedHeaders): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \InvalidArgumentException('File CSV tidak bisa dibaca.');
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false) {
                throw new \InvalidArgumentException('File CSV kosong.');
            }

            $header = array_map(function ($cell) {
                $cell = trim((string) $cell);
                // Buang BOM UTF-8 di sel pertama.
                if (str_starts_with($cell, "\xEF\xBB\xBF")) {
                    $cell = substr($cell, 3);
                }

                return mb_strtolower($cell);
            }, $header);

            $missing = array_diff($expectedHeaders, $header);
            $unknown = array_diff($header, $expectedHeaders);

            if (! empty($missing) || ! empty($unknown)) {
                throw new \InvalidArgumentException(
                    'Header CSV tidak sesuai template. Kolom yang diharapkan: '.implode(', ', $expectedHeaders).'.'
                );
            }

            $rows = [];
            $line = 1;

            while (($cells = fgetcsv($handle)) !== false) {
                $line++;

                if ($cells === [null] || count(array_filter($cells, fn ($c) => trim((string) $c) !== '')) === 0) {
                    continue;
                }

                $data = [];

                foreach ($header as $i => $name) {
                    $data[$name] = $cells[$i] ?? '';
                }

                $rows[] = ['_line' => $line, 'data' => $data];
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Terima tanggal format YYYY-MM-DD (atau variasi umum yang bisa
     * dibaca strtotime dengan komponen yang sama); kembalikan
     * YYYY-MM-DD atau null bila tidak valid.
     */
    private function parseDate(string $value): ?string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            [$y, $m, $d] = array_map('intval', explode('-', $value));

            return checkdate($m, $d, $y) ? $value : null;
        }

        $ts = strtotime($value);

        if ($ts === false) {
            return null;
        }

        return date('Y-m-d', $ts);
    }

    /**
     * Bangun isi CSV dari daftar baris.
     */
    private function buildCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
