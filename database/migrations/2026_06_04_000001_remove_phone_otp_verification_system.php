<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove phone OTP verification system.
     *
     * Drops phone_verifications table and OTP-related columns that
     * are no longer used after removing the phone OTP verification workflow.
     * Email verification and identity data remain intact.
     */
    public function up(): void
    {
        // Drop phone_verifications table if it exists
        if (Schema::hasTable('phone_verifications')) {
            Schema::dropIfExists('phone_verifications');
        }

        // Drop phone_verified_at from profiles if it exists
        if (Schema::hasTable('profiles') && Schema::hasColumn('profiles', 'phone_verified_at')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropColumn('phone_verified_at');
            });
        }

        // Drop OTP login columns from users if they exist
        if (Schema::hasTable('users')) {
            $otpColumns = ['otp_code', 'otp_expires_at', 'is_otp_verified'];
            $toDrop = array_filter($otpColumns, fn ($col) => Schema::hasColumn('users', $col));

            if ($toDrop !== []) {
                Schema::table('users', function (Blueprint $table) use ($toDrop) {
                    $table->dropColumn(array_values($toDrop));
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     * Recreates the dropped columns/table for rollback safety.
     */
    public function down(): void
    {
        // Recreate phone_verified_at in profiles
        if (Schema::hasTable('profiles') && ! Schema::hasColumn('profiles', 'phone_verified_at')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->timestamp('phone_verified_at')->nullable()->after('phone');
            });
        }

        // Recreate OTP login columns in users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'otp_code')) {
                    $table->string('otp_code')->nullable();
                }
                if (! Schema::hasColumn('users', 'otp_expires_at')) {
                    $table->timestamp('otp_expires_at')->nullable();
                }
                if (! Schema::hasColumn('users', 'is_otp_verified')) {
                    $table->boolean('is_otp_verified')->default(false);
                }
            });
        }

        // Note: phone_verifications table not recreated – structure was complex.
        // Re-run the original migration if needed.
    }
};
