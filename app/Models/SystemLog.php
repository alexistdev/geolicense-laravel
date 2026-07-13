<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * glo_logs — the system activity log shown under System > Log System.
 *
 * Deliberately does NOT extend BaseModel: log rows are append-only and are
 * never soft-deleted, so this model has no SoftDeletes trait and no
 * created_by/modified_by auditing (the actor is captured in user_id / causer).
 */
class SystemLog extends Model
{
    use HasUuids;

    protected $table = 'glo_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'level',
        'action',
        'description',
        'context',
        'user_id',
        'causer',
        'ip_address',
        'user_agent',
        'method',
        'url',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Append a log entry, auto-filling the current actor and request metadata.
     * Call it from anywhere, e.g. SystemLog::record('User suspended', 'WARNING', ['action' => 'User Suspended']).
     *
     * @param  array<string, mixed>  $attributes  Overrides/extra columns (level, action, context, …).
     */
    public static function record(string $description, string $level = 'INFO', array $attributes = []): self
    {
        $request = request();

        return static::create(array_merge([
            'level' => $level,
            'description' => $description,
            'user_id' => Auth::id(),
            'causer' => Auth::user()?->email,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'method' => $request?->method(),
            'url' => $request?->fullUrl(),
        ], $attributes));
    }
}
