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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('discount_id')->nullable()->constrained('discounts')->onDelete('set null');
            $table->string('customer_name');
            $table->string('phone_number');
            $table->decimal('subtotal_price', 8, 2)->default(0)->comment('السعر قبل الخصم');
            $table->decimal('discount_amount', 8, 2)->default(0)->comment('قيمة الخصم');
            $table->decimal('total_price', 8, 2)->comment('إجمالي السعر بعد الخصم');
            $table->string('status')->default('pending')->comment('pending, confirmed, cancelled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
