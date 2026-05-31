<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BanSeeder extends Seeder
{
    public function run(): void
    {
        $bans = [];

        for ($i = 1; $i <= 10; $i++) {
            $bans[] = [
                'ma_ban' => 'B'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'so_ban' => $i,
                'vi_tri' => $i <= 5 ? 'Tầng 1' : 'Tầng 2',
                'trang_thai' => 'trong',
                'ma_chi_nhanh' => 'CN001',
            ];
        }

        DB::table('BAN')->insertOrIgnore($bans);
    }
}
