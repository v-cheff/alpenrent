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
        Schema::create('equipment', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('category');
            $table->string('category_label');
            $table->string('badge')->nullable();
            $table->string('badge_tone')->nullable();
            $table->string('image', 2048);
            $table->json('specs');
            $table->decimal('daily_rate', 8, 2);
            $table->decimal('deposit', 8, 2);
            $table->json('sizes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
