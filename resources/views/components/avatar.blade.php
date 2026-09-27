@props(['user'])

@if ($user?->photo_url)
    <img src="{{ $user->photo_url }}" alt="{{ $user->name }}"
         {{ $attributes->merge(['class' => 'w-10 h-10 rounded-full object-cover flex-shrink-0']) }}>
@else
    <div {{ $attributes->merge(['class' => 'w-10 h-10 rounded-full bg-accent text-primary-dark font-extrabold flex items-center justify-center flex-shrink-0']) }}>
        {{ $user?->initial ?? '?' }}
    </div>
@endif
