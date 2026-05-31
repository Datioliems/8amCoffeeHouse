<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed dữ liệu từ file SQL Server đã có sẵn.
     * Chạy: php artisan db:seed
     * 
     * Lưu ý: Vì database đã được tạo bằng file 8am_coffee_database.sql
     * (chạy trực tiếp trên SQL Server), seeder này chỉ kiểm tra
     * và bỏ qua nếu dữ liệu đã tồn tại.
     */
    public function run(): void
    {
        // Kiểm tra đã seed chưa
        if (DB::table('CHI_NHANH')->count() > 0) {
            $this->command->info('Database đã có dữ liệu. Bỏ qua seeder.');
            return;
        }

        $this->command->info('Chạy seeders...');
        $this->call([
            ChiNhanhSeeder::class,
            NhanVienSeeder::class,
            TaiKhoanSeeder::class,
            DanhMucSeeder::class,
            MonSeeder::class,
            NguyenLieuSeeder::class,
            BanSeeder::class,
            NhaCungCapSeeder::class,
            DinhMucSeeder::class,
            TonKhoSeeder::class,
            MenuOptionSeeder::class,
        ]);
        $this->command->info('✅ Seed hoàn tất!');
    }
}
