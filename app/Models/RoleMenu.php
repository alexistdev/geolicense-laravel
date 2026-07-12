<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * glo_role_menus — ports entity.RoleMenu.
 */
class RoleMenu extends BaseModel
{
    protected $table = 'glo_role_menus';

    protected $fillable = [
        'role_id',
        'menu_uuid',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'role_id' => Role::class,
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_uuid');
    }
}
