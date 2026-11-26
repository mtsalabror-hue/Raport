<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SchoolProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolProfileController extends Controller
{
    public function __construct(private SchoolProfileService $profile)
    {
    }

    // -----------------------------------------------------------------
    // Profil Sekolah
    // -----------------------------------------------------------------

    public function edit()
    {
        return view('admin.school_profile.edit', [
            'profile' => $this->profile->profile(),
            'logoUrl' => $this->profile->logoUrl(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_npsn' => ['nullable', 'string', 'max:20'],
            'school_address' => ['nullable', 'string', 'max:500'],
            'school_city' => ['nullable', 'string', 'max:100'],
            'school_phone' => ['nullable', 'string', 'max:30'],
            'principal_name' => ['nullable', 'string', 'max:255'],
            'principal_nip' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $oldLogo = $this->profile->get('school_logo');
            $path = $request->file('logo')->store('school', 'public');
            $data['school_logo'] = $path;

            if ($oldLogo && $oldLogo !== $path && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
        }

        unset($data['logo']);
        $this->profile->save($data);

        return back()->with('success', 'Profil sekolah disimpan.');
    }

    // -----------------------------------------------------------------
    // Pengaturan Cetak
    // -----------------------------------------------------------------

    public function editPrint()
    {
        return view('admin.print_settings.edit', [
            'settings' => $this->profile->printSettings(),
        ]);
    }

    public function updatePrint(Request $request)
    {
        $data = $request->validate([
            'print_city' => ['nullable', 'string', 'max:100'],
            'print_date' => ['nullable', 'date'],
            'print_watermark' => ['nullable', 'boolean'],
            'principal_name' => ['nullable', 'string', 'max:255'],
            'principal_nip' => ['nullable', 'string', 'max:30'],
        ]);

        $data['print_watermark'] = $request->boolean('print_watermark');
        $this->profile->save($data);

        return back()->with('success', 'Pengaturan cetak disimpan.');
    }
}
