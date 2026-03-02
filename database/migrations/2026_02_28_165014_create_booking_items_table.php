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
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('schedule_trip_id')->constrained('schedule_trips')->onDelete('cascade');
            $table->enum('type', ['go_way', 'return_way'])->comment('go_way = ذهاب، return_way = عودة');
            $table->integer('number_of_seats');
            $table->decimal('price', 8, 2);
            $table->string('from_city');
            $table->string('to_city');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
