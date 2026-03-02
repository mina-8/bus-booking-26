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
        Schema::table('booking_items', function (Blueprint $table) {
            $table->foreignId('bus_id')->nullable()->after('schedule_trip_id')->constrained('buses')->onDelete('cascade');
            $table->json('seat_numbers')->nullable()->after('number_of_seats')->comment('أرقام المقاعد المحجوزة');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropForeign(['bus_id']);
            $table->dropColumn(['bus_id', 'seat_numbers']);
        });
    }
};
