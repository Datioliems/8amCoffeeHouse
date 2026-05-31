<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use Illuminate\Support\Facades\DB;

class BanController extends Controller
{
    public function index()
    {
        $maChiNhanh = session('ma_chi_nhanh');

        $bans = Ban::where('ma_chi_nhanh', $maChiNhanh)
            ->orderBy('so_ban')
            ->get();

        $orderCounts = DB::table('ORDERS')
            ->where('ma_chi_nhanh', $maChiNhanh)
            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan', 'dang_pha_che', 'da_phuc_vu'])
            ->select('ma_ban', DB::raw('COUNT(*) as cnt'))
            ->groupBy('ma_ban')
            ->pluck('cnt', 'ma_ban');

        return view('staff.ban-list', compact('bans', 'orderCounts'));
    }
}
