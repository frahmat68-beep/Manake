<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'midtrans_order_id')) {
                $table->string('midtrans_order_id')->nullable()->after('provider');
            }
            if (! Schema::hasColumn('payments', 'gross_amount')) {
                $table->unsignedBigInteger('gross_amount')->default(0)->after('transaction_status');
            }
            if (! Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('payments', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('paid_at');
            }
        });

        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->index(['order_id', 'status'], 'payments_order_status_idx');
            });
        } catch (\Throwable $exception) {
            // Keep migration idempotent.
        }

        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->index('midtrans_order_id', 'payments_midtrans_order_id_idx');
            });
        } catch (\Throwable $exception) {
            // Keep migration idempotent.
        }

        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->unique('transaction_id', 'payments_transaction_id_unique');
            });
        } catch (\Throwable $exception) {
            // Keep migration idempotent.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'expired_at')) {
                $table->dropColumn('expired_at');
            }
            if (Schema::hasColumn('payments', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('payments', 'gross_amount')) {
                $table->dropColumn('gross_amount');
            }
            if (Schema::hasColumn('payments', 'midtrans_order_id')) {
                $table->dropColumn('midtrans_order_id');
            }
        });
    }
};
