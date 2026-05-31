<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function show(string $maOrder)
    {
        $order = Order::with(['ban', 'chiTietOrders.mon'])->findOrFail($maOrder);
        $tongTien = $order->chiTietOrders->sum(fn($i) => $i->don_gia_tai_thoi_diem * $i->so_luong);
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
        $request->validate([
            'chiet_khau'     => 'required|numeric|min:0|max:100',
            'phuong_thuc_tt' => 'required|in:tien_mat,chuyen_khoan,the,vi_dien_tu,momo,vnpay',
        ]);

        $maHoaDon = $this->paymentService->createInvoice(
            maOrder:      $maOrder,
            chietKhau:    $request->chiet_khau,
            phuongThuc:   $request->phuong_thuc_tt,
            maNvThuNgan:  session('ma_nv'),
        );

        return redirect()->route('orders.index')
                         ->with('success', "Thanh toán thành công! Hóa đơn: {$maHoaDon}");
    }
}
