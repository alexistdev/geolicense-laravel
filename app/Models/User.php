<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

/**
 * glo_users — ports entity.User (also the auth principal).
 */
class User extends Authenticatable
{
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'glo_users';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role',
        'is_suspended',
        'created_by',
        'modified_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => Role::class,
            'is_suspended' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $model) {
            $actor = Auth::user()?->email ?? 'System';
            $model->created_by ??= $actor;
            $model->modified_by ??= $actor;
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'user_id');
    }
}
