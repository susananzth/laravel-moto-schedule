<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div
        class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full mx-auto transition-colors duration-200">
        <x-auth-header-dark :title="__('Nueva Contraseña')" :description="__('Ingresa y confirma tu nueva contraseña.')" />

        <x-auth-session-status-dark :status="session('status')" />

        <form wire:submit="resetPassword" class="space-y-6 mt-8">
            <x-forms.input-dark name="email" wireModel="email" :label="__('Correo electrónico')" type="email" required readonly
                disabled maxlength="150" />

            <x-forms.input-dark name="password" wireModel="password" :label="__('Nueva Contraseña')" type="password" required
                autocomplete="new-password" placeholder="Mínimo 8 caracteres" viewable />

            <x-forms.input-dark name="password_confirmation" wireModel="password_confirmation" :label="__('Confirmar Contraseña')"
                type="password" required autocomplete="new-password" placeholder="Repite la contraseña" viewable />

            <x-button-dark variant="primary" type="submit" class="w-full">
                {{ __('Restablecer Contraseña') }}
            </x-button-dark>
        </form>
    </div>
</div>
