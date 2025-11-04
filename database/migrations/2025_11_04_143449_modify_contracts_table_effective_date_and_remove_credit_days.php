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
        Schema::table('contracts', function (Blueprint $table) {
            // Xóa cột credit_days
            $table->dropColumn('credit_days');
            
            // Đổi effective_date từ date sang integer và string
            $table->dropColumn('effective_date');
            $table->unsignedInteger('effective_period_value')->comment('Giá trị hiệu lực (số)');
            $table->string('effective_period_type', 10)->comment('Loại hiệu lực: day hoặc month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Khôi phục lại
            $table->date('effective_date')->comment('Hiệu lực hợp đồng');
            $table->unsignedInteger('credit_days')->nullable()->comment('Số ngày được nợ');
            $table->dropColumn(['effective_period_value', 'effective_period_type']);
        });
    }
};
