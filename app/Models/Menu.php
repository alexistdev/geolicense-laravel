<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * glo_menus — ports entity.Menu.
 */
class Menu extends BaseModel
{
    protected $table = 'glo_menus';

    protected $fillable = [
        'name',
        'urlink',
        'classlink',
        'icon',
        'sort_order',
        'parent_id',
        'type_menu',
        'code',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'type_menu' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order');
    }
}
