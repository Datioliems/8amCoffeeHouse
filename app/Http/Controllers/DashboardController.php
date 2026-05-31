<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $maChiNhanh = session('ma_chi_nhanh');
        $today = now()->toDateString();

        $orderHomNay = Order::where('ma_chi_nhanh', $maChiNhanh)
            ->where('ngay_order', $today)
            ->count();

        $orderChoXacNhan = Order::where('ma_chi_nhanh', $maChiNhanh)
            ->where('trang_thai', 'cho_xac_nhan')
            ->count();

        $orderDangPhaChe = Order::where('ma_chi_nhanh', $maChiNhanh)
            ->where('trang_thai', 'dang_pha_che')
            ->count();

        $doanhThuHomNay = DB::table('HOA_DON as hd')
            ->join('ORDERS as o', 'o.ma_order', '=', 'hd.ma_order')
            ->where('o.ma_chi_nhanh', $maChiNhanh)
            ->whereRaw('CAST(hd.thoi_gian_lap AS DATE) = ?', [$today])
            ->sum('hd.tong_tien_sau_ck');

        $banCoKhach = Ban::where('ma_chi_nhanh', $maChiNhanh)
            ->where('trang_thai', 'co_khach')
            ->count();

        $tongBan = Ban::where('ma_chi_nhanh', $maChiNhanh)->count();

        $orderGanDay = Order::with(['ban', 'khachHang'])
            ->where('ma_chi_nhanh', $maChiNhanh)
            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan', 'dang_pha_che'])
            ->orderByDesc('ngay_order')
            ->orderByDesc('gio_order')
            ->limit(5)
            ->get();

        $doanhThu7NgayRaw = DB::table('HOA_DON as hd')
            ->join('ORDERS as o', 'o.ma_order', '=', 'hd.ma_order')
            ->where('o.ma_chi_nhanh', $maChiNhanh)
            ->whereRaw('CAST(hd.thoi_gian_lap AS DATE) >= ?', [now()->subDays(6)->toDateString()])
            ->selectRaw('CAST(hd.thoi_gian_lap AS DATE) as ngay, SUM(hd.tong_tien_sau_ck) as doanh_thu')
            ->groupBy('ngay')
            ->pluck('doanh_thu', 'ngay');

        $doanhThu7Ngay = collect(range(6, 0))->map(function ($daysAgo) use ($doanhThu7NgayRaw) {
            $date = now()->subDays($daysAgo);
            return [
                'label' => $date->format('d/m'),
                'value' => (float) ($doanhThu7NgayRaw[$date->toDateString()] ?? 0),
            ];
        });

        $topMons = DB::table('CHI_TIET_ORDER as ct')
            ->join('ORDERS as o', 'o.ma_order', '=', 'ct.ma_order')
            ->join('MON as m', 'm.ma_mon', '=', 'ct.ma_mon')
            ->where('o.ma_chi_nhanh', $maChiNhanh)
            ->where('o.ngay_order', $today)
            ->whereNotIn('o.trang_thai', ['da_huy', 'dang_chon'])
            ->selectRaw('m.ten_mon, SUM(ct.so_luong) as so_luong')
            ->groupBy('m.ten_mon')
            ->orderByDesc('so_luong')
            ->limit(5)
            ->get();

        return view('staff.dashboard', compact(
            'orderHomNay',
            'orderChoXacNhan',
            'orderDangPhaChe',
            'doanhThuHomNay',
            'banCoKhach',
            'tongBan',
            'orderGanDay',
            'doanhThu7Ngay',
            'topMons'
        ));
    }
}
