<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (! Schema::hasColumn('admins', 'role')) {
                $table->string('role')->default('admin')->after('password');
            }

            if (! Schema::hasColumn('admins', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('role');
            }

            if (! Schema::hasColumn('admins', 'remember_token')) {
                $table->rememberToken()->after('email_verified_at');
            }
        });

        try {
            Schema::table('admins', function (Blueprint $table) {
                $table->index('role', 'admins_role_index');
            });
        } catch (\Throwable $exception) {
            // Index might already exist.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropIndex('admins_role_index');
            });
        } catch (\Throwable $exception) {
            // Ignore if it does not exist.
        }

        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'remember_token')) {
                $table->dropColumn('remember_token');
            }

            if (Schema::hasColumn('admins', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }

            if (Schema::hasColumn('admins', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
