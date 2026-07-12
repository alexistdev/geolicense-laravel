<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_role_menus — ports entity.RoleMenu */
    public function up(): void
    {
        Schema::create('glo_role_menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('role_id', 50);
            $table->foreignUuid('menu_uuid')->constrained('glo_menus')->cascadeOnDelete();
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['role_id', 'menu_uuid'], 'uk_role_menu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_role_menus');
    }
};
