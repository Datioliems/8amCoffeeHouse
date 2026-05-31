<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE ORDERS DROP FOREIGN KEY fk_orders_ban');
        DB::statement('ALTER TABLE ORDERS MODIFY ma_ban VARCHAR(10) NULL');
        DB::statement('ALTER TABLE ORDERS ADD CONSTRAINT fk_orders_ban FOREIGN KEY (ma_ban) REFERENCES BAN(ma_ban) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ORDERS DROP FOREIGN KEY fk_orders_ban');
        DB::statement('ALTER TABLE ORDERS MODIFY ma_ban VARCHAR(10) NOT NULL');
        DB::statement('ALTER TABLE ORDERS ADD CONSTRAINT fk_orders_ban FOREIGN KEY (ma_ban) REFERENCES BAN(ma_ban)');
    }
};
