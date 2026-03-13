@props([
    'title' => null,
    'description' => null,
])

<div class="text-center mb-8">
    <x-app-logo-icon />
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-4">{{ $title }}</h2>
    @if ($description)
        <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $description }}</p>
    @endif
</div>
