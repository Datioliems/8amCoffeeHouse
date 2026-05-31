<?php

namespace App\Http\Controllers;

use App\Models\PhieuKiemKe;
use App\Models\PhieuNhapKho;
use App\Services\InventoryService;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    /** Tổng quan tồn kho */
    public function index()
    {
        $maChiNhanh = session('ma_chi_nhanh');
        $stocks = $this->inventoryService->getStockOverview($maChiNhanh);

        $items        = $stocks->map(fn($s) => [
            'ten'    => $s->ten_nl,
            'ton'    => $s->sl_ton_kho_he_thong,
            'nguong' => $s->nguong_canh_bao,
            'don_vi' => $s->don_vi,
        ])->toArray();

        $totalMaterials = $stocks->count();
        $outOfStock     = $stocks->filter(fn($s) => $s->sl_ton_kho_he_thong <= 0)->count();
        $lowStock       = $stocks->filter(fn($s) => $s->sl_ton_kho_he_thong > 0
                                && $s->sl_ton_kho_he_thong < $s->nguong_canh_bao)->count();

        $recentImports = PhieuNhapKho::with(['nhaCungCap', 'chiTietNhapKhos'])
            ->where('ma_chi_nhanh', $maChiNhanh)
            ->orderByDesc('ngay_nk')
            ->limit(5)
            ->get();

        $recentChecks = PhieuKiemKe::with(['nhanVien', 'chiTietKiemKes'])
            ->where('ma_chi_nhanh', $maChiNhanh)
            ->orderByDesc('ngay_kk')
            ->limit(5)
            ->get();

        return view('inventory.stock-overview', compact(
            'items',
            'totalMaterials',
            'outOfStock',
            'lowStock',
            'recentImports',
            'recentChecks',
        ));
    }

    /** Danh sách nguyên liệu sắp hết */
    public function lowStock()
    {
        $alerts = $this->inventoryService->getLowStockAlerts(session('ma_chi_nhanh'));
        return view('inventory.low-stock', compact('alerts'));
    }
}
