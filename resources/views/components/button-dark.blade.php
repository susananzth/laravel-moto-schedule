@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
    'icon' => null,
    'loading' => false,
])

@php
    // Base classes
    $baseClasses =
        'inline-flex items-center justify-center font-semibold rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed';

    // Size classes
    $sizeClasses = match ($size) {
        'sm' => 'px-3 py-2 text-sm',
        'lg' => 'px-6 py-3 text-lg',
        default => 'px-4 py-2.5 text-base',
    };

    // Variant classes
    $variantClasses = match ($variant) {
        'primary'
            => 'bg-moto-red text-white hover:bg-red-700 focus:ring-moto-red dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-500',
        'secondary'
            => 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600 dark:focus:ring-gray-500',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-800',
        'success'
            => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-800',
        'ghost'
            => 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-400 dark:text-gray-300 dark:hover:bg-gray-800 dark:focus:ring-gray-600',
        default => 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
    };

    $finalClasses = $baseClasses . ' ' . $sizeClasses . ' ' . $variantClasses;
@endphp

<button type="{{ $type }}" @if ($disabled) disabled @endif class="{{ $finalClasses }}"
    {{ $attributes }}>
    @if ($loading)
        <svg class="w-5 h-5 animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
    @elseif($icon)
        <span class="mr-2">{{ $icon }}</span>
    @endif
    {{ $slot }}
</button>
