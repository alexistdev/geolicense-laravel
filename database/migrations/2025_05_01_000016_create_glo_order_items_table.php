<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_order_items — ports entity.OrderItem */
    public function up(): void
    {
        Schema::create('glo_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('glo_orders')->cascadeOnDelete();
            $table->foreignUuid('license_plan_id')->constrained('glo_license_plans')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->decimal('unit_price', 19, 4);
            $table->decimal('total_price', 19, 4);
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_order_items');
    }
};
