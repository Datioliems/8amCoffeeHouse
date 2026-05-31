<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TonKhoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('TON_KHO')->insertOrIgnore([
            ['ma_chi_nhanh' => 'CN001', 'ma_nl' => 'NL001', 'sl_ton_kho_he_thong' => 5000.00, 'sl_ton_kho_thuc_te' => 5000.00, 'nguong_canh_bao' => 500.00, 'hao_hut_cost' => 0],
            ['ma_chi_nhanh' => 'CN001', 'ma_nl' => 'NL002', 'sl_ton_kho_he_thong' => 3000.00, 'sl_ton_kho_thuc_te' => 3000.00, 'nguong_canh_bao' => 300.00, 'hao_hut_cost' => 0],
            ['ma_chi_nhanh' => 'CN001', 'ma_nl' => 'NL003', 'sl_ton_kho_he_thong' => 15000.00, 'sl_ton_kho_thuc_te' => 15000.00, 'nguong_canh_bao' => 1500.00, 'hao_hut_cost' => 0],
            ['ma_chi_nhanh' => 'CN001', 'ma_nl' => 'NL004', 'sl_ton_kho_he_thong' => 5000.00, 'sl_ton_kho_thuc_te' => 5000.00, 'nguong_canh_bao' => 500.00, 'hao_hut_cost' => 0],
            ['ma_chi_nhanh' => 'CN001', 'ma_nl' => 'NL005', 'sl_ton_kho_he_thong' => 50000.00, 'sl_ton_kho_thuc_te' => 50000.00, 'nguong_canh_bao' => 5000.00, 'hao_hut_cost' => 0],
            ['ma_chi_nhanh' => 'CN001', 'ma_nl' => 'NL006', 'sl_ton_kho_he_thong' => 3000.00, 'sl_ton_kho_thuc_te' => 3000.00, 'nguong_canh_bao' => 300.00, 'hao_hut_cost' => 0],
        ]);
    }
}
