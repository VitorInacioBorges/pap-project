<?php

namespace Database\Factories;

use App\Models\Anamnese;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Anamnese>
 */
class AnamneseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'data_atendimento' => '2026-11-14 10:30:00',
            'observacoes' => 'Observações do atendimento.',
        ];
    }
}
