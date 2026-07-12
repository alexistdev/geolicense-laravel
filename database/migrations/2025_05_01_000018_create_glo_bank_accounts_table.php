<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_bank_accounts — ports entity.BankAccount */
    public function up(): void
    {
        Schema::create('glo_bank_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_method_id')->constrained('glo_payment_methods')->cascadeOnDelete();
            $table->string('bank_name', 100);
            $table->string('account_number', 50);
            $table->string('account_holder', 100);
            $table->boolean('is_main')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_bank_accounts');
    }
};
