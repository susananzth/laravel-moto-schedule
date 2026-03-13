@props(['name', 'label' => null, 'required' => false, 'disabled' => false, 'wireModel' => null])

@php
    $checkboxId = uniqid('checkbox_');
    $errorKey = $name;
    $hasError = $errors->has($errorKey);
@endphp

<div class="flex items-center space-x-3">
    <input type="checkbox" id="{{ $checkboxId }}" name="{{ $name }}" value="1"
        @if ($wireModel) wire:model="{{ $wireModel }}" @endif
        class="w-5 h-5 text-moto-red bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-moto-red cursor-pointer transition disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-red-500"
        @if ($required) required @endif @if ($disabled) disabled @endif
        {{ $attributes }} />

    @if ($label)
        <label for="{{ $checkboxId }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
</div>

@if ($hasError)
    @foreach ($errors->get($errorKey) as $error)
        <p class="text-sm text-red-500 dark:text-red-400 font-medium mt-1">{{ $error }}</p>
    @endforeach
@endif
