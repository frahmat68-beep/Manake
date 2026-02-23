<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateUsersTable();
        $this->updateProfilesTable();
    }

    public function down(): void
    {
        // Keep data-safe rollback.
    }

    private function updateUsersTable(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nik')) {
                $table->string('nik')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->change();
            }
        });
    }

    private function updateProfilesTable(): void
    {
        if (! Schema::hasTable('profiles')) {
            Schema::create('profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
                $table->string('full_name')->nullable();
                $table->string('nik', 16)->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('gender', 20)->nullable();
                $table->string('phone')->nullable();
                $table->timestamp('phone_verified_at')->nullable();
                $table->string('address_line')->nullable();
                $table->string('kelurahan')->nullable();
                $table->string('kecamatan')->nullable();
                $table->string('city')->nullable();
                $table->string('province')->nullable();
                $table->string('postal_code', 20)->nullable();
                $table->string('maps_url')->nullable();
                $table->string('emergency_name')->nullable();
                $table->string('emergency_relation')->nullable();
                $table->string('emergency_phone')->nullable();
                $table->boolean('is_completed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });

            $this->ensureProfileIndexes();

            return;
        }

        Schema::table('profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('profiles', 'nik')) {
                $table->string('nik', 16)->nullable()->after('full_name');
            }
            if (! Schema::hasColumn('profiles', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('nik');
            }
            if (! Schema::hasColumn('profiles', 'gender')) {
                $table->string('gender', 20)->nullable()->after('date_of_birth');
            }
            if (! Schema::hasColumn('profiles', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('profiles', 'address_line')) {
                $table->string('address_line')->nullable()->after('phone_verified_at');
            }
            if (! Schema::hasColumn('profiles', 'kelurahan')) {
                $table->string('kelurahan')->nullable()->after('address_line');
            }
            if (! Schema::hasColumn('profiles', 'kecamatan')) {
                $table->string('kecamatan')->nullable()->after('kelurahan');
            }
            if (! Schema::hasColumn('profiles', 'province')) {
                $table->string('province')->nullable()->after('city');
            }
            if (! Schema::hasColumn('profiles', 'postal_code')) {
                $table->string('postal_code', 20)->nullable()->after('province');
            }
            if (! Schema::hasColumn('profiles', 'maps_url')) {
                $table->string('maps_url')->nullable()->after('postal_code');
            }
            if (! Schema::hasColumn('profiles', 'emergency_name')) {
                $table->string('emergency_name')->nullable()->after('maps_url');
            }
            if (! Schema::hasColumn('profiles', 'emergency_relation')) {
                $table->string('emergency_relation')->nullable()->after('emergency_name');
            }
            if (! Schema::hasColumn('profiles', 'emergency_phone')) {
                $table->string('emergency_phone')->nullable()->after('emergency_relation');
            }
            if (! Schema::hasColumn('profiles', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('is_completed');
            }
        });

        if (
            Schema::hasColumn('profiles', 'identity_number') &&
            Schema::hasColumn('profiles', 'nik') &&
            Schema::hasColumn('profiles', 'full_name')
        ) {
            DB::table('profiles')
                ->whereNull('nik')
                ->whereNotNull('identity_number')
                ->update(['nik' => DB::raw('identity_number')]);
        }

        if (Schema::hasColumn('profiles', 'address') && Schema::hasColumn('profiles', 'address_line')) {
            DB::table('profiles')
                ->whereNull('address_line')
                ->whereNotNull('address')
                ->update(['address_line' => DB::raw('address')]);
        }

        if (Schema::hasColumn('profiles', 'emergency_contact') && Schema::hasColumn('profiles', 'emergency_name')) {
            DB::table('profiles')
                ->whereNull('emergency_name')
                ->whereNotNull('emergency_contact')
                ->update(['emergency_name' => DB::raw('emergency_contact')]);
        }

        if (Schema::hasColumn('profiles', 'is_completed') && Schema::hasColumn('profiles', 'completed_at')) {
            DB::table('profiles')
                ->where('is_completed', true)
                ->whereNull('completed_at')
                ->update(['completed_at' => now()]);
        }

        $this->ensureProfileIndexes();
    }

    private function ensureProfileIndexes(): void
    {
        try {
            Schema::table('profiles', function (Blueprint $table) {
                $table->unique('nik', 'profiles_nik_unique');
            });
        } catch (\Throwable $exception) {
            // Keep migration idempotent on existing environments.
        }

        try {
            Schema::table('profiles', function (Blueprint $table) {
                $table->index('phone', 'profiles_phone_index');
            });
        } catch (\Throwable $exception) {
            // Keep migration idempotent on existing environments.
        }
    }
};
