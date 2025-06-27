<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ElementDefinition>
 */
class ElementDefinitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $elementTypes = ['stage', 'lighting', 'sound', 'equipment', 'decoration', 'security'];
        $elementType = $this->faker->randomElement($elementTypes);

        return [
            'element_type' => $elementType,
            'display_name' => ucfirst($elementType) . ' ' . $this->faker->word(),
            'description' => $this->faker->paragraph(),
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'quantity' => ['type' => 'integer', 'minimum' => 1],
                    'specifications' => ['type' => 'string'],
                    'requirements' => ['type' => 'string'],
                ],
                'required' => ['quantity']
            ],
            'default_details_template' => [
                'quantity' => 1,
                'specifications' => '',
                'requirements' => '',
            ],
            'recommended_elements' => $this->faker->randomElements($elementTypes, 2),
        ];
    }
}
