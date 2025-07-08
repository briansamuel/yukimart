<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class AgentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('agents')->insert([
            'username' => Str::random(10),
            'password' => Str::random(40),
            'email' =>  Str::random(40).'@gmail.com',
            'full_name' =>  Str::random(40),
            'active_code' => Str::random(16),
            'group_id' => '',
            'agent_avatar' => Str::random(16),
            'agent_address' => Str::random(36),
            'agent_phone' => mt_rand(),
            'agent_birthday' => date("Y-m-d H:i:s"),
            'agent_parent' => 0,
            'status' =>  'active',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
    }
}
