<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * glo_logs — append-only system activity log surfaced under
     * System > Log System in the admin console. Unlike the other glo_*
     * tables this one has no soft-deletes: log entries are permanent and
     * cannot be removed from the UI.
     */
    public function up(): void
    {
        Schema::create('glo_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('level', 20)->default('INFO')->index();   // INFO / WARNING / ERROR / CRITICAL / DEBUG
            $table->string('action', 100)->nullable();               // short event label, e.g. "User Suspended"
            $table->text('description');                             // human-readable message
            $table->json('context')->nullable();                    // optional structured payload
            $table->foreignUuid('user_id')->nullable()->constrained('glo_users')->nullOnDelete();
            $table->string('causer')->nullable();                   // email/name snapshot of the actor
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('method', 10)->nullable();               // HTTP method
            $table->text('url')->nullable();                        // request URL
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_logs');
    }
};
