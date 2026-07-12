<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_payment_methods — ports entity.PaymentMethod */
    public function up(): void
    {
        Schema::create('glo_payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 50);
            $table->string('display_name', 100);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_payment_methods');
    }
};
