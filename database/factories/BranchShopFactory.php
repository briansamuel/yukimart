<?php

namespace Database\Factories;

use App\Models\BranchShop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchShopFactory extends Factory
{
    protected $model = BranchShop::class;

    public function definition()
    {
        $provinces = [
            'Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ',
            'An Giang', 'Bà Rịa - Vũng Tàu', 'Bắc Giang', 'Bắc Kạn', 'Bạc Liêu',
            'Bắc Ninh', 'Bến Tre', 'Bình Định', 'Bình Dương', 'Bình Phước',
            'Bình Thuận', 'Cà Mau', 'Cao Bằng', 'Đắk Lắk', 'Đắk Nông'
        ];

        $districts = [
            'Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5',
            'Quận Hoàn Kiếm', 'Quận Ba Đình', 'Quận Đống Đa', 'Quận Hai Bà Trưng',
            'Huyện Gia Lâm', 'Huyện Đông Anh', 'Huyện Sóc Sơn'
        ];

        $wards = [
            'Phường Bến Nghé', 'Phường Đa Kao', 'Phường Cô Giang', 'Phường Nguyễn Cư Trinh',
            'Phường Phúc Xá', 'Phường Trúc Bạch', 'Phường Vĩnh Phúc', 'Phường Cống Vị',
            'Xã Đông Hòa', 'Xã Tân Triều', 'Xã Đức Giang'
        ];

        $shopTypes = ['flagship', 'standard', 'mini', 'kiosk'];
        $statuses = ['active', 'inactive', 'maintenance'];
        $workingDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $province = $this->faker->randomElement($provinces);
        $shopType = $this->faker->randomElement($shopTypes);
        $name = $this->generateShopName($province, $shopType);

        return [
            'code' => $this->generateUniqueCode(),
            'name' => $name,
            'address' => $this->faker->streetAddress,
            'province' => $province,
            'district' => $this->faker->randomElement($districts),
            'ward' => $this->faker->randomElement($wards),
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'manager_id' => User::inRandomOrder()->first()?->id,
            'warehouse_id' => \App\Models\Warehouse::inRandomOrder()->first()?->id,
            'status' => $this->faker->randomElement($statuses),
            'description' => $this->faker->optional()->paragraph,
            'opening_time' => $this->faker->time('H:i', '09:00'),
            'closing_time' => $this->faker->time('H:i', '22:00'),
            'working_days' => $this->faker->randomElements($workingDays, $this->faker->numberBetween(5, 6)),
            'area' => $this->faker->randomFloat(2, 50, 500),
            'staff_count' => $this->faker->numberBetween(3, 20),
            'shop_type' => $shopType,
            'has_delivery' => $this->faker->boolean(70), // 70% chance of having delivery
            'delivery_radius' => $this->faker->optional(0.7)->randomFloat(2, 2, 15),
            'delivery_fee' => $this->faker->optional(0.7)->randomFloat(2, 15000, 50000),
            'latitude' => $this->faker->latitude(8.0, 23.5), // Vietnam latitude range
            'longitude' => $this->faker->longitude(102.0, 109.5), // Vietnam longitude range
            'image' => $this->faker->optional()->imageUrl(640, 480, 'business'),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'created_by' => User::inRandomOrder()->first()?->id ?? 1,
            'updated_by' => User::inRandomOrder()->first()?->id ?? 1,
        ];
    }

    /**
     * Generate shop name based on location and type
     */
    private function generateShopName($province, $shopType)
    {
        $typeNames = [
            'flagship' => 'Flagship Store',
            'standard' => 'Store',
            'mini' => 'Mini Store',
            'kiosk' => 'Kiosk'
        ];

        $prefixes = [
            'YukiMart', 'Yuki Shop', 'Yuki Store', 'YM'
        ];

        $prefix = $this->faker->randomElement($prefixes);
        $typeName = $typeNames[$shopType];
        
        return "{$prefix} {$province} {$typeName}";
    }

    /**
     * Generate unique code
     */
    private function generateUniqueCode()
    {
        do {
            $code = 'YM' . $this->faker->unique()->numberBetween(1000, 9999);
        } while (BranchShop::where('code', $code)->exists());

        return $code;
    }

    /**
     * State for active branch shops
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'has_delivery' => true,
                'delivery_radius' => $this->faker->randomFloat(2, 5, 15),
                'delivery_fee' => $this->faker->randomFloat(2, 20000, 40000),
            ];
        });
    }

    /**
     * State for flagship stores
     */
    public function flagship()
    {
        return $this->state(function (array $attributes) {
            return [
                'shop_type' => 'flagship',
                'area' => $this->faker->randomFloat(2, 200, 500),
                'staff_count' => $this->faker->numberBetween(15, 30),
                'has_delivery' => true,
                'delivery_radius' => $this->faker->randomFloat(2, 10, 20),
                'status' => 'active',
            ];
        });
    }

    /**
     * State for mini stores
     */
    public function mini()
    {
        return $this->state(function (array $attributes) {
            return [
                'shop_type' => 'mini',
                'area' => $this->faker->randomFloat(2, 30, 100),
                'staff_count' => $this->faker->numberBetween(2, 8),
                'has_delivery' => $this->faker->boolean(40), // Less likely to have delivery
                'delivery_radius' => $this->faker->optional(0.4)->randomFloat(2, 2, 8),
            ];
        });
    }

    /**
     * State for kiosks
     */
    public function kiosk()
    {
        return $this->state(function (array $attributes) {
            return [
                'shop_type' => 'kiosk',
                'area' => $this->faker->randomFloat(2, 10, 50),
                'staff_count' => $this->faker->numberBetween(1, 4),
                'has_delivery' => false,
                'delivery_radius' => null,
                'delivery_fee' => null,
            ];
        });
    }

    /**
     * State for Ho Chi Minh City branches
     */
    public function hoChiMinhCity()
    {
        return $this->state(function (array $attributes) {
            $districts = [
                'Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7',
                'Quận 8', 'Quận 9', 'Quận 10', 'Quận 11', 'Quận 12', 'Quận Bình Thạnh',
                'Quận Gò Vấp', 'Quận Phú Nhuận', 'Quận Tân Bình', 'Quận Tân Phú'
            ];

            $wards = [
                'Phường Bến Nghé', 'Phường Đa Kao', 'Phường Cô Giang', 'Phường Nguyễn Cư Trinh',
                'Phường Bến Thành', 'Phường Cầu Kho', 'Phường Cầu Ông Lãnh', 'Phường Phạm Ngũ Lão'
            ];

            return [
                'province' => 'TP. Hồ Chí Minh',
                'district' => $this->faker->randomElement($districts),
                'ward' => $this->faker->randomElement($wards),
                'latitude' => $this->faker->latitude(10.7, 10.9),
                'longitude' => $this->faker->longitude(106.6, 106.8),
            ];
        });
    }

    /**
     * State for Hanoi branches
     */
    public function hanoi()
    {
        return $this->state(function (array $attributes) {
            $districts = [
                'Quận Hoàn Kiếm', 'Quận Ba Đình', 'Quận Đống Đa', 'Quận Hai Bà Trưng',
                'Quận Hoàng Mai', 'Quận Long Biên', 'Quận Tây Hồ', 'Quận Thanh Xuân',
                'Quận Cầu Giấy', 'Quận Hà Đông', 'Quận Nam Từ Liêm', 'Quận Bắc Từ Liêm'
            ];

            $wards = [
                'Phường Phúc Xá', 'Phường Trúc Bạch', 'Phường Vĩnh Phúc', 'Phường Cống Vị',
                'Phường Liễu Giai', 'Phường Nguyễn Trung Trực', 'Phường Quán Thánh', 'Phường Điện Biên'
            ];

            return [
                'province' => 'Hà Nội',
                'district' => $this->faker->randomElement($districts),
                'ward' => $this->faker->randomElement($wards),
                'latitude' => $this->faker->latitude(20.9, 21.1),
                'longitude' => $this->faker->longitude(105.7, 105.9),
            ];
        });
    }
}
