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
                if (! Schema::hasColumn('profiles', 'alternative_phone')) {
                    $table->string('alternative_phone')->nullable()->after('phone');
                }
                if (! Schema::hasColumn('profiles', 'instagram_handle')) {
                    $table->string('instagram_handle')->nullable()->after('alternative_phone');
                }
                if (! Schema::hasColumn('profiles', 'organization_name')) {
                    $table->string('organization_name')->nullable()->after('instagram_handle');
                }
                if (! Schema::hasColumn('profiles', 'organization_type')) {
                    $table->string('organization_type')->nullable()->after('organization_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('profiles')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropColumn([
                    'alternative_phone',
                    'instagram_handle',
                    'organization_name',
                    'organization_type',
                ]);
            });
        }
    }
};
