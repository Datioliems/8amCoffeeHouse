<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChiNhanhController extends Controller
{
    /** Super-admin đổi chi nhánh đang xem (cập nhật session ma_chi_nhanh). */
    public function switch(Request $request)
    {
        $request->validate([
            'ma_chi_nhanh' => 'required|exists:CHI_NHANH,ma_chi_nhanh',
        ]);

        session(['ma_chi_nhanh' => $request->ma_chi_nhanh]);

        return back()->with('success', 'Đã chuyển sang chi nhánh '.$request->ma_chi_nhanh);
    }
}
