<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div
        class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full mx-auto transition-colors duration-200">
        <x-auth-header-dark :title="__('Recuperar Contraseña')" :description="__('Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.')" />

        <x-auth-session-status-dark :status="session('status')" />

        <form wire:submit="sendPasswordResetLink" class="space-y-6 mt-8">
            <x-forms.input-dark name="email" wireModel="email" :label="__('Correo electrónico')" type="email" required autofocus
                placeholder="tu@email.com" />

            <x-button-dark variant="primary" type="submit" class="w-full">
                {{ __('Enviar enlace de recuperación') }}
            </x-button-dark>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('¿Lo recordaste?') }}
            </p>
            <a href="{{ route('login') }}"
                class="inline-block mt-2 text-moto-red dark:text-red-500 hover:text-red-700 dark:hover:text-red-400 font-medium transition">
                {{ __('Volver al inicio de sesión') }}
            </a>
        </div>
    </div>
</div>
