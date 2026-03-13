@props([
    'status' => null,
])

@if ($status)
    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
        <p class="text-sm font-medium text-green-800 dark:text-green-200">
            {{ $status }}
        </p>
    </div>
@endif
