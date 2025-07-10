<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "📊 THỐNG KÊ RETURN ORDERS\n";
echo "===============================\n\n";

// Basic statistics
$totalReturnOrders = DB::table('return_orders')->count();
$totalReturnItems = DB::table('return_order_items')->count();

echo "Tổng đơn trả hàng: {$totalReturnOrders}\n";
echo "Tổng sản phẩm trả: {$totalReturnItems}\n\n";

// Status statistics
echo "📈 THỐNG KÊ THEO TRẠNG THÁI:\n";
$statusStats = DB::table('return_orders')
    ->select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

foreach ($statusStats as $stat) {
    $percentage = round(($stat->count / $totalReturnOrders) * 100, 1);
    echo "{$stat->status}: {$stat->count} ({$percentage}%)\n";
}

echo "\n📋 THỐNG KÊ THEO LÝ DO:\n";
$reasonStats = DB::table('return_orders')
    ->select('reason', DB::raw('count(*) as count'))
    ->groupBy('reason')
    ->get();

foreach ($reasonStats as $stat) {
    $percentage = round(($stat->count / $totalReturnOrders) * 100, 1);
    echo "{$stat->reason}: {$stat->count} ({$percentage}%)\n";
}

echo "\n💰 THỐNG KÊ THEO PHƯƠNG THỨC HOÀN TIỀN:\n";
$refundStats = DB::table('return_orders')
    ->select('refund_method', DB::raw('count(*) as count'))
    ->groupBy('refund_method')
    ->get();

foreach ($refundStats as $stat) {
    $percentage = round(($stat->count / $totalReturnOrders) * 100, 1);
    echo "{$stat->refund_method}: {$stat->count} ({$percentage}%)\n";
}

echo "\n📅 THỐNG KÊ THEO NGÀY (10 ngày gần nhất):\n";
$dailyStats = DB::table('return_orders')
    ->select(DB::raw('DATE(return_date) as date'), DB::raw('count(*) as count'))
    ->where('return_date', '>=', Carbon::now()->subDays(10))
    ->groupBy(DB::raw('DATE(return_date)'))
    ->orderBy('date', 'desc')
    ->get();

foreach ($dailyStats as $stat) {
    echo "{$stat->date}: {$stat->count} đơn\n";
}

echo "\n💵 THỐNG KÊ GIÁ TRỊ:\n";
$valueStats = DB::table('return_orders')
    ->selectRaw('
        MIN(total_amount) as min_amount,
        MAX(total_amount) as max_amount,
        AVG(total_amount) as avg_amount,
        SUM(total_amount) as total_amount
    ')
    ->first();

echo "Giá trị nhỏ nhất: " . number_format($valueStats->min_amount) . "₫\n";
echo "Giá trị lớn nhất: " . number_format($valueStats->max_amount) . "₫\n";
echo "Giá trị trung bình: " . number_format($valueStats->avg_amount) . "₫\n";
echo "Tổng giá trị trả: " . number_format($valueStats->total_amount) . "₫\n";

echo "\n🔍 MẪU DỮ LIỆU (5 đơn gần nhất):\n";
$sampleData = DB::table('return_orders')
    ->join('invoices', 'return_orders.invoice_id', '=', 'invoices.id')
    ->leftJoin('customers', 'return_orders.customer_id', '=', 'customers.id')
    ->select(
        'return_orders.return_number',
        'invoices.invoice_number',
        'customers.name as customer_name',
        'return_orders.return_date',
        'return_orders.reason',
        'return_orders.status',
        'return_orders.total_amount',
        'return_orders.refund_method'
    )
    ->orderBy('return_orders.created_at', 'desc')
    ->limit(5)
    ->get();

foreach ($sampleData as $data) {
    echo "- {$data->return_number} | {$data->invoice_number} | " . 
         ($data->customer_name ?: 'Khách lẻ') . " | {$data->return_date} | " .
         "{$data->reason} | {$data->status} | " . number_format($data->total_amount) . "₫\n";
}

echo "\n✅ Kiểm tra hoàn tất!\n";
