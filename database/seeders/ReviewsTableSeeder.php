<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Factory;
class ReviewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
       

        $faker = Faker\Factory::create('en_US');

        //
        $host = \DB::table('hosts')->inRandomOrder()->first();
        $guest = \DB::table('guests')->inRandomOrder()->first();
        \DB::table('reviews')->insert([
            'host_id' => 466,
            'review_title' => $faker->sentence,
            'review_content' => $faker->text,
            'review_image' => '',
            'rating_review' => rand(1,5),
            'language' => 'vi',
            'name_guest' =>  $guest->full_name,
            'created_by_guest' =>  $guest->id,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);

    }
}
