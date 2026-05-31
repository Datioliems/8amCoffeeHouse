<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DinhMucSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('DINH_MUC')->insertOrIgnore([
            ['ma_mon' => 'MON001', 'ma_nl' => 'NL001', 'so_luong_dung' => 18.00, 'mo_ta' => '2 shot espresso Arabica'],
            ['ma_mon' => 'MON001', 'ma_nl' => 'NL005', 'so_luong_dung' => 30.00, 'mo_ta' => 'Nước pha espresso'],
            ['ma_mon' => 'MON002', 'ma_nl' => 'NL001', 'so_luong_dung' => 18.00, 'mo_ta' => '2 shot espresso'],
            ['ma_mon' => 'MON002', 'ma_nl' => 'NL005', 'so_luong_dung' => 150.00, 'mo_ta' => 'Nước nóng pha loãng'],
            ['ma_mon' => 'MON003', 'ma_nl' => 'NL001', 'so_luong_dung' => 18.00, 'mo_ta' => '1 shot espresso'],
            ['ma_mon' => 'MON003', 'ma_nl' => 'NL003', 'so_luong_dung' => 180.00, 'mo_ta' => 'Sữa tươi steam'],
            ['ma_mon' => 'MON004', 'ma_nl' => 'NL001', 'so_luong_dung' => 18.00, 'mo_ta' => '1 shot espresso'],
            ['ma_mon' => 'MON004', 'ma_nl' => 'NL003', 'so_luong_dung' => 120.00, 'mo_ta' => 'Sữa tươi'],
            ['ma_mon' => 'MON004', 'ma_nl' => 'NL004', 'so_luong_dung' => 20.00, 'mo_ta' => 'Sữa đặc'],
            ['ma_mon' => 'MON004', 'ma_nl' => 'NL006', 'so_luong_dung' => 30.00, 'mo_ta' => 'Kem béo'],
            ['ma_mon' => 'MON005', 'ma_nl' => 'NL001', 'so_luong_dung' => 18.00, 'mo_ta' => '1 shot espresso'],
            ['ma_mon' => 'MON005', 'ma_nl' => 'NL003', 'so_luong_dung' => 150.00, 'mo_ta' => 'Sữa tươi'],
            ['ma_mon' => 'MON005', 'ma_nl' => 'NL006', 'so_luong_dung' => 30.00, 'mo_ta' => 'Kem topping'],
        ]);
    }
}
