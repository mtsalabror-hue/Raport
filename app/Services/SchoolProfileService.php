<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

/**
 * Profil sekolah + pengaturan cetak (Fase A penyelarasan RDM).
 *
 * Disimpan sebagai baris key/value di system_settings — pola yang
 * sudah dipakai aplikasi (mis. school_name, kktp_default) — jadi
 * tidak perlu tabel/model baru. Setiap baris menyimpan
 * ['value' => ...] sesuai cast array pada SystemSetting.
 *
 * Kunci profil: school_name, school_npsn, school_address,
 * school_city, school_phone, school_logo (path relatif di disk
 * public, mis. school/logo.jpg), principal_name, principal_nip.
 * Kunci cetak: print_city, print_date (Y-m-d), print_watermark
 * (bool). Nama kepala sekolah + NIP dipakai bersama oleh halaman
 * Profil Sekolah dan Pengaturan Cetak.
 */
class SchoolProfileService
{
    public const PROFILE_KEYS = [
        'school_name',
        'school_npsn',
        'school_address',
        'school_city',
        'school_phone',
        'school_logo',
        'principal_name',
        'principal_nip',
    ];

    public const PRINT_KEYS = [
        'print_city',
        'print_date',
        'print_watermark',
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        $row = SystemSetting::query()->where('key', $key)->first();

        if (! $row) {
            return $default;
        }

        return $row->value['value'] ?? $default;
    }

    /**
     * Seluruh profil sekolah; school_name memakai fallback
     * 'SMK IT AL-ABROR' bila belum pernah diisi.
     */
    public function profile(): array
    {
        $data = [];
        foreach (self::PROFILE_KEYS as $key) {
            $data[$key] = $this->get($key);
        }

        $data['school_name'] = $data['school_name'] ?: 'SMK IT AL-ABROR';

        return $data;
    }

    /**
     * Pengaturan cetak; kota penerbitan memakai kota sekolah bila
     * belum diisi, tanggal memakai hari ini, watermark mati.
     */
    public function printSettings(): array
    {
        $city = $this->get('print_city') ?: $this->get('school_city');

        return [
            'print_city' => $city,
            'print_date' => $this->get('print_date') ?: now()->toDateString(),
            'print_watermark' => (bool) ($this->get('print_watermark') ?? false),
            'principal_name' => $this->get('principal_name'),
            'principal_nip' => $this->get('principal_nip'),
        ];
    }

    public function save(array $data): void
    {
        foreach (array_merge(self::PROFILE_KEYS, self::PRINT_KEYS) as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => ['value' => $data[$key]]]
            );
        }
    }

    /**
     * URL publik logo sekolah (untuk layout); null bila belum ada.
     */
    public function logoUrl(): ?string
    {
        $path = $this->get('school_logo');

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Path absolut logo di filesystem (untuk DomPDF, yang tidak
     * boleh memakai URL http); null bila belum ada.
     */
    public function logoAbsolutePath(): ?string
    {
        $path = $this->get('school_logo');

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return storage_path('app/public/'.$path);
    }
}
