<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('tai_khoan_id')) {
            return redirect()->route('orders.index');
        }
        return view('staff.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'ten_tk'   => 'required|string',
            'mat_khau' => 'required|string',
        ], [
            'ten_tk.required'   => 'Vui lòng nhập tên đăng nhập.',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $taiKhoan = DB::table('TAI_KHOAN')
            ->join('NHAN_VIEN', 'TAI_KHOAN.ma_nv', '=', 'NHAN_VIEN.ma_nv')
            ->where('TAI_KHOAN.ten_tk', $request->ten_tk)
            ->where('TAI_KHOAN.trang_thai', 'active')
            ->select(
                'TAI_KHOAN.ma_tai_khoan', 'TAI_KHOAN.mat_khau',
                'TAI_KHOAN.chuc_vu',      'TAI_KHOAN.ma_nv',
                'NHAN_VIEN.ten_nv',       'NHAN_VIEN.ma_chi_nhanh'
            )
            ->first();

        if (!$taiKhoan || !Hash::check($request->mat_khau, $taiKhoan->mat_khau)) {
            return back()
                ->withErrors(['ten_tk' => 'Tên đăng nhập hoặc mật khẩu không đúng.'])
                ->withInput($request->only('ten_tk'));
        }

        $request->session()->regenerate();

        // Dùng put() từng key để tránh bug session array assignment
        $request->session()->put('tai_khoan_id', $taiKhoan->ma_tai_khoan);
        $request->session()->put('ma_nv',        $taiKhoan->ma_nv);
        $request->session()->put('ten_nv',        $taiKhoan->ten_nv);
        $request->session()->put('chuc_vu',       $taiKhoan->chuc_vu);
        $request->session()->put('ma_chi_nhanh',  $taiKhoan->ma_chi_nhanh);

        // Lọc bỏ URL .html cũ trong intended
        $intendedUrl = $request->session()->pull('url.intended');
        $safeUrl = ($intendedUrl && !str_ends_with($intendedUrl, '.html'))
            ? $intendedUrl
            : route('orders.index');

        return redirect($safeUrl);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->regenerate();
        return redirect()->route('login');
    }
}
