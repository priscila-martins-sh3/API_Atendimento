<?php

namespace Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Support;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Support>
 */
class SupportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [        
            'area_atuacao' =>fake()->randomElement(['contabilidade', 'departamento pessoal', 'compras']), 
            'livre' => true,  
            'user_id' => function () {
                
                return User::factory()->create(['tipo_funcionario' => 'suporte'])->id;
            },  
        ];
    }
   
}