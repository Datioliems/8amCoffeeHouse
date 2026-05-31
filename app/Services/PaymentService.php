<?php

namespace App\Services;

use App\Models\HoaDon;
use App\Models\Order;
use App\Models\OrderLog;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function createInvoice(string $maOrder, float $chietKhau, string $phuongThuc, string $maNvThuNgan): string
    {
        return DB::transaction(function () use ($maOrder, $chietKhau, $phuongThuc, $maNvThuNgan) {
            $order = Order::with(['chiTietOrders', 'hoaDon'])
                ->where('ma_order', $maOrder)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->hoaDon) {
                return $order->hoaDon->ma_hoa_don;
            }

            $tongTruoc = $order->chiTietOrders->sum(fn($i) => $i->don_gia_tai_thoi_diem * $i->so_luong);
            $tongSau   = $tongTruoc * (1 - $chietKhau / 100);

            do {
                $maHoaDon = 'HD' . now()->format('YmdHis') . random_int(1000, 9999);
            } while (HoaDon::whereKey($maHoaDon)->exists());

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

            $oldStatus = $order->trang_thai;
            $order->update(['trang_thai' => 'hoan_thanh']);
            OrderLog::create([
                'ma_order' => $maOrder,
                'hanh_dong' => 'thanh_toan',
                'trang_thai_cu' => $oldStatus,
                'trang_thai_moi' => 'hoan_thanh',
                'noi_dung' => 'Don hang da thanh toan va tao hoa don.',
                'du_lieu' => [
                    'ma_hoa_don' => $maHoaDon,
                    'tong_tien_truoc_ck' => $tongTruoc,
                    'chiet_khau' => $chietKhau,
                    'tong_tien_sau_ck' => $tongSau,
                    'phuong_thuc_tt' => $phuongThuc,
                ],
                'ma_nv' => $maNvThuNgan,
                'created_at' => now(),
            ]);
            // TRG_ORDER_CAP_NHAT_TRANG_THAI_BAN chạy tự động

            return $maHoaDon;
        });
    }
}
