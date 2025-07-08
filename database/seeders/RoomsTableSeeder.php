<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Factory;
class RoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $price_one_night = rand(99000,1299000);
        $sale_for_room = rand(0,3)*10*$price_one_night/100;
        $host = \DB::table('hosts')->inRandomOrder()->first();
        $agent = \DB::table('agents')->inRandomOrder()->first();
        $room = \DB::table('rooms')->first();
        $array_type = [
            'Phòng Deluxe',
            'Phòng Classic',
            'Phòng 2 giường ngủ',
            'Phòng đơn giản',
            'Phòng King',
            'Phòng Queen',
            'Phòng Diamond'
        ];
        $single_bed = rand(0,4);
        $twin_bed = $single_bed == 0 ? rand(1,2) : rand(0,2);
        $guest_amount = $single_bed + ($twin_bed*2);
        $room_option = '{"single_bed":"'.$single_bed.'","twin_beds":"'.$twin_bed.'"}';
        $randIndex3 = array_rand($array_type);
        DB::table('rooms')->insert([
           
            'host_id' => $host->id,
            'room_name' => $array_type[$randIndex3],
            'room_description' => $array_type[$randIndex3],
            'room_gallery' => $room->room_gallery,
            'room_area' => rand(14,100),
            'room_convenient' => $room->room_convenient,
            'room_option' => $room_option,
            'price_one_night' => $price_one_night,
            'sale_for_room' => $sale_for_room,
            'guest_amount' => $guest_amount,
            'room_amount_empty' => rand(1,3),
            'room_status' => 'available_room',
            'language' => 'vi',
            'created_by_agent' => $agent->id,
            'updated_by_agent' => $agent->id,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);

        $room_lowest = DB::table('rooms')->where('host_id', $host->id)->orderBy('price_one_night', 'ASC')->first();
        $lowest_room_rates = $room_lowest->sale_for_room == 0 ? $room_lowest->price_one_night : $room_lowest->sale_for_room;
        DB::table('hosts')->where('id', $room_lowest->host_id)->update(array('lowest_room_rates' => $lowest_room_rates ));
    }
}
