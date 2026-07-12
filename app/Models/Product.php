<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * glo_products — ports entity.Product.
 */
class Product extends BaseModel
{
    protected $table = 'glo_products';

    protected $fillable = [
        'name',
        'version',
        'description',
        'sku',
        'is_active',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function licensePlans(): HasMany
    {
        return $this->hasMany(LicensePlan::class, 'product_id');
    }
}
