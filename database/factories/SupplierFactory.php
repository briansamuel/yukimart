<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $companies = [
            'Công ty TNHH ABC',
            'Công ty Cổ phần XYZ',
            'Doanh nghiệp tư nhân DEF',
            'Công ty TNHH MTV GHI',
            'Tập đoàn JKL',
            'Công ty Cổ phần MNO',
            'Công ty TNHH PQR',
            'Doanh nghiệp STU',
            'Công ty VWX',
            'Tổng công ty YZ'
        ];

        $groups = [
            'Điện tử',
            'Thời trang',
            'Gia dụng',
            'Thực phẩm',
            'Mỹ phẩm',
            'Văn phòng phẩm',
            'Đồ chơi',
            'Thể thao',
            'Sách báo',
            'Nội thất'
        ];

        $provinces = [
            'Hà Nội',
            'TP. Hồ Chí Minh',
            'Đà Nẵng',
            'Hải Phòng',
            'Cần Thơ',
            'Bình Dương',
            'Đồng Nai',
            'Khánh Hòa',
            'Lâm Đồng',
            'Quảng Nam'
        ];

        $districts = [
            'Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5',
            'Quận Hoàn Kiếm', 'Quận Ba Đình', 'Quận Cầu Giấy',
            'Huyện Gia Lâm', 'Huyện Đông Anh'
        ];

        $wards = [
            'Phường 1', 'Phường 2', 'Phường 3', 'Phường 4',
            'Phường Bến Nghé', 'Phường Đa Kao', 'Phường Cô Giang',
            'Phường Nguyễn Cư Trinh', 'Phường Cầu Ông Lãnh'
        ];

        return [
            'code' => 'SUP' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'name' => $this->faker->company(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'company' => $this->faker->randomElement($companies),
            'tax_code' => $this->faker->numerify('##########'),
            'address' => $this->faker->streetAddress(),
            'province' => $this->faker->randomElement($provinces),
            'district' => $this->faker->randomElement($districts),
            'ward' => $this->faker->randomElement($wards),

            'group' => $this->faker->randomElement($groups),
            'note' => $this->faker->optional(0.7)->sentence(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }

    /**
     * Indicate that the supplier is active.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
            ];
        });
    }

    /**
     * Indicate that the supplier is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'inactive',
            ];
        });
    }

    /**
     * Indicate that the supplier has no company.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function individual()
    {
        return $this->state(function (array $attributes) {
            return [
                'company' => null,
                'tax_code' => null,
            ];
        });
    }

    /**
     * Indicate that the supplier is from a specific group.
     *
     * @param string $group
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function group($group)
    {
        return $this->state(function (array $attributes) use ($group) {
            return [
                'group' => $group,
            ];
        });
    }

    /**
     * Indicate that the supplier is from Ho Chi Minh City.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function hoChiMinhCity()
    {
        return $this->state(function (array $attributes) {
            return [
                'province' => 'TP. Hồ Chí Minh',
                'district' => $this->faker->randomElement(['Quận 1', 'Quận 2', 'Quận 3', 'Quận 7', 'Quận Bình Thạnh']),
                'ward' => $this->faker->randomElement(['Phường Bến Nghé', 'Phường Đa Kao', 'Phường Cô Giang']),
            ];
        });
    }

    /**
     * Indicate that the supplier is from Hanoi.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function hanoi()
    {
        return $this->state(function (array $attributes) {
            return [
                'province' => 'Hà Nội',
                'district' => $this->faker->randomElement(['Quận Hoàn Kiếm', 'Quận Ba Đình', 'Quận Cầu Giấy', 'Quận Đống Đa']),
                'ward' => $this->faker->randomElement(['Phường Hàng Bài', 'Phường Hàng Trống', 'Phường Cửa Nam']),
            ];
        });
    }
}
