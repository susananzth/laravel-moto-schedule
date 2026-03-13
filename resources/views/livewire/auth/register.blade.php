<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div
        class="w-full max-w-2xl bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 transition-colors duration-200">
        <x-auth-header-dark :title="__('Crear una cuenta')" :description="__('Ingrese sus datos a continuación para crear su cuenta.')" />

        <x-auth-session-status-dark :status="session('status')" />

        <form wire:submit="register" class="space-y-6 mt-8">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-forms.input-dark name="firstname" wireModel="firstname" :label="__('Nombres')" required autofocus
                    maxlength="150" placeholder="Ej: Juan" />

                <x-forms.input-dark name="lastname" wireModel="lastname" :label="__('Apellidos')" required maxlength="150"
                    placeholder="Ej: Pérez" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-forms.input-dark name="username" wireModel="username" maxlength="50" :label="__('Usuario')" required
                    placeholder="juanperez" />

                <x-forms.input-dark name="phone" wireModel="phone" :label="__('Teléfono')" required type="tel"
                    placeholder="999111555" maxlength="10" />
            </div>

            <x-forms.input-dark name="email" wireModel="email" :label="__('Correo electrónico')" type="email" required
                maxlength="150" placeholder="tu@email.com" />

            <x-forms.input-dark name="password" wireModel="password" :label="__('Contraseña')" type="password" required
                autocomplete="new-password" placeholder="Ingresa tu contraseña" viewable />

            <x-forms.input-dark name="password_confirmation" wireModel="password_confirmation" :label="__('Confirmar contraseña')"
                type="password" required placeholder="Confirme contraseña" viewable />

            <x-button-dark variant="primary" type="submit" class="w-full">
                {{ __('Crear cuenta') }}
            </x-button-dark>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('¿Ya tiene una cuenta?') }}
            </p>
            <a href="{{ route('login') }}"
                class="inline-block mt-2 text-moto-red dark:text-red-500 hover:text-red-700 dark:hover:text-red-400 font-semibold transition">
                {{ __('Inicie sesión') }}
            </a>
        </div>
    </div>
</div>
