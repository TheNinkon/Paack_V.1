<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'google_maps_api_key')) {
                $table->string('google_maps_api_key')->nullable()->after('contact_phone');
            }
        });

        Schema::table('parcels', function (Blueprint $table) {
            if (! Schema::hasColumn('parcels', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('address_line');
            }

            if (! Schema::hasColumn('parcels', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }

            if (! Schema::hasColumn('parcels', 'formatted_address')) {
                $table->string('formatted_address')->nullable()->after('longitude');
            }

            $table->index(['courier_id', 'status', 'assigned_at'], 'parcels_courier_status_assigned_idx');
            $table->index(['latitude', 'longitude'], 'parcels_lat_lng_idx');
        });
    }

    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            if (Schema::hasColumn('parcels', 'formatted_address')) {
                $table->dropColumn('formatted_address');
            }

            if (Schema::hasColumn('parcels', 'longitude')) {
                $table->dropColumn('longitude');
            }

            if (Schema::hasColumn('parcels', 'latitude')) {
                $table->dropColumn('latitude');
            }

            $table->dropIndex('parcels_courier_status_assigned_idx');
            $table->dropIndex('parcels_lat_lng_idx');
        });

        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'google_maps_api_key')) {
                $table->dropColumn('google_maps_api_key');
            }
        });
    }
};
