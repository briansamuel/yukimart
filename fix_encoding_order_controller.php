<?php

$file = __DIR__ . '/app/Http/Controllers/Tenant/Sales/OrderController.php';
$content = file_get_contents($file);

// Map of incorrect characters to correct ones
$replacements = [
    '?ơn h?ng' => 'đơn hàng',
    '?ược' => 'được',
    '?�?' => 'để ',
    '?ang x�?lý' => 'đang xử lý',
    'd�?liệu' => 'dữ liệu',
    'ban ?ầu' => 'ban đầu',
    'D�?liệu' => 'Dữ liệu',
    'h?ng' => 'hàng',
    'hợp l�?' => 'hợp lệ',
    'S�?' => 'Số ',
    '?iện thoại' => 'điện thoại',
    'th�?' => 'thể ',
    'xuất ?ơn' => 'xuất đơn',
    'Trần Th�?B' => 'Trần Thị B',
    'ch�?' => 'chờ ',
    'x�?lý' => 'xử lý',
    '?ơn' => 'đơn',
    '?�?' => 'để ',
    'n?o' => 'nào',
    'n?a' => 'nữa',
    'Không có ?ơn h?ng n?o ?ược chọn ?�?xóa.' => 'Không có đơn hàng nào được chọn để xóa.',
    '?ang x�?lý' => 'đang xử lý',
    'Lỗi khi tải d�?liệu ban ?ầu:' => 'Lỗi khi tải dữ liệu ban đầu:',
    'D�?liệu khách h?ng không hợp l�?' => 'Dữ liệu khách hàng không hợp lệ',
    'S�??iện thoại không ?ược ?�?trống' => 'Số điện thoại không được để trống',
    'Lỗi khi kiểm tra s�??iện thoại:' => 'Lỗi khi kiểm tra số điện thoại:',
    'Không th�?xuất ?ơn h?ng' => 'Không thể xuất đơn hàng',
    'Vui lòng chọn ít nhất một ?ơn h?ng ?�?xuất Excel.' => 'Vui lòng chọn ít nhất một đơn hàng để xuất Excel.',
    'Không tìm thấy ?ơn h?ng n?o ?�?xuất.' => 'Không tìm thấy đơn hàng nào để xuất.',
    'Vui lòng chọn ít nhất một ?ơn h?ng ?�?cập nhật.' => 'Vui lòng chọn ít nhất một đơn hàng để cập nhật.',
    'Vui lòng chọn ít nhất một trạng thái ?�?cập nhật.' => 'Vui lòng chọn ít nhất một trạng thái để cập nhật.',
    '?ơn h?ng ch�?x�?lý' => 'đơn hàng chờ xử lý',
    'Không có ?ơn h?ng n?o ?�?test. Vui lòng tạo test data trước.' => 'Không có đơn hàng nào để test. Vui lòng tạo test data trước.',
];

// Apply replacements
foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

// Write back
file_put_contents($file, $content);

echo "Fixed encoding issues in OrderController.php\n";

