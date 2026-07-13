<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * glo_settings — key/value store for runtime-editable application settings.
 */
class Setting extends Model
{
    protected $table = 'glo_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Read a setting value, falling back to $default when it is not stored.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::query()->find($key)?->value ?? $default;
    }

    /**
     * Create or update a setting value.
     */
    public static function put(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
