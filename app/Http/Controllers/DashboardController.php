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

        return view('staff.dashboard', compact(
            'orderHomNay',
            'orderChoXacNhan',
            'orderDangPhaChe',
            'doanhThuHomNay',
            'banCoKhach',
            'tongBan',
            'orderGanDay'
        ));
    }
}
