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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_one_id')->constrained('cities')->onDelete('cascade');
            $table->foreignId('city_two_id')->constrained('cities')->onDelete('cascade');
            $table->decimal('price', 8, 2);
            $table->decimal('round_trip_price', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['city_one_id', 'city_two_id'], 'unique_trip_route');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
