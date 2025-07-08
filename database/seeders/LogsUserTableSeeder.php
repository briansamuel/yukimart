<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Factory;
class LogsUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('logs_user')->insert([
            'user_id' => rand(1, 20),
            'email' =>  \Str::random(20).'@gmail.com',
            'full_name' =>  \Str::random(10),
            'action' => 'Xóa thông tin 1 khách hàng có id = 2',
            'content' => '{"user_id":"2"}',
            'ip' => '192.1.1.1',
            'timestamp' => time()
        ]);
    }
}
