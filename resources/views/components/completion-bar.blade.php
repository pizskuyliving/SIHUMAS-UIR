@props(['percent', 'label' => 'Kelengkapan Data', 'sublabel' => null])

@php
    $color = $percent >= 80 ? 'bg-emerald-600' : ($percent >= 50 ? 'bg-accent' : 'bg-coral');
@endphp

<div class="neu-card p-4 sm:p-6 mb-6">
    <div class="flex items-center justify-between mb-2">
        <h2 class="font-semibold">{{ $label }}</h2>
        <span class="text-lg font-bold text-primary-dark">{{ $percent }}%</span>
    </div>
    <div class="w-full h-3 rounded-full overflow-hidden neu-input">
        <div class="h-full {{ $color }} rounded-full transition-all duration-500" style="width: {{ min($percent, 100) }}%"></div>
    </div>
    @if ($sublabel)
        <p class="text-xs text-gray-400 mt-2">{{ $sublabel }}</p>
    @endif
</div>
