@props(['user'])

@php
    // Ukuran default (w-10 h-10) hanya dipakai kalau pemanggil tidak menentukan
    // ukuran sendiri lewat class="w-.. h-..", supaya tidak saling menimpa.
    $sizeClass = preg_match('/(^|\s)w-\S+/', (string) $attributes->get('class')) ? '' : 'w-10 h-10';
@endphp

@if ($user?->photo_url)
    <img src="{{ $user->photo_url }}" alt="{{ $user->name }}"
         {{ $attributes->merge(['class' => trim($sizeClass . ' rounded-full object-cover shrink-0')]) }}>
@else
    <div {{ $attributes->merge(['class' => trim($sizeClass . ' rounded-full bg-accent text-primary-dark font-extrabold flex items-center justify-center shrink-0')]) }}>
        {{ $user?->initial ?? '?' }}
    </div>
@endif
