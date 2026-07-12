<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_orders — ports entity.Orders */
    public function up(): void
    {
        Schema::create('glo_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('glo_users')->cascadeOnDelete();
            $table->string('order_number', 255);
            $table->string('currency', 3);
            $table->string('status', 50)->default('PENDING');
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_orders');
    }
};
