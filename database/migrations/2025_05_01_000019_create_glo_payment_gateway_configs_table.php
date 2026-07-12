<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_payment_gateway_configs — ports entity.PaymentGatewayConfig */
    public function up(): void
    {
        Schema::create('glo_payment_gateway_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_method_id')->unique()->constrained('glo_payment_methods')->cascadeOnDelete();
            $table->string('api_key', 500);
            $table->string('webhook_token', 500);
            $table->text('extra_config')->nullable();
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_payment_gateway_configs');
    }
};
