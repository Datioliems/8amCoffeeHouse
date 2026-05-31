-- ============================================================
--  8AM COFFEE & ROASTERY - QR ORDER SYSTEM
--  MySQL/MariaDB Database for XAMPP
--  Converted from database/8am_coffee_database.sql
-- ============================================================

DROP DATABASE IF EXISTS `8am_coffee`;
CREATE DATABASE `8am_coffee`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `8am_coffee`;

SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS `VW_TON_KHO_TONG_QUAN`;
DROP VIEW IF EXISTS `VW_MENU_HIEN_THI`;
DROP VIEW IF EXISTS `VW_ORDER_DASHBOARD`;

DROP TABLE IF EXISTS `CHI_TIET_KIEM_KE`;
DROP TABLE IF EXISTS `PHIEU_KIEM_KE`;
DROP TABLE IF EXISTS `CHI_TIET_NHAP_KHO`;
DROP TABLE IF EXISTS `PHIEU_NHAP_KHO`;
DROP TABLE IF EXISTS `NHA_CUNG_CAP`;
DROP TABLE IF EXISTS `TON_KHO`;
DROP TABLE IF EXISTS `HOA_DON`;
DROP TABLE IF EXISTS `CHI_TIET_ORDER`;
DROP TABLE IF EXISTS `ORDERS`;
DROP TABLE IF EXISTS `DINH_MUC`;
DROP TABLE IF EXISTS `NGUYEN_LIEU`;
DROP TABLE IF EXISTS `MON`;
DROP TABLE IF EXISTS `DANH_MUC`;
DROP TABLE IF EXISTS `KHACH_HANG`;
DROP TABLE IF EXISTS `BAN`;
DROP TABLE IF EXISTS `TAI_KHOAN`;
DROP TABLE IF EXISTS `NHAN_VIEN`;
DROP TABLE IF EXISTS `CHI_NHANH`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `CHI_NHANH` (
    `ma_chi_nhanh` VARCHAR(10) NOT NULL,
    `ten_chi_nhanh` VARCHAR(100) NOT NULL,
    `dia_chi` VARCHAR(255) NULL,
    `sdt` VARCHAR(15) NULL,
    PRIMARY KEY (`ma_chi_nhanh`),
    UNIQUE KEY `uq_chi_nhanh_ten` (`ten_chi_nhanh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `NHAN_VIEN` (
    `ma_nv` VARCHAR(10) NOT NULL,
    `ten_nv` VARCHAR(100) NOT NULL,
    `sdt` VARCHAR(15) NULL,
    `cccd` VARCHAR(12) NULL,
    `dia_chi` VARCHAR(255) NULL,
    `ma_chi_nhanh` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`ma_nv`),
    UNIQUE KEY `uq_nv_cccd` (`cccd`),
    CONSTRAINT `fk_nv_chi_nhanh` FOREIGN KEY (`ma_chi_nhanh`) REFERENCES `CHI_NHANH` (`ma_chi_nhanh`)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `TAI_KHOAN` (
    `ma_tai_khoan` VARCHAR(10) NOT NULL,
    `ten_tk` VARCHAR(50) NOT NULL,
    `mat_khau` VARCHAR(255) NOT NULL,
    `chuc_vu` VARCHAR(20) NOT NULL,
    `trang_thai` VARCHAR(10) NOT NULL DEFAULT 'active',
    `ma_nv` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`ma_tai_khoan`),
    UNIQUE KEY `uq_tk_ten` (`ten_tk`),
    CONSTRAINT `fk_tk_nhan_vien` FOREIGN KEY (`ma_nv`) REFERENCES `NHAN_VIEN` (`ma_nv`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `BAN` (
    `ma_ban` VARCHAR(10) NOT NULL,
    `so_ban` INT UNSIGNED NOT NULL,
    `vi_tri` VARCHAR(50) NULL,
    `trang_thai` VARCHAR(10) NOT NULL DEFAULT 'trong',
    `ma_chi_nhanh` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`ma_ban`),
    UNIQUE KEY `uq_ban_so_chi_nhanh` (`so_ban`, `ma_chi_nhanh`),
    CONSTRAINT `fk_ban_chi_nhanh` FOREIGN KEY (`ma_chi_nhanh`) REFERENCES `CHI_NHANH` (`ma_chi_nhanh`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `KHACH_HANG` (
    `ma_kh` VARCHAR(10) NOT NULL,
    `ten_kh` VARCHAR(100) NOT NULL,
    `sdt` VARCHAR(15) NULL,
    `ngay_tao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_kh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `DANH_MUC` (
    `ma_danh_muc` VARCHAR(10) NOT NULL,
    `ten_danh_muc` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`ma_danh_muc`),
    UNIQUE KEY `uq_danh_muc_ten` (`ten_danh_muc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `MON` (
    `ma_mon` VARCHAR(10) NOT NULL,
    `ten_mon` VARCHAR(100) NOT NULL,
    `don_gia` DECIMAL(12,0) NOT NULL,
    `mo_ta` VARCHAR(500) NULL,
    `hinh_anh` VARCHAR(255) NULL,
    `ma_danh_muc` VARCHAR(10) NOT NULL,
    `trang_thai` VARCHAR(10) NOT NULL DEFAULT 'active',
    PRIMARY KEY (`ma_mon`),
    CONSTRAINT `fk_mon_danh_muc` FOREIGN KEY (`ma_danh_muc`) REFERENCES `DANH_MUC` (`ma_danh_muc`)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `NGUYEN_LIEU` (
    `ma_nl` VARCHAR(10) NOT NULL,
    `ten_nl` VARCHAR(100) NOT NULL,
    `don_vi` VARCHAR(20) NOT NULL,
    PRIMARY KEY (`ma_nl`),
    UNIQUE KEY `uq_nl_ten` (`ten_nl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `DINH_MUC` (
    `ma_mon` VARCHAR(10) NOT NULL,
    `ma_nl` VARCHAR(10) NOT NULL,
    `so_luong_dung` DECIMAL(10,2) NOT NULL,
    `mo_ta` VARCHAR(200) NULL,
    PRIMARY KEY (`ma_mon`, `ma_nl`),
    CONSTRAINT `fk_dm_mon` FOREIGN KEY (`ma_mon`) REFERENCES `MON` (`ma_mon`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_dm_nguyen_lieu` FOREIGN KEY (`ma_nl`) REFERENCES `NGUYEN_LIEU` (`ma_nl`)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ORDERS` (
    `ma_order` VARCHAR(20) NOT NULL,
    `ma_ban` VARCHAR(10) NOT NULL,
    `ma_kh` VARCHAR(10) NULL,
    `ma_chi_nhanh` VARCHAR(10) NOT NULL,
    `trang_thai` VARCHAR(15) NOT NULL DEFAULT 'cho_xac_nhan',
    `ngay_order` DATE NOT NULL DEFAULT (CURRENT_DATE),
    `gio_order` TIME NOT NULL DEFAULT (CURRENT_TIME),
    `ghi_chu` VARCHAR(300) NULL,
    PRIMARY KEY (`ma_order`),
    KEY `ix_orders_status_branch_date` (`trang_thai`, `ma_chi_nhanh`, `ngay_order`),
    KEY `ix_orders_ban_ngay` (`ma_ban`, `ngay_order`),
    CONSTRAINT `fk_orders_ban` FOREIGN KEY (`ma_ban`) REFERENCES `BAN` (`ma_ban`),
    CONSTRAINT `fk_orders_khach_hang` FOREIGN KEY (`ma_kh`) REFERENCES `KHACH_HANG` (`ma_kh`) ON DELETE SET NULL,
    CONSTRAINT `fk_orders_chi_nhanh` FOREIGN KEY (`ma_chi_nhanh`) REFERENCES `CHI_NHANH` (`ma_chi_nhanh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `CHI_TIET_ORDER` (
    `ma_order` VARCHAR(20) NOT NULL,
    `ma_mon` VARCHAR(10) NOT NULL,
    `so_luong` INT UNSIGNED NOT NULL,
    `don_gia_tai_thoi_diem` DECIMAL(12,0) NOT NULL,
    `ghi_chu` VARCHAR(200) NULL,
    PRIMARY KEY (`ma_order`, `ma_mon`),
    CONSTRAINT `fk_cto_orders` FOREIGN KEY (`ma_order`) REFERENCES `ORDERS` (`ma_order`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_cto_mon` FOREIGN KEY (`ma_mon`) REFERENCES `MON` (`ma_mon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `HOA_DON` (
    `ma_hoa_don` VARCHAR(20) NOT NULL,
    `ma_order` VARCHAR(20) NOT NULL,
    `ma_kh` VARCHAR(10) NULL,
    `thoi_gian_lap` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `tong_tien_truoc_ck` DECIMAL(12,0) NOT NULL DEFAULT 0,
    `chiet_khau` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `tong_tien_sau_ck` DECIMAL(12,0) NOT NULL DEFAULT 0,
    `phuong_thuc_tt` VARCHAR(20) NOT NULL DEFAULT 'tien_mat',
    `trang_thai` VARCHAR(15) NOT NULL DEFAULT 'cho_thanh_toan',
    `ma_nv_thu_ngan` VARCHAR(10) NULL,
    PRIMARY KEY (`ma_hoa_don`),
    UNIQUE KEY `uq_hd_order` (`ma_order`),
    KEY `ix_hoa_don_thoi_gian` (`thoi_gian_lap`, `trang_thai`),
    CONSTRAINT `fk_hd_orders` FOREIGN KEY (`ma_order`) REFERENCES `ORDERS` (`ma_order`),
    CONSTRAINT `fk_hd_khach_hang` FOREIGN KEY (`ma_kh`) REFERENCES `KHACH_HANG` (`ma_kh`) ON DELETE SET NULL,
    CONSTRAINT `fk_hd_nhan_vien` FOREIGN KEY (`ma_nv_thu_ngan`) REFERENCES `NHAN_VIEN` (`ma_nv`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `TON_KHO` (
    `ma_chi_nhanh` VARCHAR(10) NOT NULL,
    `ma_nl` VARCHAR(10) NOT NULL,
    `sl_ton_kho_he_thong` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `sl_ton_kho_thuc_te` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `nguong_canh_bao` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `hao_hut_cost` DECIMAL(12,0) NOT NULL DEFAULT 0,
    PRIMARY KEY (`ma_chi_nhanh`, `ma_nl`),
    KEY `ix_ton_kho_canh_bao` (`ma_chi_nhanh`, `sl_ton_kho_he_thong`, `nguong_canh_bao`),
    CONSTRAINT `fk_tk_chi_nhanh` FOREIGN KEY (`ma_chi_nhanh`) REFERENCES `CHI_NHANH` (`ma_chi_nhanh`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_tk_nguyen_lieu` FOREIGN KEY (`ma_nl`) REFERENCES `NGUYEN_LIEU` (`ma_nl`)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `NHA_CUNG_CAP` (
    `ma_ncc` VARCHAR(10) NOT NULL,
    `ten_ncc` VARCHAR(100) NOT NULL,
    `dia_chi` VARCHAR(255) NULL,
    `sdt` VARCHAR(15) NULL,
    `email` VARCHAR(100) NULL,
    PRIMARY KEY (`ma_ncc`),
    UNIQUE KEY `uq_ncc_ten` (`ten_ncc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PHIEU_NHAP_KHO` (
    `ma_pnk` VARCHAR(20) NOT NULL,
    `ngay_nk` DATE NOT NULL DEFAULT (CURRENT_DATE),
    `ma_ncc` VARCHAR(10) NOT NULL,
    `ma_nv` VARCHAR(10) NOT NULL,
    `ma_chi_nhanh` VARCHAR(10) NOT NULL,
    `tong_gia_tri` DECIMAL(15,0) NOT NULL DEFAULT 0,
    `trang_thai` VARCHAR(10) NOT NULL DEFAULT 'cho_duyet',
    `ghi_chu` VARCHAR(300) NULL,
    PRIMARY KEY (`ma_pnk`),
    KEY `ix_pnk_chi_nhanh_ngay` (`ma_chi_nhanh`, `ngay_nk`),
    CONSTRAINT `fk_pnk_ncc` FOREIGN KEY (`ma_ncc`) REFERENCES `NHA_CUNG_CAP` (`ma_ncc`),
    CONSTRAINT `fk_pnk_nv` FOREIGN KEY (`ma_nv`) REFERENCES `NHAN_VIEN` (`ma_nv`),
    CONSTRAINT `fk_pnk_chi_nhanh` FOREIGN KEY (`ma_chi_nhanh`) REFERENCES `CHI_NHANH` (`ma_chi_nhanh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `CHI_TIET_NHAP_KHO` (
    `ma_pnk` VARCHAR(20) NOT NULL,
    `ma_nl` VARCHAR(10) NOT NULL,
    `so_luong` DECIMAL(12,2) NOT NULL,
    `don_gia` DECIMAL(12,0) NOT NULL,
    `tong_tien` DECIMAL(15,0) GENERATED ALWAYS AS (`so_luong` * `don_gia`) STORED,
    PRIMARY KEY (`ma_pnk`, `ma_nl`),
    CONSTRAINT `fk_ctnk_phieu` FOREIGN KEY (`ma_pnk`) REFERENCES `PHIEU_NHAP_KHO` (`ma_pnk`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_ctnk_nguyen_lieu` FOREIGN KEY (`ma_nl`) REFERENCES `NGUYEN_LIEU` (`ma_nl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PHIEU_KIEM_KE` (
    `ma_pkk` VARCHAR(20) NOT NULL,
    `ngay_kk` DATE NOT NULL DEFAULT (CURRENT_DATE),
    `ma_chi_nhanh` VARCHAR(10) NOT NULL,
    `ma_nv` VARCHAR(10) NOT NULL,
    `trang_thai` VARCHAR(15) NOT NULL DEFAULT 'nhap',
    `ghi_chu` VARCHAR(300) NULL,
    PRIMARY KEY (`ma_pkk`),
    CONSTRAINT `fk_pkk_chi_nhanh` FOREIGN KEY (`ma_chi_nhanh`) REFERENCES `CHI_NHANH` (`ma_chi_nhanh`),
    CONSTRAINT `fk_pkk_nv` FOREIGN KEY (`ma_nv`) REFERENCES `NHAN_VIEN` (`ma_nv`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `CHI_TIET_KIEM_KE` (
    `ma_pkk` VARCHAR(20) NOT NULL,
    `ma_nl` VARCHAR(10) NOT NULL,
    `sl_he_thong` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `sl_thuc_te` DECIMAL(12,2) NOT NULL,
    `chenh_lech` DECIMAL(12,2) GENERATED ALWAYS AS (`sl_thuc_te` - `sl_he_thong`) STORED,
    `don_gia_tb` DECIMAL(12,0) NULL,
    PRIMARY KEY (`ma_pkk`, `ma_nl`),
    CONSTRAINT `fk_ctkk_phieu` FOREIGN KEY (`ma_pkk`) REFERENCES `PHIEU_KIEM_KE` (`ma_pkk`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_ctkk_nguyen_lieu` FOREIGN KEY (`ma_nl`) REFERENCES `NGUYEN_LIEU` (`ma_nl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER //

CREATE TRIGGER `TRG_ORDER_TRU_TON_KHO`
AFTER UPDATE ON `ORDERS`
FOR EACH ROW
BEGIN
    IF NEW.trang_thai = 'da_xac_nhan' AND OLD.trang_thai <> 'da_xac_nhan' THEN
        UPDATE `TON_KHO` tk
        JOIN `DINH_MUC` dm ON dm.ma_nl = tk.ma_nl
        JOIN `CHI_TIET_ORDER` cto ON cto.ma_mon = dm.ma_mon
        SET tk.sl_ton_kho_he_thong = tk.sl_ton_kho_he_thong - (cto.so_luong * dm.so_luong_dung)
        WHERE cto.ma_order = NEW.ma_order
          AND tk.ma_chi_nhanh = NEW.ma_chi_nhanh;
    END IF;
END//

CREATE TRIGGER `TRG_ORDER_CAP_NHAT_TRANG_THAI_BAN`
AFTER UPDATE ON `ORDERS`
FOR EACH ROW
BEGIN
    IF NEW.trang_thai IN ('cho_xac_nhan', 'da_xac_nhan', 'dang_pha_che', 'da_phuc_vu') THEN
        UPDATE `BAN` SET trang_thai = 'co_khach' WHERE ma_ban = NEW.ma_ban;
    END IF;

    IF NEW.trang_thai IN ('hoan_thanh', 'da_huy') THEN
        IF NOT EXISTS (
            SELECT 1 FROM `ORDERS`
            WHERE ma_ban = NEW.ma_ban
              AND trang_thai IN ('cho_xac_nhan', 'da_xac_nhan', 'dang_pha_che', 'da_phuc_vu')
        ) THEN
            UPDATE `BAN` SET trang_thai = 'trong' WHERE ma_ban = NEW.ma_ban;
        END IF;
    END IF;
END//

CREATE TRIGGER `TRG_NHAP_KHO_CAP_NHAT_TON`
AFTER UPDATE ON `PHIEU_NHAP_KHO`
FOR EACH ROW
BEGIN
    IF NEW.trang_thai = 'da_duyet' AND OLD.trang_thai <> 'da_duyet' THEN
        INSERT INTO `TON_KHO` (`ma_chi_nhanh`, `ma_nl`, `sl_ton_kho_he_thong`, `sl_ton_kho_thuc_te`, `nguong_canh_bao`, `hao_hut_cost`)
        SELECT NEW.ma_chi_nhanh, ct.ma_nl, ct.so_luong, ct.so_luong, 0, 0
        FROM `CHI_TIET_NHAP_KHO` ct
        WHERE ct.ma_pnk = NEW.ma_pnk
        ON DUPLICATE KEY UPDATE
            sl_ton_kho_he_thong = sl_ton_kho_he_thong + VALUES(sl_ton_kho_he_thong),
            sl_ton_kho_thuc_te = sl_ton_kho_thuc_te + VALUES(sl_ton_kho_thuc_te);
    END IF;
END//

CREATE TRIGGER `TRG_NHAP_KHO_TINH_TONG_INSERT`
AFTER INSERT ON `CHI_TIET_NHAP_KHO`
FOR EACH ROW
BEGIN
    UPDATE `PHIEU_NHAP_KHO`
    SET tong_gia_tri = (
        SELECT COALESCE(SUM(so_luong * don_gia), 0)
        FROM `CHI_TIET_NHAP_KHO`
        WHERE ma_pnk = NEW.ma_pnk
    )
    WHERE ma_pnk = NEW.ma_pnk;
END//

CREATE TRIGGER `TRG_NHAP_KHO_TINH_TONG_UPDATE`
AFTER UPDATE ON `CHI_TIET_NHAP_KHO`
FOR EACH ROW
BEGIN
    UPDATE `PHIEU_NHAP_KHO`
    SET tong_gia_tri = (
        SELECT COALESCE(SUM(so_luong * don_gia), 0)
        FROM `CHI_TIET_NHAP_KHO`
        WHERE ma_pnk = NEW.ma_pnk
    )
    WHERE ma_pnk = NEW.ma_pnk;
END//

CREATE TRIGGER `TRG_NHAP_KHO_TINH_TONG_DELETE`
AFTER DELETE ON `CHI_TIET_NHAP_KHO`
FOR EACH ROW
BEGIN
    UPDATE `PHIEU_NHAP_KHO`
    SET tong_gia_tri = (
        SELECT COALESCE(SUM(so_luong * don_gia), 0)
        FROM `CHI_TIET_NHAP_KHO`
        WHERE ma_pnk = OLD.ma_pnk
    )
    WHERE ma_pnk = OLD.ma_pnk;
END//

CREATE TRIGGER `TRG_KIEM_KE_CAP_NHAT_TON`
AFTER UPDATE ON `PHIEU_KIEM_KE`
FOR EACH ROW
BEGIN
    IF NEW.trang_thai = 'da_xac_nhan' AND OLD.trang_thai <> 'da_xac_nhan' THEN
        UPDATE `TON_KHO` tk
        JOIN `CHI_TIET_KIEM_KE` ct ON ct.ma_nl = tk.ma_nl
        SET tk.sl_ton_kho_thuc_te = ct.sl_thuc_te,
            tk.sl_ton_kho_he_thong = ct.sl_thuc_te,
            tk.hao_hut_cost = CASE
                WHEN ct.sl_thuc_te < ct.sl_he_thong
                THEN (ct.sl_he_thong - ct.sl_thuc_te) * COALESCE(ct.don_gia_tb, 0)
                ELSE tk.hao_hut_cost
            END
        WHERE ct.ma_pkk = NEW.ma_pkk
          AND tk.ma_chi_nhanh = NEW.ma_chi_nhanh;
    END IF;
END//

DELIMITER ;

CREATE VIEW `VW_ORDER_DASHBOARD` AS
SELECT
    o.ma_order,
    b.so_ban,
    cn.ten_chi_nhanh,
    k.ten_kh,
    o.trang_thai,
    o.ngay_order,
    o.gio_order,
    COALESCE(SUM(cto.so_luong * cto.don_gia_tai_thoi_diem), 0) AS tong_tien
FROM `ORDERS` o
JOIN `BAN` b ON b.ma_ban = o.ma_ban
JOIN `CHI_NHANH` cn ON cn.ma_chi_nhanh = o.ma_chi_nhanh
LEFT JOIN `KHACH_HANG` k ON k.ma_kh = o.ma_kh
LEFT JOIN `CHI_TIET_ORDER` cto ON cto.ma_order = o.ma_order
WHERE o.trang_thai IN ('cho_xac_nhan', 'da_xac_nhan', 'dang_pha_che')
GROUP BY o.ma_order, b.so_ban, cn.ten_chi_nhanh, k.ten_kh, o.trang_thai, o.ngay_order, o.gio_order;

CREATE VIEW `VW_MENU_HIEN_THI` AS
SELECT m.ma_mon, m.ten_mon, m.don_gia, m.mo_ta, m.hinh_anh, m.trang_thai, dm.ten_danh_muc
FROM `MON` m
JOIN `DANH_MUC` dm ON dm.ma_danh_muc = m.ma_danh_muc
WHERE m.trang_thai = 'active';

CREATE VIEW `VW_TON_KHO_TONG_QUAN` AS
SELECT
    cn.ten_chi_nhanh,
    nl.ten_nl,
    nl.don_vi,
    tk.sl_ton_kho_he_thong,
    tk.sl_ton_kho_thuc_te,
    tk.nguong_canh_bao,
    tk.hao_hut_cost,
    CASE
        WHEN tk.sl_ton_kho_he_thong = 0 THEN 'HET_HANG'
        WHEN tk.sl_ton_kho_he_thong < tk.nguong_canh_bao THEN 'SAP_HET'
        ELSE 'DU_HANG'
    END AS trang_thai_kho
FROM `TON_KHO` tk
JOIN `CHI_NHANH` cn ON cn.ma_chi_nhanh = tk.ma_chi_nhanh
JOIN `NGUYEN_LIEU` nl ON nl.ma_nl = tk.ma_nl;

INSERT INTO `CHI_NHANH` VALUES
('CN001', '8AM Coffee & Roastery', '34 Tăng Bạt Hổ, Hai Bà Trưng, Hà Nội', '0241234567');

INSERT INTO `DANH_MUC` VALUES
('DM001', 'Arabica Base'),
('DM002', 'Signature'),
('DM003', 'Hand Brew'),
('DM004', 'Cold Brew'),
('DM005', 'Fine Robusta Base'),
('DM006', 'Not-café'),
('DM007', 'Eats');

INSERT INTO `NGUYEN_LIEU` VALUES
('NL001', 'Cà phê hạt Arabica', 'gram'),
('NL002', 'Cà phê hạt Robusta Fine', 'gram'),
('NL003', 'Sữa tươi nguyên kem', 'ml'),
('NL004', 'Sữa đặc', 'ml'),
('NL005', 'Nước lọc', 'ml'),
('NL006', 'Kem béo (heavy cream)', 'ml'),
('NL007', 'Kem mặn (salted cream)', 'ml'),
('NL008', 'Caramel syrup', 'ml'),
('NL009', 'Mứt hồng', 'gram'),
('NL010', 'Trứng gà', 'cai'),
('NL011', 'Cold Brew concentrate', 'ml'),
('NL012', 'Tiramisu mix', 'gram'),
('NL013', 'Rượu Bailey', 'ml'),
('NL014', 'Bột gừng', 'gram'),
('NL015', 'Quả mơ (mứt mơ)', 'gram'),
('NL016', 'Me (mứt me)', 'gram'),
('NL017', 'Tonic water', 'ml'),
('NL018', 'Mứt đào', 'gram'),
('NL019', 'Chanh leo tươi', 'gram'),
('NL020', 'Sữa chua không đường', 'gram'),
('NL021', 'Ca cao nguyên chất', 'gram'),
('NL022', 'Chanh tươi', 'gram'),
('NL023', 'Xí muội', 'gram'),
('NL024', 'Mứt chanh leo (Tiramisu)', 'gram'),
('NL025', 'Lục trà (green tea)', 'gram'),
('NL026', 'Mứt ổi', 'gram'),
('NL027', 'Đá viên', 'gram'),
('NL028', 'Bánh sừng bò plain', 'cai'),
('NL029', 'Bánh sừng bò socola', 'cai'),
('NL030', 'Hạt sen sấy', 'gram');

INSERT INTO `MON` VALUES
('MON001', 'Espresso', 35000, 'Double shot espresso', NULL, 'DM001', 'active'),
('MON002', 'Americano', 40000, 'Espresso, nước', NULL, 'DM001', 'active'),
('MON003', 'Latte', 45000, 'Espresso, sữa tươi', NULL, 'DM001', 'active'),
('MON004', ':am ấm', 45000, 'Espresso, sữa tươi, sữa đặc, kem béo', NULL, 'DM001', 'active'),
('MON005', 'Salted Caramel', 50000, 'Espresso, sữa tươi, caramel, kem mặn', NULL, 'DM001', 'active');

INSERT INTO `DINH_MUC` VALUES
('MON001','NL001',18.00,'2 shot espresso Arabica'),
('MON001','NL005',30.00,'Nước pha espresso'),
('MON002','NL001',18.00,'2 shot espresso'),
('MON002','NL005',150.00,'Nước nóng pha loãng'),
('MON003','NL001',18.00,'1 shot espresso'),
('MON003','NL003',180.00,'Sữa tươi steam'),
('MON004','NL001',18.00,'1 shot espresso'),
('MON004','NL003',120.00,'Sữa tươi'),
('MON004','NL004',20.00,'Sữa đặc'),
('MON004','NL006',30.00,'Kem béo'),
('MON005','NL001',18.00,'1 shot espresso'),
('MON005','NL003',150.00,'Sữa tươi'),
('MON005','NL006',30.00,'Kem topping');

INSERT INTO `BAN` VALUES
('B001',1,'Tầng 1 - Cửa sổ phố','trong','CN001'),
('B002',2,'Tầng 1 - Giữa','trong','CN001'),
('B003',3,'Tầng 1 - Góc trong','trong','CN001'),
('B004',4,'Tầng 1 - Quầy bar','trong','CN001'),
('B005',5,'Tầng 2 - Ban công ngoài','trong','CN001'),
('B006',6,'Tầng 2 - Sofa góc','trong','CN001'),
('B007',7,'Tầng 2 - Bàn dài','trong','CN001'),
('B008',8,'Tầng 2 - Cạnh cầu thang','trong','CN001');

INSERT INTO `TON_KHO` (`ma_chi_nhanh`, `ma_nl`, `sl_ton_kho_he_thong`, `sl_ton_kho_thuc_te`, `nguong_canh_bao`) VALUES
('CN001','NL001',5000.00,5000.00,500.00),
('CN001','NL002',3000.00,3000.00,300.00),
('CN001','NL003',15000.00,15000.00,1500.00),
('CN001','NL004',5000.00,5000.00,500.00),
('CN001','NL005',50000.00,50000.00,5000.00),
('CN001','NL006',3000.00,3000.00,300.00);

INSERT INTO `NHA_CUNG_CAP` VALUES
('NCC001','Phúc Sinh Corporation','TP. Hồ Chí Minh','0281234567','info@phucsinh.com'),
('NCC002','Dalat Milk','Đà Lạt, Lâm Đồng','0263456789','dalatmilk@dl.vn'),
('NCC003','Khánh Hòa Salanganes','Khánh Hòa','0258765432',NULL),
('NCC004','Thực phẩm Đức Việt','Hà Nội','0241112222','ducviet@hn.vn');

INSERT INTO `NHAN_VIEN` VALUES
('NV001','Nguyễn Văn An','0901234567','001234567890','Hà Nội','CN001'),
('NV002','Trần Thị Bình','0912345678','001234567891','Hà Nội','CN001'),
('NV003','Lê Văn Cường','0923456789','001234567892','Hà Nội','CN001');

-- Mật khẩu mẫu: Admin@123
INSERT INTO `TAI_KHOAN` VALUES
('TK001','admin_8am','$2y$10$VgT3NEZK0qik2K/KwvvKqeusp9Dmv4KaxsKVD6XgQzOfDH24xbUWO','quan_ly','active','NV001'),
('TK002','bartender01','$2y$10$VgT3NEZK0qik2K/KwvvKqeusp9Dmv4KaxsKVD6XgQzOfDH24xbUWO','bartender','active','NV002'),
('TK003','staff01','$2y$10$VgT3NEZK0qik2K/KwvvKqeusp9Dmv4KaxsKVD6XgQzOfDH24xbUWO','nhan_vien','active','NV003');

-- ============================================================
-- UPDATE hinh_anh — chạy sau khi import (ảnh để ở public/images/)
-- ============================================================
UPDATE `MON` SET `hinh_anh` = 'espresso.jpg'       WHERE `ma_mon` = 'MON001';
UPDATE `MON` SET `hinh_anh` = 'americano.jpg'      WHERE `ma_mon` = 'MON002';
UPDATE `MON` SET `hinh_anh` = 'latte.jpg'          WHERE `ma_mon` = 'MON003';
UPDATE `MON` SET `hinh_anh` = 'cam_em.jpg'         WHERE `ma_mon` = 'MON004';
UPDATE `MON` SET `hinh_anh` = 'salted_caramel.jpg' WHERE `ma_mon` = 'MON005';
