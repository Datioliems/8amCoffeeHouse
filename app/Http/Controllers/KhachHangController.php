<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class KhachHangController extends Controller
{
    /** Danh sách khách hàng — chỉ những khách có giao dịch (hóa đơn). */
    public function index()
    {
        $isSuper    = session('chuc_vu') === 'superadmin';
        $maChiNhanh = (string) session('ma_chi_nhanh', '');

        $khachHangs = DB::table('KHACH_HANG as kh')
            ->join('HOA_DON as hd', 'hd.ma_kh', '=', 'kh.ma_kh')
            ->join('ORDERS as o', 'o.ma_order', '=', 'hd.ma_order')
            ->when(! $isSuper, fn($q) => $q->where('o.ma_chi_nhanh', $maChiNhanh))
            ->select(
                'kh.ma_kh', 'kh.ten_kh', 'kh.sdt',
                DB::raw('COUNT(DISTINCT hd.ma_hoa_don) as so_don'),
                DB::raw('SUM(hd.tong_tien_sau_ck) as tong_chi_tieu'),
                DB::raw('MAX(hd.thoi_gian_lap) as lan_cuoi')
            )
            ->groupBy('kh.ma_kh', 'kh.ten_kh', 'kh.sdt')
            ->orderByDesc('tong_chi_tieu')
            ->paginate(50);

        return view('staff.khachhang-list', compact('khachHangs', 'isSuper'));
    }
}
