@props(['name', 'label' => null, 'options' => [], 'required' => false, 'disabled' => false, 'wireModel' => null])

@php
    $selectId = uniqid('select_');
    $errorKey = $name;
    $hasError = $errors->has($errorKey);

    $baseClasses =
        'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent transition duration-200 outline-none disabled:opacity-50 disabled:cursor-not-allowed font-medium';

    $lightClasses =
        'bg-white text-gray-900 border-gray-300 focus:ring-moto-red disabled:bg-gray-100 disabled:text-gray-500';
    $darkClasses = 'dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:ring-red-500';

    $errorClasses = 'border-red-500 focus:ring-red-500 dark:border-red-500 dark:focus:ring-red-500';
    $normalClasses = 'border-gray-300 focus:ring-moto-red';

    $selectClasses =
        $baseClasses . ' ' . $lightClasses . ' ' . $darkClasses . ' ' . ($hasError ? $errorClasses : $normalClasses);
@endphp

<div class="space-y-2">
    @if ($label)
        <label for="{{ $selectId }}" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <select id="{{ $selectId }}" name="{{ $name }}"
        @if ($wireModel) wire:model="{{ $wireModel }}" @endif class="{{ $selectClasses }}"
        @if ($required) required @endif @if ($disabled) disabled @endif
        {{ $attributes }}>
        @foreach ($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>

    @if ($hasError)
        @foreach ($errors->get($errorKey) as $error)
            <p class="text-sm text-red-500 dark:text-red-400 font-medium">{{ $error }}</p>
        @endforeach
    @endif
</div>
