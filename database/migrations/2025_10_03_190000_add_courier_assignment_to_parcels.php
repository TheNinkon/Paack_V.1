<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            if (! Schema::hasColumn('parcels', 'courier_id')) {
                $table->foreignId('courier_id')
                    ->nullable()
                    ->after('provider_barcode_id')
                    ->constrained()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('parcels', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('courier_id');
            }

            $table->index(['courier_id', 'status'], 'parcels_courier_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            if (Schema::hasColumn('parcels', 'courier_id')) {
                $table->dropIndex('parcels_courier_id_status_index');
            }

            if (Schema::hasColumn('parcels', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }

            if (Schema::hasColumn('parcels', 'courier_id')) {
                $table->dropForeign(['courier_id']);
                $table->dropColumn('courier_id');
            }
        });
    }
};
