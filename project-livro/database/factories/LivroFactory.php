<?php

namespace Database\Factories;

use App\Models\Livro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Livro>
 */
class LivroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo'        => fake()->sentence(3),
            'genero'        => fake()->randomElement(['ficcao', 'suspense', 'terror', 'drama', 'fantasia', 'misterio', 'biografia']),
            'preco'         => fake()->randomFloat(2, 10, 200), 
            'estoque'       => fake()->numberBetween(1, 100), 
            'caminho_imagem' => null, 
        ];
    }
}