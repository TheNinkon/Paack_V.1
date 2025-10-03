<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->foreignId('parcel_id')
                ->nullable()
                ->after('client_id')
                ->constrained('parcels')
                ->nullOnDelete();
        });

        Schema::table('parcel_events', function (Blueprint $table) {
            $table->foreignId('parcel_id')
                ->nullable()
                ->after('scan_id')
                ->constrained('parcels')
                ->nullOnDelete();

            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcel_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parcel_id');
            $table->dropIndex(['parcel_events_event_type_index']);
        });

        Schema::table('scans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parcel_id');
        });
    }
};
