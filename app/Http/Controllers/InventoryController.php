<?php

namespace App\Http\Controllers;

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
        $outOfStock     = $stocks->where('sl_ton_kho_he_thong', 0)->count();
        $lowStock       = $stocks->filter(fn($s) => $s->sl_ton_kho_he_thong > 0
                                && $s->sl_ton_kho_he_thong < $s->nguong_canh_bao)->count();

        return view('inventory.stock-overview', compact('items','totalMaterials','outOfStock','lowStock'));
    }

    /** Danh sách nguyên liệu sắp hết */
    public function lowStock()
    {
        $alerts = $this->inventoryService->getLowStockAlerts(session('ma_chi_nhanh'));
        return view('inventory.low-stock', compact('alerts'));
    }
}
