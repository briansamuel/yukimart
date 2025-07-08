<?php

namespace App\Helpers;

class ConvenientHelper {

    public static function getConvenient() {

        $convenients = [
            'service_hotel' => [
                'staff_carry_luggage' => "Nhân viên xách hành lý",
                'free_welcome_drink' => "Thức uống chào mừng miễn phí",
                'concierge_services' => "Dịch vụ concierge/hỗ trợ khách",
                'currency_exchange_service' => "Dịch vụ thu đổi ngoại tệ",
                'early_check_in' => "Check In sớm",
                'express_check-in_service' => "Dịch vụ nhận phòng cấp tốc",
                'express_check-out_service' => "Dịch vụ trả phòng cấp tốc",
                'reception' => "Quầy lễ tân",
                'reception_24h' => "Lễ tân 24h",
                '24 hour security' => "Bảo vệ 24 giờ",
                'late_check-out_service' => "Dịch vụ trả phòng muộn",
                'laundry_service' => "Dịch vụ giặt ủi",
                'luggage_storage_service' => "Dịch vụ lưu trữ/bảo quản hành lý",
                'medical_care' => "Dịch vụ chăm sóc y tế",
                'daily_newspaper_in_the_lobby' => "Nhật báo tại sảnh",
                'daily_news' => "Nhật báo",
                'gate_guard' => "Nhân viên gác cổng",
                'tour_booking_support_service' => "Dịch vụ hỗ trợ đặt Tour",
            ],

            'foods' => [
                'breakfast_with_menu' => "Bữa sáng với thực đơn gọi món",
                'bar' => "Quầy bar",
                'breakfast' => "Bữa sáng",
                'breakfast_buffet' => "Bữa sáng món tự chọn",
                'coffe' => "Tiệm cà phê",
                'dood_court' => "Khu ẩm thực",
                'diner_with_menu' => "Bữa tối với thực đơn chọn sẵn",
                'launch_with_menu' => "Bữa trưa với thực đơn chọn sẵn",
                'snacks' => "Đồ ăn nhẹ",
                'vegetarian_dishes' => "Phục vụ món chay",
            ],

            'convenient_room' => [
                'bathrobe' => "Áo choàng tắm",
                'bathtub' => "Bồn tắm",
                'cable_television' => "Truyền hình cáp",
                'room_desk' => "Bàn làm việc",
                'hairdryer' => "Máy sấy tóc",
                'safe_vault' => "Két an toàn trong phòng",
                'mini_kitchen' => "Nhà bếp mini",
                'mini_bar' => "Minibar",
                'shower' => "Phòng tắm vòi sen",
                'television' => "TV",
            ],

            'convenient_general' => [
                'wifi' => "Kết nối wifi",
                'air_conditioner' => "Máy lạnh",
                'banquet' => "Tiệc chiêu đãi",
                'locker_room' => "Phòng giữ đồ",
                'family_room' => "Phòng gia đình",
                'no_smoke_room' => "Phòng không hút thuốc",
                'swimming_pool' => "Hồ bơi",
                'terrace' => "Sân thượng/Sân hiên",
                'no_smoke' => "Không khói thuốc",
               
            ],

            'convenient_public' => [
                'parking' => "Bãi đậu xe",
                'coffee_in_lobby' => "Cà phê/trà tại sảnh",
                'elevator' => "Thang máy",
                'restaurent' => "Nhà hàng",
                'housekeeper' => "Dịch vụ dọn phòng",
                'wifi_publish' => "WiFi tại khu vực chung",
            ],

            'convenient_office' => [
                'office_service' => "Dịch vụ văn phòng",
                'office_covenient' => "Các tiện nghi văn phòng",
                'computer' => "Máy tính",
                'conference_reception' => "Lễ tân hội nghị",
                'conference_room' => "Phòng hội nghị",
                'conference_convenient' => "Tiện nghi phục vụ hội họp",
                'photocopy' => "Máy photocopy",
                'projectors' => "Máy chiếu",
            ],

            'convenient_activaty' => [
                'swimming_pool_in-door' => "Hồ bơi trong nhà",
                'swimming_pool_out-door' => "Hồ bơi ngoài trời",
                'karaoke' => "Karaoke",
                'massage' => "Mát-xa",
                'umbrella' => "Dù (ô) che nắng",
                'spa_service' => "Dịch vụ spa",
                'sauna_service' => "Xông hơi khô/ướt",
                'children_play_area' => "Khu vực vui chơi trẻ em",
            ],

            'convenient_transport' => [
                'airport_transfer' => "Đưa đón sân bay",
                'motorbike_for_rent' => "Cho thuê xe máy",
                'car_for_rent' => "Cho thuê xe hơi",
                'limited_parking' => "Bãi đậu xe hạn chế",
            ],
            
            'support_people' => [
                'convenient_people_disabilities' => "Thuận tiện cho người khuyết tật",
                'walkways_people_disabilities' => "Lối đi lại cho người khuyết tật",
                'suitable_for_wheelchairs' => "Phù hợp cho xe lăn",
                
            ]
        ];
        return $convenients;
    }

    public static function getConvenientRoom() {

        $convenients = [
            'bathroom_accessories' => [
                'bathrobe' => [
                    'icon' => 'fa-scarf',
                    'text' => "Áo choàng tắm"
                ],
                'bathtub' => [
                    'icon' => 'fa-bath',
                    'text' => "Bồn tắm",
                ],
                'towels' => [
                    'icon' => 'fa-heat',
                    'text' => "Các loại khăn",
                ],
                'scales' => [
                    'icon' => 'fa-weight',
                    'text' => "Cân",
                ],
                'phone_bathroom' => [
                    'icon' => 'fa-blender-phone',
                    'text' => 'Điện thoại trong phòng tắm',
                ],
                'mirror' => [
                    'icon' => 'fa-webcam',
                    'text' => 'Gương',
                ],
                'hairdryer' => [
                    'icon' => 'fa-hairdryer',
                    'text' => 'Máy sấy tóc',
                ],
                'wash_utensils' => [
                    'icon' => 'fa-duck',
                    'text' => 'Vật dụng tắm rửa',
                ],
                
            ],
            'entertaiments' => [
                'phone' => [
                    'icon' => 'fa-phone',
                    'text' => 'Điện thoại',
                ],
                'wifi' => [
                    'icon' => 'fa-wifi',
                    'text' => 'Wi-Fi miễn phí trong tất cả các phòng!',
                ],
                'cable_tv' => [
                    'icon' => 'fa-tv-music',
                    'text' => 'Truyền hình cáp/vệ tinh',
                ],
            ],
            'convenients' => [
                'slipper' => [
                    'icon' => 'fa-shoe-prints',
                    'text' => 'Dép đi trong nhà',
                ],
                'alarm_service' => [
                    'icon' => 'fa-alarm-snooze',
                    'text' => 'Dịch vụ báo thức',
                ],
                'air_conditioning' => [
                    'icon' => 'fa-air-conditioner',
                    'text' => 'Điều hòa',
                ],
                'umbrella' => [
                    'icon' => 'fa-umbrella',
                    'text' => 'Ô/Dù',
                ],
                'blinds_light' => [
                    'icon' => 'fa-blinds',
                    'text' => 'Rèm che nắng',
                ],
                'heating' => [
                    'icon' => 'fa-heat',
                    'text' => 'Sưởi',
                ],
            ],
            'eating' => [
                'free_coffee' => [
                    'icon' => 'fa-coffee-pot',
                    'text' => 'Cà phê hòa tan miễn phí',
                ],
                'free_water' => [
                    'icon' => 'fa-wine-bottle',
                    'text' => 'Nước đóng chai miễn phí',
                ],
                'free_tea' => [
                    'icon' => 'fa-mug-tea',
                    'text' => 'Trà miễn phí',
                ],
                'fridge' => [
                    'icon' => 'fa-refrigerator',
                    'text' => 'Tủ lạnh',
                ],
            ],
            'layout_and_interio' => [
                'desk_work' => [
                    'icon' => 'fa-border-style-alt',
                    'text' => 'Bàn làm việc'
                ]
            ],
            'clothes_and_laundry' => [
                'shoe_polish_set' => [
                    'icon' => 'fa-ring',
                    'text' => 'Bộ đánh giày',
                ],
                'clothes_rack' => [
                    'icon' => 'fa-oven',
                    'text' => 'Giá treo quần áo',
                ],
                'clothes_dryer_pay' => [
                    'icon' => 'fa-dryer-alt',
                    'text' => 'Máy sấy quần áo',
                ],
                'closet' => [
                    'icon' => 'fa-door-closed',
                    'text' => 'Tủ quần áo',
                ],
            ],
            'security_item' => [
                'in_room_safe_laptop' => [
                    'icon' => 'fa-sensor',
                    'text' => 'Két sắt cho laptop',
                ],
                'in_room_safe' => [
                    'icon' => 'fa-outlet    ',
                    'text' => 'Két sắt trong phòng',
                ],
                'fire_extinguisherk' => [
                    'icon' => 'fa-fire-extinguisher',
                    'text' => 'Bình chữa cháy',
                ],
                'smoke_detector' => [
                    'icon' => 'fa-sensor-smoke',
                    'text' => 'Đầu báo khói',
                ],
                
            ]
        ];

        
        return $convenients;
    }
}