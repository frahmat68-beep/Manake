<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $nameLocked = false;

        if (schema_table_exists_cached('profiles') && $request->user()) {
            $lockedName = trim((string) ($request->user()->profile()->value('full_name') ?? ''));
            $nameLocked = $lockedName !== '';
        }

        return view('profile.edit', [
            'user' => $request->user(),
            'nameLocked' => $nameLocked,
        ]);
    }

    /**
     * Display the profile completion form.
     */
    public function complete(Request $request): View
    {
        $profile = null;
        $profilesTableMissing = ! schema_table_exists_cached('profiles');

        if (! $profilesTableMissing && $request->user()) {
            $profile = $request->user()->profile()->firstOrCreate([], [
                'is_completed' => false,
            ]);
        }

        return view('profile.complete', [
            'user' => $request->user(),
            'profile' => $profile,
            'profilesTableMissing' => $profilesTableMissing,
        ]);
    }

    /**
     * Store profile completion data.
     */
    public function storeCompletion(Request $request): RedirectResponse
    {
        if (! schema_table_exists_cached('profiles')) {
            return redirect()
                ->route('profile.complete')
                ->with('error', __('Profil belum siap, jalankan migrasi terlebih dahulu.'));
        }

        $user = $request->user();
        $profile = $user->profile()->firstOrCreate([], [
            'is_completed' => false,
        ]);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'regex:/^[0-9]{16}$/', 'unique:profiles,nik,' . $profile->id],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'phone' => ['required', 'regex:/^(\+62|62|0)8[0-9]{8,13}$/', 'max:25'],
            'address_line' => ['required', 'string', 'max:255'],
            'kelurahan' => ['required', 'string', 'max:120'],
            'kecamatan' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:20'],
            'maps_url' => [
                'nullable',
                'url',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || trim($value) === '') {
                        return;
                    }

                    if (! $this->isTrustedMapsUrl($value)) {
                        $fail(__('Link Google Maps tidak valid.'));
                    }
                },
            ],
            'emergency_name' => ['required', 'string', 'max:120'],
            'emergency_relation' => ['required', 'string', 'max:80'],
            'emergency_phone' => ['required', 'regex:/^(\+62|62|0)8[0-9]{8,13}$/', 'max:25'],
        ], [
            'nik.regex' => __('NIK harus 16 digit angka.'),
            'nik.unique' => __('NIK sudah terdaftar.'),
            'phone.regex' => __('Format nomor telepon tidak valid.'),
            'emergency_phone.regex' => __('Format nomor kontak darurat tidak valid.'),
            'maps_url.url' => __('Link Google Maps tidak valid.'),
        ]);

        $incomingFullName = trim((string) ($data['full_name'] ?? ''));
        $incomingNik = $this->normalizeNik((string) ($data['nik'] ?? ''));
        $existingFullName = trim((string) ($profile->full_name ?? ''));
        $existingNik = $this->normalizeNik((string) ($profile->nik ?? ''));

        if ($existingFullName !== '' && strcasecmp($existingFullName, $incomingFullName) !== 0) {
            return redirect()
                ->route('profile.complete', ['edit' => 1])
                ->withInput()
                ->with('error', __('Nama yang sudah tersimpan tidak dapat diubah demi keamanan data.'));
        }

        if ($existingNik !== '' && $existingNik !== $incomingNik) {
            return redirect()
                ->route('profile.complete', ['edit' => 1])
                ->withInput()
                ->with('error', __('NIK yang sudah tersimpan tidak dapat diubah demi keamanan data.'));
        }

        $resolvedFullName = $existingFullName !== '' ? $existingFullName : $incomingFullName;
        $resolvedNik = $existingNik !== '' ? $existingNik : $incomingNik;
        $normalizedPhone = $this->normalizePhone($data['phone']);
        $phoneChanged = $profile->phone !== $normalizedPhone;

        $profile->fill([
            'full_name' => $resolvedFullName,
            'nik' => $resolvedNik,
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'phone' => $normalizedPhone,
            'address_line' => $data['address_line'],
            'kelurahan' => $data['kelurahan'],
            'kecamatan' => $data['kecamatan'],
            'city' => $data['city'],
            'province' => $data['province'],
            'postal_code' => $data['postal_code'],
            'maps_url' => $data['maps_url'] ?? null,
            'emergency_name' => $data['emergency_name'],
            'emergency_relation' => $data['emergency_relation'],
            'emergency_phone' => $this->normalizePhone($data['emergency_phone']),
            // Backward-compatible fields
            'identity_number' => $resolvedNik,
            'address' => $data['address_line'],
            'emergency_contact' => $data['emergency_name'] . ' (' . $data['emergency_relation'] . ') - ' . $this->normalizePhone($data['emergency_phone']),
        ]);

        if ($phoneChanged) {
            $profile->phone_verified_at = null;
        }

        $user->setRelation('profile', $profile);
        $profile->is_completed = $user->profileIsComplete();
        $profile->completed_at = $profile->is_completed ? ($profile->completed_at ?: now()) : null;
        $profile->save();

        if ($existingFullName === '' && $user->name !== $resolvedFullName) {
            $user->name = $resolvedFullName;
            $user->save();
        }

        if (! $user->hasVerifiedPhone()) {
            return redirect()
                ->route('phone.verify')
                ->with('status', __('Profil tersimpan. Lanjut verifikasi nomor telepon untuk aktivasi checkout.'));
        }

        return redirect()
            ->route('profile.complete')
            ->with('status', 'profile-completed')
            ->with('success', __('ui.profile_complete.saved_badge'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (schema_table_exists_cached('profiles')) {
            $profile = $request->user()->profile()->first();
            $lockedName = trim((string) ($profile?->full_name ?? ''));

            if ($lockedName !== '') {
                $incomingName = trim((string) ($validated['name'] ?? ''));

                if ($incomingName !== '' && strcasecmp($incomingName, $lockedName) !== 0) {
                    return Redirect::back()
                        ->withInput($request->except('password'))
                        ->withErrors([
                            'name' => __('Nama akun sudah dikunci mengikuti data identitas dan tidak dapat diubah.'),
                        ]);
                }

                $validated['name'] = $lockedName;
            }
        }

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone) ?: '';
        if ($digits === '') {
            return $phone;
        }

        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }

        return str_starts_with($digits, '0') ? $digits : ('0' . $digits);
    }

    private function normalizeNik(string $nik): string
    {
        return preg_replace('/[^0-9]/', '', $nik) ?: '';
    }

    private function isTrustedMapsUrl(string $value): bool
    {
        $parts = parse_url($value);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = strtolower((string) ($parts['path'] ?? ''));

        if ($host === '') {
            return false;
        }

        if (in_array($host, ['maps.google.com', 'maps.app.goo.gl'], true)) {
            return true;
        }

        if (in_array($host, ['google.com', 'www.google.com'], true)) {
            return str_starts_with($path, '/maps') || str_contains($path, '/maps/');
        }

        if (in_array($host, ['goo.gl', 'www.goo.gl'], true)) {
            return str_starts_with($path, '/maps');
        }

        return false;
    }
}
