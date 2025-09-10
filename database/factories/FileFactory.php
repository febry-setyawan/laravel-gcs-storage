<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'text/plain',
            'application/msword',
            'application/vnd.ms-excel',
        ];

        $extensions = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'text/plain' => 'txt',
            'application/msword' => 'doc',
            'application/vnd.ms-excel' => 'xls',
        ];

        $mimeType = fake()->randomElement($mimeTypes);
        $extension = $extensions[$mimeType];
        $filename = fake()->slug().'_'.uniqid().'.'.$extension;

        return [
            'user_id' => User::factory(),
            'original_name' => fake()->words(3, true).'.'.$extension,
            'filename' => $filename,
            'path' => 'uploads/test/'.$filename,
            'mime_type' => $mimeType,
            'size' => fake()->numberBetween(1024, 5242880), // 1KB to 5MB
            'gcs_path' => 'uploads/test/'.$filename,
            'is_published' => fake()->boolean(30), // 30% chance of being published
            'description' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the file is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }

    /**
     * Indicate that the file is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
