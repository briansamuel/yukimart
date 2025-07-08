<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vietnameseNames = [
            'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Văn Cường', 'Phạm Thị Dung', 'Hoàng Văn Em',
            'Vũ Thị Phương', 'Đặng Văn Giang', 'Bùi Thị Hoa', 'Đỗ Văn Inh', 'Ngô Thị Kim',
            'Lý Văn Long', 'Đinh Thị Mai', 'Tạ Văn Nam', 'Phan Thị Oanh', 'Võ Văn Phúc',
            'Chu Thị Quỳnh', 'Dương Văn Rồng', 'Lưu Thị Sương', 'Triệu Văn Tài', 'Cao Thị Uyên',
            'Hồ Văn Vinh', 'Lâm Thị Xuân', 'Tôn Văn Yên', 'Huỳnh Thị Zung', 'Mã Văn Bảo',
            'Thái Thị Cẩm', 'Lại Văn Đức', 'Ông Thị Ế', 'Từ Văn Phong', 'Quan Thị Giang',
            'Trịnh Văn Hải', 'Kiều Thị Ích', 'Lộc Văn Khánh', 'Ưng Thị Linh', 'Ấu Văn Minh',
            'Âu Thị Nga', 'Ô Văn Oanh', 'Ư Thị Phúc', 'Y Văn Quang', 'Ỷ Thị Rạng'
        ];

        $vietnameseAddresses = [
            'Số 123 Đường Lê Lợi, Quận 1, TP.HCM',
            'Số 456 Đường Nguyễn Huệ, Quận 3, TP.HCM',
            'Số 789 Đường Trần Hưng Đạo, Quận 5, TP.HCM',
            'Số 321 Đường Võ Văn Tần, Quận 10, TP.HCM',
            'Số 654 Đường Cách Mạng Tháng 8, Quận Tân Bình, TP.HCM',
            'Số 987 Đường Hoàng Văn Thụ, Quận Phú Nhuận, TP.HCM',
            'Số 147 Đường Lý Tự Trọng, Quận 1, TP.HCM',
            'Số 258 Đường Pasteur, Quận 3, TP.HCM',
            'Số 369 Đường Điện Biên Phủ, Quận Bình Thạnh, TP.HCM',
            'Số 741 Đường Nguyễn Thị Minh Khai, Quận 1, TP.HCM',
            'Số 852 Đường Hai Bà Trưng, Quận 1, Hà Nội',
            'Số 963 Đường Hoàn Kiếm, Quận Hoàn Kiếm, Hà Nội',
            'Số 159 Đường Đống Đa, Quận Đống Đa, Hà Nội',
            'Số 357 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội',
            'Số 468 Đường Thanh Xuân, Quận Thanh Xuân, Hà Nội',
            'Số 579 Đường Hà Đông, Quận Hà Đông, Hà Nội',
            'Số 680 Đường Long Biên, Quận Long Biên, Hà Nội',
            'Số 791 Đường Gia Lâm, Quận Gia Lâm, Hà Nội',
            'Số 802 Đường Đan Phượng, Huyện Đan Phượng, Hà Nội',
            'Số 913 Đường Sóc Sơn, Huyện Sóc Sơn, Hà Nội'
        ];

        $name = $this->faker->randomElement($vietnameseNames);
        $phone = '0' . $this->faker->numberBetween(900000000, 999999999);
        
        return [
            'name' => $name,
            'phone' => $phone,
            'email' => $this->faker->optional(0.7)->email(),
            'address' => $this->faker->randomElement($vietnameseAddresses),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the customer is a VIP customer.
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => 'Khách hàng VIP - Ưu tiên phục vụ',
        ]);
    }

    /**
     * Indicate that the customer has no email.
     */
    public function withoutEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
        ]);
    }

    /**
     * Indicate that the customer is from Ho Chi Minh City.
     */
    public function fromHCM(): static
    {
        $hcmAddresses = [
            'Số 123 Đường Lê Lợi, Quận 1, TP.HCM',
            'Số 456 Đường Nguyễn Huệ, Quận 3, TP.HCM',
            'Số 789 Đường Trần Hưng Đạo, Quận 5, TP.HCM',
            'Số 321 Đường Võ Văn Tần, Quận 10, TP.HCM',
            'Số 654 Đường Cách Mạng Tháng 8, Quận Tân Bình, TP.HCM',
        ];

        return $this->state(fn (array $attributes) => [
            'address' => $this->faker->randomElement($hcmAddresses),
        ]);
    }

    /**
     * Indicate that the customer is from Hanoi.
     */
    public function fromHanoi(): static
    {
        $hanoiAddresses = [
            'Số 852 Đường Hai Bà Trưng, Quận 1, Hà Nội',
            'Số 963 Đường Hoàn Kiếm, Quận Hoàn Kiếm, Hà Nội',
            'Số 159 Đường Đống Đa, Quận Đống Đa, Hà Nội',
            'Số 357 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội',
            'Số 468 Đường Thanh Xuân, Quận Thanh Xuân, Hà Nội',
        ];

        return $this->state(fn (array $attributes) => [
            'address' => $this->faker->randomElement($hanoiAddresses),
        ]);
    }
}
