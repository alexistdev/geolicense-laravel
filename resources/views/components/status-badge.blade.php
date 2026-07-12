@props(['status'])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $classes = match ($value) {
        'ACTIVE', 'PAID', 'VERIFIED', 'COMPLETED' => 'bg-primary-container/20 text-primary border-primary/30',
        'PENDING', 'UNPAID', 'AWAITING_VERIFICATION' => 'bg-tertiary/10 text-tertiary border-tertiary/30',
        'REJECTED', 'CANCELLED', 'REVOKED', 'SUSPENDED' => 'bg-error-container/40 text-error border-error/30',
        default => 'bg-surface-container-highest text-on-surface-variant border-white/10',
    };
    $label = ucwords(strtolower(str_replace('_', ' ', $value)));
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[0.6875rem] font-bold uppercase tracking-wider $classes"]) }}>
    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $label }}
</span>
