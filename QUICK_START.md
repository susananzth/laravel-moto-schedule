# 🚀 GUÍA RÁPIDA: CÓMO SEGUIR ESTE PATRÓN

## 📝 Cuando Necesites Agregar Funcionalidad

### Paso 1: Identificar Responsabilidades

Pregúntate:
- ¿Es una acción/caso de uso? → Crear **Action**
- ¿Es validación/negocio? → Refactorizar a **Value Object**
- ¿Es consulta DB? → Agregar a **Repository**
- ¿Es autorización? → Agregar a **Policy**
- ¿Es transferencia de datos? → Crear **DTO**
- ¿Es estado de entidad? → Usar **Enum**

### Paso 2: Template de Una Action Nueva

```php
<?php
declare(strict_types=1);

namespace App\Modules\[Module]\Actions;

use App\Modules\[Module]\DTOs\[ActionName]DTO;
use App\Modules\[Module]\Repositories\[Model]Repository;
use App\Models\[Model];

final class [ActionName]Action
{
    public function __construct(
        private readonly [Model]Repository $repository,
    ) {}

    /**
     * [Descripción breve de qué hace].
     *
     * @throws [TuExcepcióPersonalizada]
     */
    public function execute([Model] $model, [ActionName]DTO $dto): [Model]
    {
        // 1. Validar (lanzar excepciones específicas)
        if (!$this->validate($model, $dto)) {
            throw new \DomainException('Validación fallida');
        }

        // 2. Ejecutar lógica de negocio
        $model->update([
            'campo' => $dto->campo,
        ]);

        // 3. Persistir
        $model = $this->repository->save($model);

        // 4. Notificar si es necesario
        // $this->notify($model);

        // 5. Retornar resultado
        return $model;
    }

    private function validate([Model] $model, [ActionName]DTO $dto): bool
    {
        // Validaciones específicas
        return true;
    }
}
```

### Paso 3: Template de DTO

```php
<?php
declare(strict_types=1);

namespace App\Modules\[Module]\DTOs;

final class [ActionName]DTO
{
    public function __construct(
        public readonly string $campo1,
        public readonly ?int $campo2 = null,
        public readonly string $campo3 = '',
    ) {}
}
```

### Paso 4: Template de Test

```php
<?php
declare(strict_types=1);

namespace Tests\Feature\Modules\[Module];

use App\Models\[Model];
use App\Modules\[Module]\Actions\[ActionName]Action;
use App\Modules\[Module]\DTOs\[ActionName]DTO;
use Tests\TestCase;

class [ActionName]ActionTest extends TestCase
{
    private [ActionName]Action $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = $this->app->make([ActionName]Action::class);
    }

    // ==================== TEST: Caso Exitoso ====================

    public function test_executes_successfully_with_valid_data(): void
    {
        // Arrange
        $model = [Model]::factory()->create();
        $dto = new [ActionName]DTO(
            campo1: 'valor1',
            campo2: 1,
        );

        // Act
        $result = $this->action->execute($model, $dto);

        // Assert
        $this->assertTrue($result->campo1 === 'valor1');
    }

    // ==================== TEST: Caso Fallido ====================

    public function test_throws_exception_when_validation_fails(): void
    {
        // Arrange
        $model = [Model]::factory()->create();
        $dto = new [ActionName]DTO(campo1: 'invalid');

        // Act & Assert
        $this->expectException(\DomainException::class);
        $this->action->execute($model, $dto);
    }
}
```

### Paso 5: Template de Policy

```php
<?php
declare(strict_types=1);

namespace App\Modules\[Module]\Policies;

use App\Models\[Model];
use App\Models\User;

final class [Model]Policy
{
    public function view(User $user, [Model] $model): bool
    {
        return $user->hasPermissionTo('[module].view')
            || $model->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('[module].create');
    }

    public function update(User $user, [Model] $model): bool
    {
        return $user->hasPermissionTo('[module].update')
            || $model->user_id === $user->id;
    }

    public function delete(User $user, [Model] $model): bool
    {
        return $user->hasPermissionTo('[module].delete');
    }
}
```

## 🔧 Mejores Prácticas Aplicadas

### ✅ Esto Es Correcto

```php
// Action con DF inyectada
final class MyAction
{
    public function __construct(
        private readonly MyRepository $repository,
    ) {}
}

// DTO inmutable
final class MyDTO
{
    public function __construct(
        public readonly string $value,
    ) {}
}

// Enum con métodos
enum Status: string {
    case ACTIVE = 'active';
    
    public function label(): string { ... }
}

// Value Object validando
final class MoneyAmount
{
    public function __construct(float $amount) {
        if ($amount < 0) throw new \Exception('Invalid');
    }
}

// Repository con scopes
public function findActive(): Collection
{
    return Model::active()->get();
}

// Policy retornando bool
public function update(User $user, Model $model): bool
{
    return $user->id === $model->user_id;
}
```

### ❌ Esto Es Incorrecto

```php
// ❌ Action sin DI
class MyAction
{
    public function execute() {
        Auth::user();  // ❌ Facade directo
    }
}

// ❌ DTO mutable
class MyDTO
{
    public $value;  // ❌ public, no readonly
}

// ❌ Strings mágicos
if ($status === 'active') { ... }  // ❌ Magic string

// ❌ Value Object sin validación
class MoneyAmount {
    public function __construct(public $amount) {}  // ❌ Sin validar
}

// ❌ Queries directas en componente
public function getData() {
    Model::where(...)->get();  // ❌ Consulta en componente
}

// ❌ Policy sin retorno declarado
public function update($user, $model)  // ❌ Sin tipos
{
    // ...
}
```

## 📚 Checklist para Cada Feature Nueva

- [ ] Crear Action con responsabilidad única
- [ ] Crear DTO con `declare(strict_types=1)`
- [ ] Crear excepciones personalizadas
- [ ] Crear/actualizar Policy si hay autorización
- [ ] Agregar métodos a Repository si es necesario
- [ ] Crear tests (mínimo 5)
- [ ] Inyectar Action en componentes/controllers
- [ ] Documentar en PHPDoc
- [ ] Escribir migrations si toca DB
- [ ] Ejecutar tests: `php artisan test`

## 🎯 Estructura de Carpetas por Módulo

```
app/Modules/MyModule/
├── Actions/
│   ├── CreateAction.php
│   ├── UpdateAction.php
│   ├── DeleteAction.php
│   └── [OtherActions].php
├── DTOs/
│   ├── CreateDTO.php
│   ├── UpdateDTO.php
│   └── [OtherDTOs].php
├── Repositories/
│   └── [Model]Repository.php
├── Policies/
│   └── [Model]Policy.php
├── Exceptions/
│   └── [CustomException].php
├── ValueObjects/
│   └── [ValueObject].php
└── Models/ (si son específicas del módulo)
```

## 🔄 Flujo Estándar de Refactorización

1. **Identificar código problemático** (lógica en componente, etc.)
2. **Extraer a Action** con responsabilidad clara
3. **Crear DTO** para parámetros
4. **Crear excepciones** específicas del dominio
5. **Crear Repository** si hay consultas complejas
6. **Crear Policy** si hay autorización
7. **Inyectar en componente** y usar
8. **Testear exhaustivamente**

## 💡 Siglas y Conceptos

| Sigla | Significado | Cuándo |
|-------|-------------|--------|
| **DTO** | Data Transfer Object | Pasar datos entre capas |
| **Action** | Caso de uso | Lógica de negocio |
| **Repository** | Patron acceso datos | Encapsular queries |
| **Policy** | Autorización | Controlar permisos |
| **ValueObject** | Objeto de valor | Encapsular lógica con estado |
| **Enum** | Enumeración | Estados fijos |
| **DI** | Dependency Injection | Pasar dependencias constructor |

## 📖 Comandos Útiles

```bash
# Generar Action (manual)
touch app/Modules/[Module]/Actions/[Name]Action.php

# Generar tests
touch tests/Feature/Modules/[Module]/[Name]ActionTest.php

# Ejecutar tests módulo
php artisan test tests/Feature/Modules/[Module]

# Verificar tipado
grep -r "declare(strict_types=1)" app/Modules

# Contar líneas de código
wc -l app/Modules -r

# Buscar magic strings
grep -r "===\|==" app/Modules | grep -v "==="
```

## 🚨 Anti-patrones a Evitar

```php
// ❌ Action que llama a otra action
class MyAction {
    public function execute() {
        $this->otherAction->execute();  // ❌ Anidamiento
    }
}

// ❌ Componente con lógica compleja
final class MyComponent {
    public function update() {
        if ($this->validate()) {  // ❌ Validación aquí
            Model::where(...)->update(...);  // ❌ Query aquí
        }
    }
}

// ❌ Repository con lógica
class MyRepository {
    public function saveWithCalculations($data) {  // ❌ Lógica aquí
        $data['price'] = $data['qty'] * $data['rate'];
        return $this->model->create($data);
    }
}

// ❌ Policy sin considerar todos los casos
class MyPolicy {
    public function update(User $u, Model $m): bool {
        return $u->id === $m->user_id;  // ❌ Sin considerar admin
    }
}
```

## ✨ Cuando Algo es Difícil de Testear

Si una acción es difícil de testear:
1. Probablemente tiene múltiples responsabilidades
2. Solución: Dividirla en acciones más pequeñas
3. Cada acción debe ser **testeable** independientemente
4. Si necesita muchos mocks, refactoriza la dependencias

---

**Mantén estos patrones, ¡tu código será AWESOME!** 🔥
