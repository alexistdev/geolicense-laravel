<?php

namespace App\Models;

/**
 * glo_license_types — ports entity.LicenseType.
 */
class LicenseType extends BaseModel
{
    protected $table = 'glo_license_types';

    protected $fillable = [
        'name',
        'description',
        'is_trial',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'is_trial' => 'boolean',
        ];
    }
}
