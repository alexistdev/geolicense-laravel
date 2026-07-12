<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * glo_license_activations — ports entity.LicenseActivation.
 */
class LicenseActivation extends BaseModel
{
    protected $table = 'glo_license_activations';

    protected $fillable = [
        'license_id',
        'machine_id',
        'os_info',
        'activated_at',
        'last_verified_at',
        'is_activated',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'last_verified_at' => 'datetime',
            'is_activated' => 'boolean',
        ];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id');
    }
}
