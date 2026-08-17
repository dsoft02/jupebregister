@props(['status' => 'pending'])

@php
    $styles = [
        'pending' => 'bg-secondary-100 text-secondary-800',
        'approved' => 'bg-primary-100 text-primary-800',
        'rejected' => 'bg-red-100 text-red-800',
        'published' => 'bg-primary-100 text-primary-800',
        'draft' => 'bg-slate-100 text-slate-700',
    ];
@endphp

<span class="badge {{ $styles[$status] ?? $styles['pending'] }} capitalize">
    {{ $slot }}
</span>
