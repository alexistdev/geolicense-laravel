<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_license_plans — ports entity.LicensePlan (note snake_case duration_days/max_seats) */
    public function up(): void
    {
        Schema::create('glo_license_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('glo_products')->cascadeOnDelete();
            $table->foreignUuid('license_type_id')->constrained('glo_license_types')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('billing_cycle', 255);
            $table->integer('duration_days')->default(1);
            $table->integer('max_seats')->default(5);
            $table->decimal('price', 19, 4);
            $table->string('currency', 3);
            $table->boolean('is_active')->default(true);
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_license_plans');
    }
};
