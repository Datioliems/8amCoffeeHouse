<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Trang phân tích AI: dự báo doanh thu + luật gợi ý món mua kèm.
 */
class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analytics) {}

    public function index(Request $request)
    {
        // Superadmin xem toàn hệ thống; admin/nhân viên xem theo chi nhánh.
        $maChiNhanh = session('chuc_vu') === 'superadmin' ? null : session('ma_chi_nhanh');

        $forecast = $this->analytics->revenueForecast($maChiNhanh, lookback: 30, forecast: 7);

        // Luật kết hợp + map tên món để hiển thị.
        ['rules' => $rules, 'total' => $totalDon] = $this->analytics->associationRules($maChiNhanh, minSupport: 2);
        $rules = array_slice($rules, 0, 20);

        $maMons = collect($rules)->flatMap(fn($r) => [$r['a'], $r['b']])->unique()->all();
        $tenMon = DB::table('MON')->whereIn('ma_mon', $maMons ?: ['__none__'])->pluck('ten_mon', 'ma_mon');

        return view('staff.analytics', compact('forecast', 'rules', 'tenMon', 'totalDon'));
    }
}
