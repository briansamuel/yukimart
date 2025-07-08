<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\Helpers\StringHelper;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
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
            'page_title' => $title,
            'page_slug' => $slug,
            'page_description' => $this->faker->sentence,
            'page_content' => $this->faker->realText,
            'page_author' => $this->faker->name,
            'page_type' => 'page',
            'language' => 'vi',
            'page_status' => $status[rand(0, 3)]

        ];
    }
}
