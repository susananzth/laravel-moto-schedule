# Guía de Arquitectura y Mejores Prácticas

## 🏗️ Estructura del Proyecto (DDD Ligero)

```
app/
├── Core/                          # Core compartido del proyecto
│   ├── Enums/                     # Enumeraciones (AppointmentStatus, etc.)
│   ├── Repositories/              # Repositorios genéricos (UserRepository, etc.)
│   └── DTOs/                      # Data Transfer Objects comunes
│
├── Modules/                       # Lógica de dominio organizada por módulos
│   ├── Appointments/
│   │   ├── Actions/               # Acciones = Casos de uso (1 responsabilidad)
│   │   ├── Repositories/          # Encapsulación de consultas DB
│   │   ├── Policies/              # Autorización (Policies de Laravel)
│   │   ├── DTOs/                  # Data Transfer Objects específicos del módulo
│   │   ├── Exceptions/            # Excepciones personalizadas del módulo
│   │   └── ValueObjects/          # Value Objects (AppointmentDateTime, etc.)
│   │
│   └── Services/                  # Similar a Appointments
│
├── Models/                        # Modelos Eloquent (SOLO representación de datos)
├── Livewire/                      # Componentes Livewire (UI delgada)
└── Http/
    └── Controllers/               # Controllers (casi vacíos en CQRS puro)
```

## 📋 Principios Clave

### 1. **SRP - Single Responsibility Principle**
- **Models**: SOLO almacenamiento y relaciones
- **Actions**: UNA responsabilidad = UN caso de uso
- **Policies**: SOLO autorización
- **Repositories**: SOLO acceso a datos

### 2. **Modelos Eloquent Limpios**
✅ **PERMITIDO**:
```php
// Relaciones
public function appointments(): HasMany { ... }

// Query Scopes
public function scopePending(Builder $query): Builder { ... }

// Helper methods (sin lógica de negocio)
public function isCompletedOrCancelled(): bool { ... }
```

❌ **PROHIBIDO**:
```php
// Lógica de negocio en modelos
public function confirmAppointment() { ... }
public function calculatePrice() { ... }
```

### 3. **Inyección de Dependencias Obligatoria**
```php
// ✅ CORRECTO - Componente Livewire con DI
final class AppointmentCalendar extends Component
{
    public function __construct(
        private readonly UpdateAppointmentAction $updateAction,
        private readonly AppointmentRepository $repository,
    ) {}
}

// ❌ INCORRECTO - Sin DI, usando Facades
class AppointmentCalendar extends Component
{
    public function update()
    {
        Auth::user();  // ❌ Facade directo
    }
}
```

### 4. **Typado Estricto Obligatorio**

```php
<?php
declare(strict_types=1);  // ✅ REQUERIDO en CADA archivo

namespace App\Modules\Appointments\Actions;

final class UpdateAppointmentAction
{
    public function __construct(
        private readonly AppointmentRepository $repository,  // ✅ Tipado
    ) {}

    // ✅ Tipos explícitos en TODO
    public function execute(
        Appointment $appointment,
        UpdateAppointmentDTO $dto
    ): Appointment
    {
        // ...
    }
}
```

### 5. **Enums para Estados**
```php
// ✅ CORRECTO - Enum type-safe
enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    
    public function label(): string { ... }
    public function color(): string { ... }
}

// Uso
$appointment->getStatusEnum() === AppointmentStatus::CONFIRMED;

// ❌ INCORRECTO - Strings mágicos
if ($appointment->status === 'pending') { ... }
```

## 🎬 Actions (Casos de Uso)

### Estructura Básica
```php
<?php
declare(strict_types=1);

namespace App\Modules\Appointments\Actions;

final class UpdateAppointmentAction
{
    public function __construct(
        private readonly AppointmentRepository $repository,
    ) {}

    /**
     * Actualiza una cita.
     *
     * @throws InvalidAppointmentStatusTransitionException
     */
    public function execute(
        Appointment $appointment,
        UpdateAppointmentDTO $dto
    ): Appointment {
        // 1. Validar (excepciones específicas)
        // 2. Actualizar
        // 3. Notificar (si es necesario)
        // 4. Retornar resultado
    }
}
```

## 📦 Repositories

### Propósito
Encapsular toda la lógica de acceso a datos para un modelo.

```php
final class AppointmentRepository
{
    public function findById(int $id): ?Appointment { ... }
    public function findByDateRange(Carbon $start, Carbon $end): Collection { ... }
    public function save(Appointment $app): Appointment { ... }
}
```

## 🔐 Policies

### Estructura
```php
final class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool { ... }
    public function update(User $user, Appointment $appointment): bool { ... }
    public function cancel(User $user, Appointment $appointment): bool { ... }
}
```

### Uso en Componentes Livewire
```php
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

final class AppointmentCalendar extends Component
{
    use AuthorizesRequests;

    public function editAppointment(int $id): void
    {
        $appointment = Appointment::findOrFail($id);
        
        // ✅ Usa authorize() trait
        try {
            $this->authorize('view', $appointment);
        } catch (AuthorizationException) {
            $this->dispatch('app-error', message: 'No tienes permiso.');
            return;
        }
    }
}
```

## 🧪 Testing (Pest/PHPUnit)

### Patrón AAA
```php
public function test_can_confirm_pending_appointment(): void
{
    // Arrange - Preparar datos necesarios
    $appointment = Appointment::factory()->create();
    $dto = new UpdateAppointmentDTO(/*...*/);

    // Act - Ejecutar la acción
    $result = $this->action->execute($appointment, $dto);

    // Assert - Verificar resultados
    $this->assertTrue($result->getStatusEnum() === AppointmentStatus::CONFIRMED);
}
```

### Checklist de Testing
- ✅ Casos exitosos
- ✅ Casos de error / excepciones
- ✅ Transiciones inválidas
- ✅ Autorización (Policies)
- ✅ Validaciones de negocio
- ✅ Notificaciones enviadas
- ✅ Límites (empty, null, overflow)

## 🎨 Livewire Components (Delgados)

### Responsabilidades
✅ Estado de UI
✅ Validación de formularios
✅ Dispatch de eventos
✅ Inyección y delegación a Actions

### Anti-patrones
❌ Lógica de negocio
❌ Consultas directas a BD
❌ Uso de Facades (Auth, Mail, etc.)

```php
final class AppointmentCalendar extends Component
{
    // ✅ Solo iny ecta acciones
    public function __construct(
        private readonly UpdateAppointmentAction $action,
    ) {}

    public function updateAppointment(): void
    {
        $this->validate();  // ✅ UI validation aquí
        
        try {
            // ✅ Delega a action
            $this->action->execute($this->appointment, $dto);
            $this->dispatch('notify');
        } catch (\Exception $e) {
            $this->dispatch('app-error', message: $e->getMessage());
        }
    }
}
```

## 📝 DTOs (Data Transfer Objects)

### Cuándo usar
- Entre capas (Controller → Action)
- Request → Service
- Service → Service

```php
final class UpdateAppointmentDTO
{
    public function __construct(
        public readonly AppointmentStatus $status,
        public readonly ?int $technicianId = null,
        public readonly ?string $notes = null,
    ) {}
}
```

## 🚨 Value Objects

### Cuándo usar
- Para valores complejos con validación
- Cuando hay reglas de negocio embebidas
- Inmutables y type-safe

```php
final class AppointmentDateTime
{
    public function __construct(public readonly Carbon $dateTime) {
        $this->validate();  // Valida en construcción
    }

    public static function createForReschedule(
        Carbon $newDate,
        Carbon $currentDate
    ): self { ... }
}
```

## 📚 Excepciones Personalizadas

Crea excepciones específicas del dominio:

```php
final class InvalidAppointmentStatusTransitionException extends Exception
{
    public static function make(string $from, string $to): self
    {
        return new self("Transición inválida de '{$from}' a '{$to}'.");
    }
}
```

## 🔄 Flujo Típico

```
         ┌─────────────────────┐
         │   Livre Component   │
         │  (AppointmentCal)   │
         └──────────┬──────────┘
                    │ Inyecta
                    ▼
         ┌─────────────────────┐
         │     Action          │ ← Lógica de negocio
         │  (UpdateAppt)       │   1. Validar
         └──────────┬──────────┘   2. Actualizar
                    │              3. Notificar
        ┌───────────┴───────────┐
        ▼                       ▼
    ┌──────────┐          ┌──────────────┐
    │Repository│          │   Policy     │
    │(Query DB)│          │(Autorizar)   │
    └──────────┘          └──────────────┘
```

## ✅ Checklist Pre-Commit

- [ ] `declare(strict_types=1)` en todo archivo
- [ ] Tipos explícitos (argumentos y retorno)
- [ ] Sin Facades en lógica (Actions, Services, Repositories)
- [ ] Models sin lógica de negocio
- [ ] Una Action = Una responsabilidad
- [ ] Tests con patrón AAA
- [ ] Tests para Policies y autorizaciones
- [ ] DTOs para transferencia entre capas
- [ ] Excepciones personalizadas para errores del dominio
