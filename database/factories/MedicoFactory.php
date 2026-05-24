<?php

namespace Database\Factories;

use App\Fila\Enums\StatusOperador;
use App\Models\Medico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medico>
 */
class MedicoFactory extends Factory
{
    protected $model = Medico::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'cpf' => fake()->unique()->numerify('###########'),
            'password' => 'password',
            'status' => StatusOperador::Ativo,
        ];
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['status' => StatusOperador::Inativo]);
    }
}
