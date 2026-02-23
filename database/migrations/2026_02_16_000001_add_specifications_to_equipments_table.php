<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('equipments')) {
            return;
        }

        Schema::table('equipments', function (Blueprint $table) {
            if (! Schema::hasColumn('equipments', 'specifications')) {
                $table->text('specifications')->nullable()->after('description');
            }
        });

        if (Schema::hasColumn('equipments', 'description') && Schema::hasColumn('equipments', 'specifications')) {
            DB::table('equipments')
                ->where(function ($query) {
                    $query->whereNull('specifications')
                        ->orWhere('specifications', '');
                })
                ->whereNotNull('description')
                ->where('description', '<>', '')
                ->update(['specifications' => DB::raw('description')]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('equipments')) {
            return;
        }

        Schema::table('equipments', function (Blueprint $table) {
            if (Schema::hasColumn('equipments', 'specifications')) {
                $table->dropColumn('specifications');
            }
        });
    }
};
