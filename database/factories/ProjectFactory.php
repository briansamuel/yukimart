<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\Helpers\StringHelper;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = $this->faker->company;
        $slug = Str::slug($name);
        $status = ['in_progress', 'pending', 'over_due', 'completed'];
        $framework = ['html5', 'vue', 'reactjs', 'php', 'laravel', 'golang', 'python'];
        $status = ['in_progress', 'pending', 'over_due', 'completed'];
        return [
            'project_name' => $name,

            'project_description' => $name,
            'project_type' => 'default',
            'project_database' => 'mysql',
            'project_framework' => $framework[rand(0, 3)],
            'project_due_date' => date('Y-m-d', strtotime('+2 months')),
            'project_notifications' => json_encode([]),
            'project_status' => $status[rand(0, 3)],
            'project_category' => 'default',
            'project_budget' => mt_rand(1, 1000) * 1000,
            'language' => 'vi',
            'created_by_user' => 1,
            'updated_by_user' => 1,

        ];
    }
}
