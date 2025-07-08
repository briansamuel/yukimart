<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\Helpers\StringHelper;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $title = $this->faker->sentence;
        $slug = Str::slug($title);
        $status = ['trash', 'pending', 'draft', 'publish'];
        return [
            'post_title' => $title,
            'post_slug' => $slug,
            'post_description' => $this->faker->sentence,
            'post_content' => $this->faker->realText,
            'post_author' => $this->faker->name,
            'post_type' => 'news',
            'language' => 'vi',
            'post_status' => $status[rand(0, 3)]

        ];
    }
}
