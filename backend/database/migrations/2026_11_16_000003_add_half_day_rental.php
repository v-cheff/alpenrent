<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            // Null = the rental point doesn't offer half-day rentals for this item.
            $table->decimal('half_day_rate', 8, 2)->nullable()->after('daily_rate');
        });

        Schema::table('bookings', function (Blueprint $table) {
            // "full_day": every date from start to end inclusive; "half_day": one date, from noon.
            $table->string('rental_type', 20)->default('full_day')->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('rental_type');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('half_day_rate');
        });
    }
};
