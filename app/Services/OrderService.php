<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderLog;
use App\Models\ChiTietOrder;
use App\Models\ChiTietOrderOption;
use App\Models\KhachHang;
use App\Models\Mon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            $maOrder = $this->generateUniqueOrderId();

            Order::create([
                'ma_order'     => $maOrder,
                'ma_ban'       => $maBan,
                'ma_kh'        => $khachHang->ma_kh,
                'ma_chi_nhanh' => $maChiNhanh,
                'trang_thai'   => 'dang_chon',
                'ngay_order'   => now()->toDateString(),
                'gio_order'    => now()->toTimeString(),
            ]);

            $this->log($maOrder, 'tao_don_nhap', null, 'dang_chon', 'Khach hang bat dau chon mon tu QR.', [
                'ma_ban' => $maBan,
                'ma_kh' => $khachHang->ma_kh,
                'ma_chi_nhanh' => $maChiNhanh,
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
            $this->log($maOrder, 'xac_nhan_don', 'cho_xac_nhan', 'da_xac_nhan', 'Nhan vien xac nhan don hang.', maNv: session('ma_nv'));
        });
    }

    public function createTakeawayOrder(string $tenKh, ?string $sdtKh, string $maChiNhanh, array $items): string
    {
        return DB::transaction(function () use ($tenKh, $sdtKh, $maChiNhanh, $items) {
            $khachHang = null;
            if ($sdtKh) {
                $khachHang = KhachHang::where('sdt', $sdtKh)->first();
            }

            if (!$khachHang) {
                $maKh = 'KH' . str_pad(KhachHang::count() + 1, 6, '0', STR_PAD_LEFT);
                $khachHang = KhachHang::create([
                    'ma_kh' => $maKh,
                    'ten_kh' => $tenKh ?: 'Khách mang về',
                    'sdt' => $sdtKh,
                ]);
            }

            $maOrder = $this->generateUniqueOrderId();

            Order::create([
                'ma_order' => $maOrder,
                'ma_ban' => null,
                'ma_kh' => $khachHang->ma_kh,
                'ma_chi_nhanh' => $maChiNhanh,
                'trang_thai' => 'cho_xac_nhan',
                'ngay_order' => now()->toDateString(),
                'gio_order' => now()->toTimeString(),
                'ghi_chu' => 'Đơn mang về',
            ]);

            foreach ($items as $item) {
                $quantity = (int) ($item['so_luong'] ?? 0);
                if ($quantity <= 0 || empty($item['ma_mon'])) {
                    continue;
                }

                $mon = Mon::where('ma_mon', $item['ma_mon'])
                    ->where('trang_thai', 'active')
                    ->firstOrFail();

                ChiTietOrder::create([
                    'ma_order' => $maOrder,
                    'ma_mon' => $mon->ma_mon,
                    'so_luong' => $quantity,
                    'don_gia_tai_thoi_diem' => $mon->don_gia,
                    'ghi_chu' => $item['ghi_chu'] ?? null,
                ]);
            }

            $this->log($maOrder, 'tao_don_mang_ve', null, 'cho_xac_nhan', 'Nhan vien tao don mua mang ve.', [
                'ma_kh' => $khachHang->ma_kh,
                'so_dong_mon' => count($items),
            ]);

            return $maOrder;
        });
    }

    public function submitByCustomer(string $maOrder): void
    {
        DB::transaction(function () use ($maOrder) {
            $order = Order::where('ma_order', $maOrder)
                          ->where('trang_thai', 'dang_chon')
                          ->lockForUpdate()
                          ->firstOrFail();

            $order->update(['trang_thai' => 'cho_xac_nhan']);
            $this->log($maOrder, 'khach_gui_don', 'dang_chon', 'cho_xac_nhan', 'Khach hang gui don cho quan.');
        });
    }

    public function updateStatus(string $maOrder, string $trangThai): void
    {
        DB::transaction(function () use ($maOrder, $trangThai) {
            $order = Order::where('ma_order', $maOrder)->lockForUpdate()->firstOrFail();
            $oldStatus = $order->trang_thai;
            $order->update(['trang_thai' => $trangThai]);
            $this->log($maOrder, 'cap_nhat_trang_thai', $oldStatus, $trangThai, 'Cap nhat trang thai don hang.', maNv: session('ma_nv'));
        });
    }

    public function addItem(string $maOrder, string $maMon, int $soLuong, ?string $ghiChu = null, array $options = []): void
    {
        DB::transaction(function () use ($maOrder, $maMon, $soLuong, $ghiChu, $options) {
            Order::where('ma_order', $maOrder)
                ->where('trang_thai', 'dang_chon')
                ->lockForUpdate()
                ->firstOrFail();

            $existing = ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)->lockForUpdate()->first();
            $mon = Mon::findOrFail($maMon);

            if ($mon->trang_thai !== 'active') {
                throw ValidationException::withMessages([
                    'ma_mon' => 'Món này hiện không thể đặt.',
                ]);
            }

            if ($existing) {
                ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)
                    ->increment('so_luong', $soLuong);
                if ($ghiChu) {
                    $notes = trim(implode("\n", array_filter([$existing->ghi_chu, $ghiChu])));
                    ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)
                        ->update(['ghi_chu' => mb_substr($notes, 0, 200)]);
                }
                $action = 'tang_so_luong_mon';
            } else {
                ChiTietOrder::create([
                    'ma_order'              => $maOrder,
                    'ma_mon'                => $maMon,
                    'so_luong'              => $soLuong,
                    'don_gia_tai_thoi_diem' => $mon->don_gia,
                    'ghi_chu'               => $ghiChu,
                ]);
                $action = 'them_mon';
            }

            $this->syncOrderOptions($maOrder, $maMon, $options);
            $this->log($maOrder, $action, data: [
                'ma_mon' => $maMon,
                'so_luong' => $soLuong,
                'ghi_chu' => $ghiChu,
                'options' => $options,
            ]);
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
        DB::transaction(function () use ($maOrder, $maMon) {
            $item = ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)->lockForUpdate()->first();
            ChiTietOrder::where('ma_order', $maOrder)->where('ma_mon', $maMon)->delete();
            $this->log($maOrder, 'xoa_mon', data: [
                'ma_mon' => $maMon,
                'so_luong' => $item?->so_luong,
            ]);
        });
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

            $target = Order::where('ma_order', $maOrderTarget)->lockForUpdate()->firstOrFail();
            $oldStatus = $target->trang_thai;
            $target->update(['trang_thai' => 'da_huy']);
            $this->log($maOrderGoc, 'gop_don_nhan', data: ['ma_order_gop' => $maOrderTarget]);
            $this->log($maOrderTarget, 'gop_don_huy', $oldStatus, 'da_huy', 'Don duoc gop vao don khac.', [
                'ma_order_nhan' => $maOrderGoc,
            ]);
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
            $maOrderMoi = $this->generateUniqueOrderId();

            Order::create([
                'ma_order'     => $maOrderMoi,
                'ma_ban'       => $orderGoc->ma_ban,
                'ma_kh'        => $orderGoc->ma_kh,
                'ma_chi_nhanh' => $orderGoc->ma_chi_nhanh,
                'trang_thai'   => $orderGoc->trang_thai,
                'ngay_order'   => now()->toDateString(),
                'gio_order'    => now()->toTimeString(),
            ]);

            ChiTietOrder::create([
                'ma_order'              => $maOrderMoi,
                'ma_mon'                => $maMon,
                'so_luong'              => $soLuongTach,
                'don_gia_tai_thoi_diem' => $itemGoc->don_gia_tai_thoi_diem,
                'ghi_chu'               => $itemGoc->ghi_chu,
            ]);

            $options = ChiTietOrderOption::where('ma_order', $maOrderGoc)
                ->where('ma_mon', $maMon)
                ->get(['loai_option', 'ten_lua_chon', 'gia_them']);

            foreach ($options as $option) {
                ChiTietOrderOption::create([
                    'ma_order' => $maOrderMoi,
                    'ma_mon' => $maMon,
                    'loai_option' => $option->loai_option,
                    'ten_lua_chon' => $option->ten_lua_chon,
                    'gia_them' => $option->gia_them,
                ]);
            }

            ChiTietOrder::where('ma_order', $maOrderGoc)->where('ma_mon', $maMon)
                ->decrement('so_luong', $soLuongTach);

            $this->log($maOrderGoc, 'tach_don_goc', data: [
                'ma_order_moi' => $maOrderMoi,
                'ma_mon' => $maMon,
                'so_luong_tach' => $soLuongTach,
            ]);
            $this->log($maOrderMoi, 'tach_don_moi', null, $orderGoc->trang_thai, 'Tao don moi tu thao tac tach don.', [
                'ma_order_goc' => $maOrderGoc,
                'ma_mon' => $maMon,
                'so_luong_tach' => $soLuongTach,
            ]);

            return $maOrderMoi;
        });
    }

    public function countByStatus(string $maChiNhanh): array
    {
        return Order::where('ma_chi_nhanh', $maChiNhanh)
            ->whereIn('trang_thai', ['cho_xac_nhan','da_xac_nhan','dang_pha_che','da_phuc_vu','hoan_thanh'])
            ->selectRaw('trang_thai, COUNT(*) as cnt')
            ->groupBy('trang_thai')
            ->pluck('cnt', 'trang_thai')
            ->toArray();
    }

    public function log(
        string $maOrder,
        string $hanhDong,
        ?string $trangThaiCu = null,
        ?string $trangThaiMoi = null,
        ?string $noiDung = null,
        ?array $data = null,
        ?string $maNv = null
    ): void {
        OrderLog::create([
            'ma_order' => $maOrder,
            'hanh_dong' => $hanhDong,
            'trang_thai_cu' => $trangThaiCu,
            'trang_thai_moi' => $trangThaiMoi,
            'noi_dung' => $noiDung,
            'du_lieu' => $data,
            'ma_nv' => $maNv ?? session('ma_nv'),
            'created_at' => now(),
        ]);
    }

    private function generateUniqueOrderId(): string
    {
        do {
            $maOrder = 'ORD' . now()->format('ymdHis') . random_int(10, 99);
        } while (Order::whereKey($maOrder)->exists());

        return $maOrder;
    }
}
