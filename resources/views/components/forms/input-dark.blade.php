@props([
    'name' => null,
    'label' => null,
    'type' => 'text',
    'required' => false,
    'placeholder' => null,
    'wireModel' => null,
    'icon' => null,
    'viewable' => false,
    'disabled' => false,
])

@php
    // make sure showPassword variable is always defined for blade logic
    $showPassword = $showPassword ?? false;

    $inputId = $name ?: uniqid('input_');
    $errorKey = $name ?: $wireModel;
    $hasError = $errorKey && $errors->has($errorKey);

    // Dark mode compatible classes
    $baseClasses =
        'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent transition duration-200 outline-none disabled:opacity-50 disabled:cursor-not-allowed font-medium';

    $lightClasses =
        'bg-white text-gray-900 placeholder-gray-400 border-gray-300 focus:ring-moto-red focus:ring-offset-0 disabled:bg-gray-100 disabled:text-gray-500';
    $darkClasses =
        'dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:border-gray-600 dark:focus:ring-red-500';

    $errorClasses = 'border-red-500 focus:ring-red-500 dark:border-red-500 dark:focus:ring-red-500';
    $normalClasses = 'border-gray-300 focus:ring-moto-red dark:border-gray-600 dark:focus:ring-red-500';

    $inputClasses =
        $baseClasses . ' ' . $lightClasses . ' ' . $darkClasses . ' ' . ($hasError ? $errorClasses : $normalClasses);

    if ($icon) {
        $inputClasses .= ' pl-11';
    }
    if ($viewable) {
        $inputClasses .= ' pr-11';
    }
@endphp

<div class="space-y-2" x-data="{ showPassword: false }">
    @if ($label)
        <label for="{{ $inputId }}" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <div class="absolute left-3 top-3 text-gray-400 dark:text-gray-500">
                {{ $icon }}
            </div>
        @endif

        <input type="{{ $viewable && $showPassword ? 'text' : $type }}" id="{{ $inputId }}"
            @if ($name) name="{{ $name }}" @endif
            @if ($wireModel) wire:model.blur="{{ $wireModel }}" @endif class="{{ $inputClasses }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($required) required @endif @if ($disabled) disabled @endif
            {{ $attributes }} />

        @if ($viewable && $type === 'password')
            <button type="button" @click="showPassword = !showPassword"
                class="absolute right-3 top-3 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 transition"
                tabindex="-1">
                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                    </path>
                </svg>
                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M19.892 16.391a18.384 18.384 0 00-7.437-7.437m12.868 12.868l-4.243-4.243m4.243 4.243L9.878 9.878">
                    </path>
                </svg>
            </button>
        @endif
    </div>

    @if ($hasError)
        @foreach ($errors->get($errorKey) as $error)
            <p class="text-sm text-red-500 dark:text-red-400 font-medium">{{ $error }}</p>
        @endforeach
    @endif
</div>
