<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function show(string $maOrder)
    {
        // Scope theo chi nhánh của staff đang đăng nhập
        $order = Order::with(['ban', 'chiTietOrders.mon', 'chiTietOrders.options', 'hoaDon'])
            ->where('ma_order', $maOrder)
            ->where('ma_chi_nhanh', (string) session('ma_chi_nhanh', ''))
            ->firstOrFail();

        // Tổng tiền đã tính gộp phụ thu options
        $tongTien = $order->chiTietOrders->sum(
            fn($i) => ($i->don_gia_tai_thoi_diem + $i->options->sum('gia_them')) * $i->so_luong
        );

        $mergeTargets = Order::with('ban')
            ->where('ma_chi_nhanh', $order->ma_chi_nhanh)
            ->where('ma_order', '<>', $order->ma_order)
            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan', 'dang_pha_che', 'da_phuc_vu'])
            ->orderByDesc('ngay_order')
            ->orderByDesc('gio_order')
            ->limit(30)
            ->get();

        return view('staff.payment', compact('order', 'tongTien', 'mergeTargets'));
    }

    public function process(Request $request, string $maOrder)
    {
        // Scope theo chi nhánh
        Order::where('ma_order', $maOrder)
            ->where('ma_chi_nhanh', (string) session('ma_chi_nhanh', ''))
            ->firstOrFail();

        $request->validate([
            'chiet_khau'     => 'required|numeric|min:0|max:100',
            'phuong_thuc_tt' => 'required|in:tien_mat,chuyen_khoan,the,vi_dien_tu,momo,vnpay',
        ]);

        $chietKhau   = (float)  $request->input('chiet_khau', 0);
        $phuongThuc  = (string) $request->input('phuong_thuc_tt', '');
        $maNv        = (string) (session('ma_nv') ?? '');

        try {
            $maHoaDon = $this->paymentService->createInvoice(
                maOrder:     $maOrder,
                chietKhau:   $chietKhau,
                phuongThuc:  $phuongThuc,
                maNvThuNgan: $maNv,
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        // Ở lại trang thanh toán để hiện kết quả + in hóa đơn (không nhảy về danh sách đơn)
        return redirect()->route('payment.show', $maOrder)
                         ->with('success', "Thanh toán thành công! Hóa đơn: {$maHoaDon}");
    }
}
