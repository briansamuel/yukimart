<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \DB::table('categories')->insert([
            'category_name' => Str::random(20),
            'category_slug' => Str::random(20),
            'category_description' => Str::random(16),
            'category_type' => 'category_of_news',
            'category_parent' => 0,
            'language' => 'vi',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
    }
}
