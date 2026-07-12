<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_licenses — ports entity.License */
    public function up(): void
    {
        Schema::create('glo_licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('glo_users')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('glo_products')->cascadeOnDelete();
            $table->foreignUuid('license_plan_id')->constrained('glo_license_plans')->cascadeOnDelete();
            $table->foreignUuid('order_item_id')->constrained('glo_order_items')->cascadeOnDelete();
            $table->string('license_key')->unique();
            $table->integer('max_seats');
            $table->integer('used_seats');
            $table->dateTime('issued_at');
            $table->dateTime('expires_at');
            $table->string('status', 20)->default('ACTIVE');
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_licenses');
    }
};
