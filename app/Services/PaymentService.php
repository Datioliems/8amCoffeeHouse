<?php

namespace App\Services;

use App\Models\HoaDon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function createInvoice(string $maOrder, float $chietKhau, string $phuongThuc, string $maNvThuNgan): string
    {
        return DB::transaction(function () use ($maOrder, $chietKhau, $phuongThuc, $maNvThuNgan) {
            $order = Order::with('chiTietOrders')->findOrFail($maOrder);

            $tongTruoc = $order->chiTietOrders->sum(fn($i) => $i->don_gia_tai_thoi_diem * $i->so_luong);
            $tongSau   = $tongTruoc * (1 - $chietKhau / 100);

            $maHoaDon = 'HD' . now()->format('YmdHis');

            HoaDon::create([
                'ma_hoa_don'          => $maHoaDon,
                'ma_order'            => $maOrder,
                'ma_kh'               => $order->ma_kh,
                'tong_tien_truoc_ck'  => $tongTruoc,
                'chiet_khau'          => $chietKhau,
                'tong_tien_sau_ck'    => $tongSau,
                'phuong_thuc_tt'      => $phuongThuc,
                'trang_thai'          => 'da_thanh_toan',
                'ma_nv_thu_ngan'      => $maNvThuNgan,
            ]);

            $order->update(['trang_thai' => 'hoan_thanh']);
            // TRG_ORDER_CAP_NHAT_TRANG_THAI_BAN chạy tự động

            return $maHoaDon;
        });
    }
}
