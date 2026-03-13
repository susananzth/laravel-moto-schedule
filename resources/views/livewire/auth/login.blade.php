<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div
        class="w-full max-w-md bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 transition-colors duration-200">

        <x-auth-header-dark :title="__('Iniciar Sesión')" :description="__('Ingresa a tu cuenta para gestionar tus citas')" />

        <x-auth-session-status-dark :status="session('status')" />

        <form method="POST" wire:submit="login" class="space-y-6 mt-8">
            <x-forms.input-dark name="email" wireModel="email" :label="__('Correo electrónico')" type="email" required autofocus
                maxlength="150" autocomplete="email" placeholder="tu@email.com" />

            <x-forms.input-dark name="password" wireModel="password" :label="__('Contraseña')" type="password" required
                autocomplete="current-password" :placeholder="__('Ingresa tu contraseña')" viewable />

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <x-forms.checkbox-dark name="remember" wireModel="remember" :label="__('Recordar sesión')" />

                @if (Route::has('password.request'))
                    <a class="text-sm text-moto-red dark:text-red-500 hover:text-red-700 dark:hover:text-red-400 font-medium transition"
                        href="{{ route('password.request') }}">
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </a>
                @endif
            </div>

            <x-button-dark variant="primary" type="submit" class="w-full">
                {{ __('Iniciar Sesión') }}
            </x-button-dark>
        </form>

        @if (Route::has('register'))
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
                <p class="text-gray-600 dark:text-gray-400">
                    {{ __('¿No tienes una cuenta?') }}
                </p>
                <a href="{{ route('register') }}"
                    class="inline-block mt-2 text-moto-red dark:text-red-500 hover:text-red-700 dark:hover:text-red-400 font-semibold transition">
                    {{ __('Regístrate aquí') }}
                </a>
            </div>
        @endif
    </div>
</div>
