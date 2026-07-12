<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_invoices — ports entity.Invoice */
    public function up(): void
    {
        Schema::create('glo_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('glo_orders')->cascadeOnDelete();
            $table->string('invoice_number', 255);
            $table->decimal('amount', 19, 4);
            $table->string('currency', 3);
            $table->string('status', 50)->default('UNPAID');
            $table->dateTime('issued_at');
            $table->integer('unique_code');
            $table->decimal('total_amount', 19, 4);
            $table->decimal('discount', 19, 4)->default(0);
            $table->decimal('tax', 19, 4)->default(0);
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_invoices');
    }
};
