<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Mail\OtpMail;
use Throwable;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $data = [
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        if (schema_column_exists_cached('users', 'role')) {
            $data['role'] = 'user';
        }

        if (schema_column_exists_cached('users', 'name')) {
            $data['name'] = Str::before($request->email, '@');
        }

        if (schema_column_exists_cached('users', 'nik')) {
            $data['nik'] = null;
        }

        if (schema_column_exists_cached('users', 'phone')) {
            $data['phone'] = null;
        }

        if (schema_column_exists_cached('users', 'address')) {
            $data['address'] = null;
        }

        if (schema_column_exists_cached('users', 'is_otp_verified')) {
            $data['is_otp_verified'] = ! config('security.otp_required');
        }

        $user = User::create($data);
        event(new Registered($user));

        Auth::login($user);

        if (schema_table_exists_cached('profiles')) {
            $user->profile()->create([
                'full_name' => null,
                'nik' => null,
                'date_of_birth' => null,
                'gender' => null,
                'phone' => null,
                'phone_verified_at' => null,
                'address_line' => null,
                'kelurahan' => null,
                'kecamatan' => null,
                'city' => null,
                'province' => null,
                'postal_code' => null,
                'maps_url' => null,
                'emergency_name' => null,
                'emergency_relation' => null,
                'emergency_phone' => null,
                'address' => null,
                'identity_number' => null,
                'emergency_contact' => null,
                'is_completed' => false,
                'completed_at' => null,
            ]);
        }

        if (config('security.otp_required') && schema_column_exists_cached('users', 'is_otp_verified')) {
            try {
                $otp = $user->generateOtp();
                Mail::to($user->email)->send(new OtpMail($otp));
                Log::info('Registration OTP sent.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } catch (Throwable $exception) {
                Log::error('Registration OTP failed.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $exception->getMessage(),
                ]);
            }
            $request->session()->put('otp_verified', false);

            return redirect()
                ->route('otp.form')
                ->with('status', __('Kode OTP sudah dikirim ke email kamu.'));
        }

        $request->session()->put('otp_verified', true);

        return redirect()->route('profile.complete');
    }
}
