<?php
namespace Database\Seeders;
use App\Services\HostService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
class HostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        

        $faker = \Faker\Factory::create('en_US');
        $array_type = [
            ['Khách sạn', 'hotel'],
            ['Resort', 'resort'],
            ['Chung cư', 'apartment'],
            ['Home stay', 'homestay'],
            ['Biệt thự', 'villa'],
        ];
        $array_name = ['Minh', 'Hải', 'An', 'Phú', 'Điền', 'Khả', 'Hân', 'Niệm', 'Liễu', 'Như', 'Mường', 'Thanh', 'Diệp', 'Loan', 'Như', 'Ý', 'Nhật'];
        
        $randIndex = array_rand($array_name);
        $randIndex2 = array_rand($array_name);
        $randIndex3 = array_rand($array_type);
        //
        $host = \DB::table('hosts')->first();
        $province = \DB::table('provinces')->inRandomOrder()->first();
        $district = \DB::table('districts')->where('_province_id', $province->id)->first();
        $ward = \DB::table('wards')->where('_district_id', $district->id)->first();
        \DB::table('hosts')->insert([
            'host_name' => $array_type[$randIndex3][0].' '.$array_name[$randIndex].' '.$array_name[$randIndex2],
            // 'host_name' => $array_type[$randIndex3][0].' '.$faker->company,
            'host_slug' => \Str::slug($array_type[$randIndex3][0].' '.$array_name[$randIndex].' '.$array_name[$randIndex2]),
            // 'host_slug' => Str::slug($array_type[$randIndex3][0].' '.$faker->company),
            'host_description' => $host->host_policy,
            'host_thumbnail' => $host->host_thumbnail,
            'host_policy' =>  $host->host_policy,
            'host_convenient' => $host->host_convenient,
            'host_address' => $host->host_address,
            'host_lat' => $host->host_lat,
            'host_lng' => $host->host_lng,
            'host_status' => 'publish',
            'host_gallery' => $host->host_gallery,
            'host_type' => $array_type[$randIndex3][1],
            'lowest_room_rates' => rand(99000,1299000),
            'province_id' => $province->id,
            'district_id' => $district->id,
            'ward_id' => $ward->id,
            'province_name' => $province->_name,
            'district_name' => $district->_name,
            'ward_name' => $ward->_name,
            'language' => 'vi',
            'created_by_agent' => 1,
            'updated_by_agent' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);

       
    }
}
