@props(['link', 'label', 'type' => 'primary'])

@php
$colors = [
    'primary' => 'bg-indigo-600 hover:bg-indigo-500 border-indigo-500/50 text-white',
    'success' => 'bg-emerald-600 hover:bg-emerald-500 border-emerald-500/50 text-white',
    'danger'  => 'bg-rose-600 hover:bg-rose-500 border-rose-500/50 text-white',
    'warning' => 'bg-amber-500 hover:bg-amber-400 border-amber-400/50 text-white',
];
$style = $colors[$type] ?? $colors['primary'];
@endphp

<a href="{{ $link }}" target="_blank"
   class="inline-flex items-center justify-center px-4 py-2 rounded-lg border text-sm font-semibold transition-colors {{ $style }}">
    {{ $label }}
</a>
