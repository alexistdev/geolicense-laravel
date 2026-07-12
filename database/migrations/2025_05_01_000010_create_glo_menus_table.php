<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** glo_menus — ports entity.Menu (DB-driven sidebar) */
    public function up(): void
    {
        Schema::create('glo_menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('urlink', 150);
            $table->string('classlink', 150);
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->uuid('parent_id')->nullable()->index();
            $table->integer('type_menu')->default(0);
            $table->string('code', 3);
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glo_menus');
    }
};
