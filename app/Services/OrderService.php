<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ChiTietOrder;
use App\Models\ChiTietOrderOption;
use App\Models\KhachHang;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(string $maBan, string $tenKh, ?string $sdtKh, string $maChiNhanh): array
    {
        return DB::transaction(function () use ($maBan, $tenKh, $sdtKh, $maChiNhanh) {
            $khachHang = null;
            if ($sdtKh) {
                $khachHang = KhachHang::where('sdt', $sdtKh)->first();
            }
            if (!$khachHang) {
                $maKh = 'KH' . str_pad(KhachHang::count() + 1, 6, '0', STR_PAD_LEFT);
                $khachHang = KhachHang::create([
                    'ma_kh'  => $maKh,
                    'ten_kh' => $tenKh,
                    'sdt'    => $sdtKh,
                ]);
            }

            // FIX: ORD(3) + ymdHi(10) + ss(2) + XX(2) = 17 ký tự — luôn ≤ 20
            $maOrder = 'ORD'
                . now()->format('ymdHi')
                . str_pad(now()->second, 2, '0', STR_PAD_LEFT)
                . rand(10, 99);

            Order::create([
                'ma_order'     => $maOrder,
                'ma_ban'       => $maBan,
                'ma_kh'        => $khachHang->ma_kh,
                'ma_chi_nhanh' => $maChiNhanh,
                'trang_thai'   => 'cho_xac_nhan',
                'ngay_order'   => now()->toDateString(),
                'gio_order'    => now()->toTimeString(),
            ]);

            return ['ma_order' => $maOrder, 'ma_kh' => $khachHang->ma_kh];
        });
    }

    public function confirm(string $maOrder): void
    {
        DB::transaction(function () use ($maOrder) {
            $order = Order::where('ma_order', $maOrder)
                          ->where('trang_thai', 'cho_xac_nhan')
                          ->lockForUpdate()
                          ->firstOrFail();
            $order->update(['trang_thai' => 'da_xac_nhan']);
        });
    }

    public function updateStatus(string $maOrder, string $trangThai): void
    {
        Order::where('ma_order', $maOrder)->update(['trang_thai' => $trangThai]);
    }

    public function addItem(string $maOrder, string $maMon, int $soLuong, ?string $ghiChu = null, array $options = []): void
    {
        DB::transaction(function () use ($maOrder, $maMon, $soLuong, $ghiChu, $options) {
            $existing = ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)->lockForUpdate()->first();
            if ($existing) {
                ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)
                    ->increment('so_luong', $soLuong);
                if ($ghiChu) {
                    $notes = trim(implode("\n", array_filter([$existing->ghi_chu, $ghiChu])));
                    ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)
                        ->update(['ghi_chu' => mb_substr($notes, 0, 200)]);
                }
            } else {
                $mon = \App\Models\Mon::findOrFail($maMon);
                ChiTietOrder::create([
                    'ma_order'              => $maOrder,
                    'ma_mon'                => $maMon,
                    'so_luong'              => $soLuong,
                    'don_gia_tai_thoi_diem' => $mon->don_gia,
                    'ghi_chu'               => $ghiChu,
                ]);
            }

            $this->syncOrderOptions($maOrder, $maMon, $options);
        });
    }

    private function syncOrderOptions(string $maOrder, string $maMon, array $options): void
    {
        if (empty($options)) {
            return;
        }

        ChiTietOrderOption::where('ma_order', $maOrder)->where('ma_mon', $maMon)->delete();

        foreach ($options as $option) {
            ChiTietOrderOption::create([
                'ma_order' => $maOrder,
                'ma_mon' => $maMon,
                'loai_option' => (string) ($option['type'] ?? 'custom'),
                'ten_lua_chon' => (string) ($option['value'] ?? ''),
                'gia_them' => (int) ($option['price'] ?? 0),
            ]);
        }
    }

    public function removeItem(string $maOrder, string $maMon): void
    {
        ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)->delete();
    }

    public function merge(string $maOrderGoc, string $maOrderTarget): void
    {
        DB::transaction(function () use ($maOrderGoc, $maOrderTarget) {
            $targetItems = ChiTietOrder::where('ma_order', $maOrderTarget)
                ->lockForUpdate()->get();

            foreach ($targetItems as $item) {
                $existing = ChiTietOrder::where('ma_order', $maOrderGoc)
                    ->where('ma_mon', $item->ma_mon)->lockForUpdate()->first();

                if ($existing) {
                    ChiTietOrder::where('ma_order', $maOrderGoc)->where('ma_mon', $item->ma_mon)
                        ->increment('so_luong', $item->so_luong);
                    ChiTietOrder::where('ma_order', $maOrderTarget)->where('ma_mon', $item->ma_mon)
                        ->delete();
                } else {
                    ChiTietOrder::where('ma_order', $maOrderTarget)->where('ma_mon', $item->ma_mon)
                        ->update(['ma_order' => $maOrderGoc]);
                }
            }

            Order::where('ma_order', $maOrderTarget)->update(['trang_thai' => 'da_huy']);
        });
    }

    public function split(string $maOrderGoc, string $maMon, int $soLuongTach): string
    {
        return DB::transaction(function () use ($maOrderGoc, $maMon, $soLuongTach) {
            $itemGoc = ChiTietOrder::where('ma_order', $maOrderGoc)->where('ma_mon', $maMon)
                ->lockForUpdate()->firstOrFail();

            if ($soLuongTach >= $itemGoc->so_luong) {
                throw new \InvalidArgumentException('Số lượng tách phải nhỏ hơn số lượng hiện tại.');
            }

            $orderGoc   = Order::findOrFail($maOrderGoc);
            $maOrderMoi = 'ORD'
                . now()->format('ymdHi')
                . str_pad(now()->second, 2, '0', STR_PAD_LEFT)
                . rand(10, 99);

            Order::create([
                'ma_order'     => $maOrderMoi,
                'ma_ban'       => $orderGoc->ma_ban,
                'ma_kh'        => $orderGoc->ma_kh,
                'ma_chi_nhanh' => $orderGoc->ma_chi_nhanh,
                'trang_thai'   => 'cho_xac_nhan',
                'ngay_order'   => now()->toDateString(),
                'gio_order'    => now()->toTimeString(),
            ]);

            ChiTietOrder::create([
                'ma_order'              => $maOrderMoi,
                'ma_mon'                => $maMon,
                'so_luong'              => $soLuongTach,
                'don_gia_tai_thoi_diem' => $itemGoc->don_gia_tai_thoi_diem,
            ]);

            ChiTietOrder::where('ma_order', $maOrderGoc)->where('ma_mon', $maMon)
                ->decrement('so_luong', $soLuongTach);

            return $maOrderMoi;
        });
    }

    public function countByStatus(string $maChiNhanh): array
    {
        return Order::where('ma_chi_nhanh', $maChiNhanh)
            ->whereIn('trang_thai', ['cho_xac_nhan','da_xac_nhan','dang_pha_che','da_phuc_vu'])
            ->selectRaw('trang_thai, COUNT(*) as cnt')
            ->groupBy('trang_thai')
            ->pluck('cnt', 'trang_thai')
            ->toArray();
    }
}
