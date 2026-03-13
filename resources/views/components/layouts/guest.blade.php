<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">

<head>
    @include('partials.head')
    <script>
        // Detectar preferencia de dark mode del sistema
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body
    class="bg-white dark:bg-gray-900 font-sans flex flex-col min-h-screen antialiased text-gray-900 dark:text-gray-100 transition-colors duration-200">

    @include('partials.nav-guest')

    <main class="flex-grow">
        {{ $slot }}
    </main>

    @include('partials.footer-guest')

    @fluxScripts

    <x-toast-notification />
    <x-dev-disclaimer />
    @livewireScripts

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</body>

</html>
