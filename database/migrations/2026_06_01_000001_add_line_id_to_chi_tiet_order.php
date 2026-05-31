<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop composite FK from CHI_TIET_ORDER_OPTION
        DB::unprepared('ALTER TABLE `CHI_TIET_ORDER_OPTION` DROP FOREIGN KEY `fk_ctoo_chi_tiet_order`');

        // 2. Replace composite PK on CHI_TIET_ORDER with auto-increment id
        DB::unprepared('
            ALTER TABLE `CHI_TIET_ORDER`
                DROP PRIMARY KEY,
                ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
                ADD PRIMARY KEY (`id`),
                ADD INDEX `idx_cto_order_mon` (`ma_order`, `ma_mon`)
        ');

        // 3. Add chi_tiet_id to CHI_TIET_ORDER_OPTION
        DB::unprepared('ALTER TABLE `CHI_TIET_ORDER_OPTION` ADD COLUMN `chi_tiet_id` BIGINT UNSIGNED NULL AFTER `id`');

        // 4. Populate chi_tiet_id for existing data
        DB::unprepared('
            UPDATE `CHI_TIET_ORDER_OPTION` opt
            JOIN `CHI_TIET_ORDER` cto
                ON cto.ma_order = opt.ma_order AND cto.ma_mon = opt.ma_mon
            SET opt.chi_tiet_id = cto.id
        ');

        // 5. Make NOT NULL and add new FK on id
        DB::unprepared('ALTER TABLE `CHI_TIET_ORDER_OPTION` MODIFY COLUMN `chi_tiet_id` BIGINT UNSIGNED NOT NULL');

        DB::unprepared('
            ALTER TABLE `CHI_TIET_ORDER_OPTION`
                ADD CONSTRAINT `chi_tiet_order_option_chi_tiet_id_foreign`
                FOREIGN KEY (`chi_tiet_id`) REFERENCES `CHI_TIET_ORDER`(`id`)
                ON UPDATE CASCADE ON DELETE CASCADE
        ');
    }

    public function down(): void
    {
        DB::unprepared('ALTER TABLE `CHI_TIET_ORDER_OPTION` DROP FOREIGN KEY `chi_tiet_order_option_chi_tiet_id_foreign`');
        DB::unprepared('ALTER TABLE `CHI_TIET_ORDER_OPTION` DROP COLUMN `chi_tiet_id`');

        DB::unprepared('ALTER TABLE `CHI_TIET_ORDER` DROP INDEX `idx_cto_order_mon`');
        DB::unprepared('ALTER TABLE `CHI_TIET_ORDER` DROP PRIMARY KEY, DROP COLUMN `id`, ADD PRIMARY KEY (`ma_order`, `ma_mon`)');

        DB::unprepared('
            ALTER TABLE `CHI_TIET_ORDER_OPTION`
                ADD CONSTRAINT `fk_ctoo_chi_tiet_order`
                FOREIGN KEY (`ma_order`, `ma_mon`) REFERENCES `CHI_TIET_ORDER`(`ma_order`, `ma_mon`)
                ON UPDATE CASCADE ON DELETE CASCADE
        ');
    }
};
