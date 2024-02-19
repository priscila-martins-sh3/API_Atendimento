<?php

namespace Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Service;
use App\Models\Contact;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'support_id'=> null,
            'contact_id' => function () {
                
                return Contact::factory()->create()->id;
            },
            'tipo_servico' =>fake()->randomElement(['tirar_duvida', 'informar_problema', 'solicitar_recurso']),       
            'retorno' => true,
            'informacoes'=> null,

        ];
    }
   
}

