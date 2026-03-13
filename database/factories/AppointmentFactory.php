<?php

namespace Database\Factories;

use App\Core\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scheduled = Carbon::now()->addDays($this->faker->numberBetween(1, 30))->setHour($this->faker->numberBetween(8, 17));

        return [
            'user_id' => User::factory(),
            'service_id' => Service::factory(),
            'technician_id' => null,
            'scheduled_at' => $scheduled,
            'status' => AppointmentStatus::PENDING->value,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
