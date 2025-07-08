<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = $this->faker->company;

        $status = ['in_progress', 'pending', 'completed'];
        return [
            'task_name' => $name,

            'task_description' => $name,
            'task_content' => $this->faker->sentence,
            'task_attachments' => json_encode([]),
            'task_progress' => rand(0, 100),
            'task_due_date' => date('Y-m-d', strtotime('+2 months')),
            'task_notifications' => json_encode([]),
            'task_status' => $status[rand(0, 2)],
            'task_category' => 'default',
            'project_id' => rand(1, 60),
            'language' => 'vi',
            'created_by_user' => 1,
            'updated_by_user' => 1,

        ];
    }
}
