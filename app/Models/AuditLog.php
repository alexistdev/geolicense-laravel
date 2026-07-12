<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * glo_audit_logs — ports entity.AuditLog.
 */
class AuditLog extends BaseModel
{
    protected $table = 'glo_audit_logs';

    protected $fillable = [
        'license_id',
        'machine_id',
        'action',
        'detail',
        'created_by',
        'modified_by',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id');
    }
}
