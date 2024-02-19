<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Contact;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome_pessoa' => fake()->name(),
            'nome_cliente' => fake()->company(),
            'area_atendimento' =>fake()->randomElement(['contabilidade', 'departamento pessoal', 'compras']),       
        ];
    }
   
}





