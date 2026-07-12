<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_payments — ports entity.Payment */
    public function up(): void
    {
        Schema::create('glo_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('glo_orders')->cascadeOnDelete();
            $table->foreignUuid('payment_method_id')->nullable()->constrained('glo_payment_methods')->nullOnDelete();
            $table->foreignUuid('bank_account_id')->nullable()->constrained('glo_bank_accounts')->nullOnDelete();
            $table->string('snapshot_bank_name', 100)->nullable();
            $table->string('snapshot_account_number', 50)->nullable();
            $table->string('snapshot_account_holder', 100)->nullable();
            $table->string('provider', 255);
            $table->string('provider_reference', 255);
            $table->decimal('amount', 19, 4);
            $table->string('currency', 3);
            $table->string('status', 50)->default('PENDING');
            $table->dateTime('paid_at');
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_payments');
    }
};
