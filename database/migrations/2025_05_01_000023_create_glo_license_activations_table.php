<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_license_activations — ports entity.LicenseActivation (unique license+machine) */
    public function up(): void
    {
        Schema::create('glo_license_activations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('license_id')->constrained('glo_licenses')->cascadeOnDelete();
            $table->string('machine_id');
            $table->string('os_info')->nullable();
            $table->dateTime('activated_at');
            $table->dateTime('last_verified_at')->nullable();
            $table->boolean('is_activated')->default(true);
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['license_id', 'machine_id'], 'uq_license_machine');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_license_activations');
    }
};
