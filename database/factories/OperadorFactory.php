<?php

namespace Database\Factories;

use App\Fila\Enums\StatusOperador;
use App\Models\Operador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Operador>
 */
class OperadorFactory extends Factory
{
    protected $model = Operador::class;

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
