<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GuestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create('en_US');
        DB::table('guests')->insert([
            'username' => $faker->username,
            'password' => bcrypt($faker->password),
            'email' =>  $faker->email,
            'full_name' =>  $faker->company,
            'active_code' => Str::random(16),
            'group_id' => '',
            'guest_avatar' => '',
            'guest_address' => $faker->address,
            'guest_phone' => $faker->phoneNumber,
            'guest_birthday' => date("Y-m-d H:i:s"),
            'status' =>  'active',
            'provider' =>  '',
            'provider_id' =>  '',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
    }
}
