<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ContactsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 0; $i < 20; $i++) {
            $id = DB::table('contacts')->insertGetId([
                'name' => Str::random(10),
                'email' => Str::random(10).'@gmail.com',
                'phone_number' => '0' . mt_rand(374720460, 999999999),
                'address' => Str::random(20),
                'subject' => Str::random(10),
                'content' => Str::random(100),
                'status' => mt_rand(0,1) === 1 ? 'read' : 'unread',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);

            for($j=0; $j < 5; $j++) {
                DB::table('contact_replies')->insertGetId([
                    'message' => Str::random(30),
                    'contact_id' => $id,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
            }
        }
    }
}
