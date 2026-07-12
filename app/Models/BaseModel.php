<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Shared base for every glo_* entity — reproduces the Spring BaseEntity:
 * UUID primary keys, soft deletes, and created_by/modified_by auditing.
 */
abstract class BaseModel extends Model
{
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(function (BaseModel $model) {
            $actor = Auth::user()?->email ?? 'System';
            if (empty($model->created_by)) {
                $model->created_by = $actor;
            }
            if (empty($model->modified_by)) {
                $model->modified_by = $actor;
            }
        });

        static::updating(function (BaseModel $model) {
            $model->modified_by = Auth::user()?->email ?? $model->modified_by ?? 'System';
        });
    }
}
