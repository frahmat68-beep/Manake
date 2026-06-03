<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('profiles')) {
            Schema::table('profiles', function (Blueprint $table) {
                if (! Schema::hasColumn('profiles', 'rental_consent_accepted_at')) {
                    $table->timestamp('rental_consent_accepted_at')->nullable()->after('organization_type');
                }
                if (! Schema::hasColumn('profiles', 'rental_consent_ip')) {
                    $table->string('rental_consent_ip')->nullable()->after('rental_consent_accepted_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('profiles')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropColumn([
                    'rental_consent_accepted_at',
                    'rental_consent_ip',
                ]);
            });
        }
    }
};
