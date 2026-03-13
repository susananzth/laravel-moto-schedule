<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

/**
 * Modelo User. Representa a los usuarios en la base de datos.
 * Implementa MustVerifyEmail para obligar a verificar el correo.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable; // HasRoles activa los métodos de Spatie (assignRole, hasPermissionTo, etc.)

    /**
     * $fillable: Lista blanca de atributos que se pueden asignar masivamente.
     * Protege contra ataques de 'Mass Assignment' donde un hacker intenta inyectar campos extra (ej: is_admin).
     * @var list<string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'phone',
        'email',
        'password',
    ];

    /**
     * $hidden: Atributos que NUNCA deben enviarse en respuestas JSON (API) o arrays.
     * Vital para no exponer contraseñas hasheadas o tokens.
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * casts: Conversión automática de tipos de datos al leer de la BD.
     * 'hashed' asegura que al asignar un password, Laravel maneje el hash correctamente (en versiones nuevas).
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Método auxiliar para obtener las iniciales del usuario (Ej: Juan Perez -> JP).
     * Útil para avatares cuando no hay foto.
     */
    public function initials(): string
    {
        // Usa la utilidad Str de Laravel para manipulación segura de cadenas
        $firstname_initial = Str::of($this->firstname)
            ->explode(' ') // Separa por espacios
            ->take(1)      // Toma el primer nombre
            ->map(fn ($word) => Str::substr($word, 0, 1)) // Toma la primera letra
            ->implode('');

        $lastname_initial = Str::of($this->lastname)
            ->explode(' ')
            ->take(1)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');

        return $firstname_initial . $lastname_initial;
    }

    /**
     * Personalización del envío de correo de restablecimiento de contraseña.
     * Usamos nuestra propia notificación para controlar el diseño del email.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Personalización del envío de verificación de email.
     * Detectamos si es un usuario recién creado para enviar un mensaje de "Bienvenida" vs "Verificación simple".
     */
    public function sendEmailVerificationNotification()
    {
        // wasRecentlyCreated es una propiedad mágica de Eloquent que indica si el modelo se acaba de insertar en este request.
        $this->notify(new VerifyEmailNotification($this->wasRecentlyCreated));
    }

    // --- RELACIONES (Eloquent) ---

    /**
     * Un usuario (Cliente) tiene muchas citas solicitadas.
     *
     * @return HasMany
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Un usuario (Técnico) tiene muchas citas asignadas para trabajar.
     * Especificamos 'technician_id' porque no sigue la convención estándar (user_id).
     *
     * @return HasMany
     */
    public function assignedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'technician_id');
    }
}
