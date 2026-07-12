<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_audit_logs — ports entity.AuditLog (license activation audit trail) */
    public function up(): void
    {
        Schema::create('glo_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('license_id')->nullable()->constrained('glo_licenses')->nullOnDelete();
            $table->string('machine_id')->nullable();
            $table->string('action', 50);
            $table->text('detail')->nullable();
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_audit_logs');
    }
};
