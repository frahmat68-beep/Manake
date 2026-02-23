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
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the profile completion form.
     */
    public function complete(Request $request): View
    {
        $profile = null;
        $profilesTableMissing = ! Schema::hasTable('profiles');

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
        if (! Schema::hasTable('profiles')) {
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
            'gender' => ['nullable', 'in:male,female,other'],
            'phone' => ['required', 'regex:/^(\+62|62|0)8[0-9]{8,13}$/', 'max:25'],
            'address_line' => ['required', 'string', 'max:255'],
            'kelurahan' => ['required', 'string', 'max:120'],
            'kecamatan' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:20'],
            'maps_url' => ['nullable', 'url', 'max:255'],
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

        $normalizedPhone = $this->normalizePhone($data['phone']);
        $phoneChanged = $profile->phone !== $normalizedPhone;

        $profile->fill([
            'full_name' => $data['full_name'],
            'nik' => $data['nik'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'] ?? null,
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
            'identity_number' => $data['nik'],
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

        if ($user->name !== $data['full_name']) {
            $user->name = $data['full_name'];
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
        $request->user()->fill($request->validated());

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
}
