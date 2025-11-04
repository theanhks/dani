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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->comment('Tên đơn vị');
            $table->string('contract_number')->unique()->comment('Số hợp đồng');
            $table->date('contract_date')->comment('Ngày hợp đồng');
            $table->date('effective_date')->comment('Hiệu lực hợp đồng');
            $table->date('expiration_date')->nullable()->comment('Ngày hết hiệu lực');
            $table->unsignedInteger('credit_days')->nullable()->comment('Số ngày được nợ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
