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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->date('notification_date')->comment('Ngày gửi thông báo');
            $table->string('type', 50)->comment('Loại thông báo (contract_expiration, ...)');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending')->comment('Trạng thái');
            $table->integer('retry_count')->default(0)->comment('Số lần thử lại');
            $table->text('error_message')->nullable()->comment('Lỗi nếu có');
            $table->json('data')->nullable()->comment('Dữ liệu gửi (JSON)');
            $table->timestamps();
            
            $table->index(['notification_date', 'type']);
            $table->unique(['notification_date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
