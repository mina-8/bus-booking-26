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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('كود الخصم');
            $table->text('description')->nullable()->comment('وصف الخصم');
            $table->enum('type', ['percentage', 'fixed'])->default('percentage')->comment('نوع الخصم: نسبة مئوية أو قيمة ثابتة');
            $table->decimal('value', 8, 2)->comment('قيمة الخصم (نسبة أو مبلغ)');
            $table->decimal('min_amount', 8, 2)->nullable()->comment('الحد الأدنى للمبلغ لتطبيق الخصم');
            $table->decimal('max_discount', 8, 2)->nullable()->comment('الحد الأقصى للخصم (للنسبة المئوية)');
            $table->integer('usage_limit')->nullable()->comment('عدد مرات الاستخدام المسموح');
            $table->integer('used_count')->default(0)->comment('عدد مرات الاستخدام الفعلية');
            $table->timestamp('starts_at')->nullable()->comment('تاريخ بداية الخصم');
            $table->timestamp('expires_at')->nullable()->comment('تاريخ انتهاء الخصم');
            $table->boolean('is_active')->default(true)->comment('حالة تفعيل الخصم');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
