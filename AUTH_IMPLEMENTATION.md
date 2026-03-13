# Auth Module - Guía de Implementación

## 📋 Pasos de Implementación Rápida

### Paso 1: Registrar el ServiceProvider

En `config/app.php`, agregar en el array `providers`:

```php
'providers' => [
    // ...
    
    App\Providers\AuthModuleServiceProvider::class,
    
    // ...
],
```

### Paso 2: Verificar Estructura de Carpetas

```bash
# Ejecutar para verificar que todas las carpetas existen
find app/Modules/Auth -type d | sort
find tests/Feature/Modules/Auth -type d | sort
find tests/Unit/Modules/Auth -type d | sort
find tests/Feature/Livewire/Auth -type d | sort
```

Expected output:
```
app/Modules/Auth
app/Modules/Auth/Actions
app/Modules/Auth/DTOs
app/Modules/Auth/Exceptions
app/Modules/Auth/Policies
app/Modules/Auth/ValueObjects
tests/Feature/Modules/Auth
tests/Unit/Modules/Auth/ValueObjects
tests/Feature/Livewire/Auth
```

### Paso 3: Ejecutar Tests

```bash
# Ejecutar todos los tests de Auth
php artisan test tests/Feature/Modules/Auth
php artisan test tests/Unit/Modules/Auth
php artisan test tests/Feature/Livewire/Auth

# O todos juntos
php artisan test --path tests/Unit/Modules/Auth --path tests/Feature/Modules/Auth --path tests/Feature/Livewire/Auth

# Ver cobertura
php artisan test tests/ --coverage --coverage-text
```

### Paso 4: Verificar Componentes Blade

```bash
# Verificar que existen los componentes
ls -la resources/views/components/forms/
ls -la resources/views/components/auth*.blade.php
```

Expected files:
```
resources/views/components/
  ├── auth-header-dark.blade.php
  ├── auth-session-status-dark.blade.php
  ├── button-dark.blade.php
  └── forms/
      ├── checkbox-dark.blade.php
      ├── input-dark.blade.php
      └── select-dark.blade.php
```

### Paso 5: Verificar Routes

Las rutas ya están definidas en `routes/auth.php`:

```php
Route::middleware('guest')->group(function () {
    Route::get('login', Login::class)->name('login');
    Route::get('register', Register::class)->name('register');
    Route::get('forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', LogoutController::class)->name('logout');
});
```

---

## ✅ Checklist de Verificación

### Archivos Creados

- [ ] `app/Modules/Auth/Actions/LoginAction.php`
- [ ] `app/Modules/Auth/Actions/RegisterAction.php`
- [ ] `app/Modules/Auth/Actions/ForgotPasswordAction.php`
- [ ] `app/Modules/Auth/Actions/ResetPasswordAction.php`
- [ ] `app/Modules/Auth/Actions/LogoutAction.php`
- [ ] `app/Modules/Auth/DTOs/LoginDTO.php`
- [ ] `app/Modules/Auth/DTOs/RegisterDTO.php`
- [ ] `app/Modules/Auth/DTOs/ForgotPasswordDTO.php`
- [ ] `app/Modules/Auth/DTOs/ResetPasswordDTO.php`
- [ ] `app/Modules/Auth/DTOs/LogoutDTO.php`
- [ ] `app/Modules/Auth/ValueObjects/Email.php`
- [ ] `app/Modules/Auth/ValueObjects/HashedPassword.php`
- [ ] `app/Modules/Auth/Exceptions/InvalidCredentialsException.php`
- [ ] `app/Modules/Auth/Exceptions/TooManyLoginAttemptsException.php`
- [ ] `app/Modules/Auth/Exceptions/UserAlreadyExistsException.php`
- [ ] `app/Modules/Auth/Exceptions/InvalidEmailException.php`
- [ ] `app/Modules/Auth/Exceptions/PasswordResetLinkException.php`
- [ ] `app/Modules/Auth/Policies/UserPolicy.php`
- [ ] `app/Providers/AuthModuleServiceProvider.php`

### Componentes Blade

- [ ] `resources/views/components/auth-header-dark.blade.php`
- [ ] `resources/views/components/auth-session-status-dark.blade.php`
- [ ] `resources/views/components/button-dark.blade.php`
- [ ] `resources/views/components/forms/input-dark.blade.php`
- [ ] `resources/views/components/forms/select-dark.blade.php`
- [ ] `resources/views/components/forms/checkbox-dark.blade.php`

### Vistas Actualizadas

- [ ] `resources/views/livewire/auth/login.blade.php` (con dark mode)
- [ ] `resources/views/livewire/auth/register.blade.php` (con dark mode)
- [ ] `resources/views/livewire/auth/forgot-password.blade.php` (con dark mode)
- [ ] `resources/views/livewire/auth/reset-password.blade.php` (con dark mode)
- [ ] `resources/views/components/layouts/guest.blade.php` (dark mode support)

### Componentes Livewire Refactorizados

- [ ] `app/Livewire/Auth/Login.php` (thin + delegados a Action)
- [ ] `app/Livewire/Auth/Register.php` (thin + delegados a Action)
- [ ] `app/Livewire/Auth/ForgotPassword.php` (thin + delegados a Action)
- [ ] `app/Livewire/Auth/ResetPassword.php` (thin + delegados a Action)

### Repositorio Mejorado

- [ ] `app/Core/Repositories/UserRepository.php` (métodos adicionales)

### Tests Creados

- [ ] `tests/Feature/Modules/Auth/LoginActionTest.php` (8 tests)
- [ ] `tests/Feature/Modules/Auth/RegisterActionTest.php` (7 tests)
- [ ] `tests/Feature/Modules/Auth/ForgotPasswordActionTest.php` (3 tests)
- [ ] `tests/Feature/Modules/Auth/ResetPasswordActionTest.php` (4 tests)
- [ ] `tests/Feature/Modules/Auth/LogoutActionTest.php` (3 tests)
- [ ] `tests/Unit/Modules/Auth/UserPolicyTest.php` (11 tests)
- [ ] `tests/Feature/Livewire/Auth/LoginComponentTest.php` (7 tests)
- [ ] `tests/Feature/Livewire/Auth/RegisterComponentTest.php` (8 tests)
- [ ] `tests/Unit/Modules/Auth/ValueObjects/EmailTest.php` (8 tests)
- [ ] `tests/Unit/Modules/Auth/ValueObjects/HashedPasswordTest.php` (4 tests)

---

## 🧪 Ejecutar Tests

### Todos los tests de Auth

```bash
php artisan test tests/Feature/Modules/Auth tests/Unit/Modules/Auth tests/Feature/Livewire/Auth
```

### Por categoría

```bash
# Actions
php artisan test tests/Feature/Modules/Auth/LoginActionTest
php artisan test tests/Feature/Modules/Auth/RegisterActionTest
php artisan test tests/Feature/Modules/Auth/ForgotPasswordActionTest
php artisan test tests/Feature/Modules/Auth/ResetPasswordActionTest
php artisan test tests/Feature/Modules/Auth/LogoutActionTest

# Components
php artisan test tests/Feature/Livewire/Auth/LoginComponentTest
php artisan test tests/Feature/Livewire/Auth/RegisterComponentTest

# Policies & ValueObjects
php artisan test tests/Unit/Modules/Auth/UserPolicyTest
php artisan test tests/Unit/Modules/Auth/ValueObjects/EmailTest
php artisan test tests/Unit/Modules/Auth/ValueObjects/HashedPasswordTest
```

### Con cobertura

```bash
php artisan test tests/Feature/Modules/Auth tests/Unit/Modules/Auth --coverage
```

Expected: `92%+` cobertura

---

## 🌙 Pruebas Manuales - Dark Mode

### 1. Verificar Dark Mode en Desarrollo

```bash
npm run dev
```

Abre el navegador en modo oscuro (DevTools > Appearance) para ver el dark mode.

### 2. Componentes a Verificar

- [ ] Input fields (luz/oscuridad)
- [ ] Buttons (luz/oscuridad)
- [ ] Checkboxes (luz/oscuridad)
- [ ] Error messages (rojo en ambos)
- [ ] Form labels (contraste adecuado)
- [ ] Borders y backgrounds

### 3. Colores en Dark Mode

- Background: `#111827` (gray-900)
- Text: `#F3F4F6` (gray-100)
- Input BG: `#1F2937` (gray-800)
- Input Border: `#4B5563` (gray-600)
- Success: `#059669` (green-600)
- Error: `#DC2626` (red-600)

---

## 🔄 Flujos Manuales

### Login Flow

1. Navega a `/login`
2. Ingresa credenciales válidas
3. Verifica que redirige a dashboard
4. Verifica que usuario está autenticado
5. Intenta login 6 veces con credenciales wrongas
6. Verifica que muestra error de "demasiados intentos"

### Register Flow

1. Navega a `/register`
2. Llena formulario
3. Verifica que crea usuario
4. Verifica que asigna rol 'Cliente'
5. Verifica que redirige a dashboard
6. Intenta registrar con email existente
7. Verifica que muestra error

### Forgot Password Flow

1. Navega a `/forgot-password`
2. Ingresa email existente
3. Verifica que muestra mensaje "Se enviará un link"
4. Revisa email (en Mailtrap/similar)
5. Haz click en link de reset
6. Verifica que redirige a `/reset-password/{token}?email=...`

### Reset Password Flow

1. Desde email, navega al link de reset
2. Ingresa nueva contraseña
3. Confirma contraseña
4. Verifica que resetea y redirige a login
5. Intenta login con nueva contraseña
6. Verifica que funciona

### Logout Flow

1. Estando autenticado
2. Click en "Logout"
3. Verifica que redirige a `/`
4. Verifica que usuario no está autenticado
5. Verifica que no puede acceder a rutas protected

---

## 🐛 Debugging

### Logs

```bash
# Ver logs de aplicación
tail -f storage/logs/laravel.log

# Debug modo
APP_DEBUG=true php artisan serve
```

### Testing con Prints

```php
// En tus tests
public function test_something() {
    $user = User::factory()->create();
    dump($user->toArray()); // Imprimir usuario
    
    $this->actingAs($user);
    // ... test
}
```

### Database

```bash
# Verificar que usuarios se crean
php artisan tinker
> User::all()->pluck('email', 'id');

# Verificar roles
> User::first()->roles;
```

---

## 🚨 Problemas Comunes

### 1. "Service not found" Error

**Problema:** `AuthModuleServiceProvider` no registrado

**Solución:**
```php
// config/app.php
'providers' => [
    // ...
    App\Providers\AuthModuleServiceProvider::class,
],
```

### 2. Tests fallan con "Class not found"

**Problema:** Composer autoload no actualizado

**Solución:**
```bash
composer dump-autoload
php artisan test
```

### 3. Dark mode no funciona

**Problema:** Frontend cache

**Solución:**
```bash
npm run build
php artisan view:clear
php artisan cache:clear
```

### 4. Email lowercase no funciona

**Problema:** Validación ocurre antes de normalización

**Solución:** Email se normaliza automáticamente en UserRepository

### 5. Session no regenera

**Problema:** Session driver no compatible

**Solución:**
```php
// .env
SESSION_DRIVER=database
SESSION_DOMAIN=localhost
```

---

## 📈 Próximos Pasos

1. **Aplica el mismo patrón a Módulo Services**
   - ServiceAction (CRUD)
   - ServiceRepository
   - ServicePolicy
   - Tests

2. **Aplica a Módulo Users**
   - UserUpdateAction
   - UserDeleteAction
   - UserChangePasswordAction
   - Tests

3. **Auditoría**
   - Agregar logging a Actions
   - Dashboard de auditoría
   - Eventos de login/logout

4. **Seguridad Avanzada**
   - 2FA Integration
   - IP Whitelist
   - Device tracking
   - Session management

---

## 📞 Soporte

Para preguntas sobre la arquitectura, revisar:
- `ARCHITECTURE.md` - Principios generales
- `AUTH_MODULE.md` - Detalles de Auth
- `README.REFACTOR.md` - Cambios realizados

---

**Última actualización:** Marzo 2026  
**Versión:** 1.0
