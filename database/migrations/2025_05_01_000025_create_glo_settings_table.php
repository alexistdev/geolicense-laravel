<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * glo_settings — simple key/value store for runtime-editable application
     * settings (e.g. the Google reCAPTCHA toggle and keys managed from the
     * admin Settings screen).
     */
    public function up(): void
    {
        Schema::create('glo_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_settings');
    }
};
