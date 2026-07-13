<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SKU is the stable product identity that client apps (GeoCAT, GeoBill) send on
 * activation, and that license product-scoping is checked against. It must be
 * unique so `license->product->sku === productSku` is never ambiguous.
 *
 * NOTE: a plain unique index (not composite with deleted_at) is intentional —
 * in MySQL, NULL deleted_at values are treated as distinct, so a composite index
 * would fail to enforce uniqueness among active rows. A SKU should never be
 * reused for a different product anyway, since clients are built against it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('glo_products', function (Blueprint $table) {
            $table->unique('sku');
        });
    }

    public function down(): void
    {
        Schema::table('glo_products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
        });
    }
};
