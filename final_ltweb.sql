-- ================================================================
-- HỆ THỐNG QUẢN LÝ NHẬP NGUYÊN VẬT LIỆU
-- ================================================================

-- 1. TẮT KIỂM TRA KHÓA NGOẠI VÀ XÓA BẢNG CŨ (NẾU CÓ) ĐỂ RESET
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS purchase_orders;
DROP TABLE IF EXISTS supplier_materials;
DROP TABLE IF EXISTS materials;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- 2. TẠO LẠI CẤU TRÚC BẢNG (TABLES)
-- ================================================================

CREATE TABLE `materials` (
  `material_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `material_name` varchar(100) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `supplier_name` varchar(100) NOT NULL,
  `contact_email` varchar(100) DEFAULT NULL UNIQUE,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `supplier_materials` (
  `supplier_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `supply_price` decimal(15,2) NOT NULL,
  PRIMARY KEY (`supplier_id`,`material_id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE,
  FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('Admin','Staff') DEFAULT 'Staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `purchase_orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `supplier_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `expected_date` datetime NOT NULL,
  `actual_date` datetime DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `order_status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending',
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  FOREIGN KEY (`admin_id`) REFERENCES `users` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `order_details` (
  `order_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  PRIMARY KEY (`order_id`,`material_id`),
  FOREIGN KEY (`order_id`) REFERENCES `purchase_orders` (`order_id`) ON DELETE CASCADE,
  FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- 3. XÓA VÀ TẠO LẠI THỦ TỤC LƯU TRỮ (STORED PROCEDURES)
-- ================================================================

DELIMITER $$

-- Xóa thủ tục cũ nếu tồn tại
DROP PROCEDURE IF EXISTS `sp_add_supplier`$$
DROP PROCEDURE IF EXISTS `sp_update_supplier`$$
DROP PROCEDURE IF EXISTS `sp_delete_supplier`$$
DROP PROCEDURE IF EXISTS `sp_get_all_suppliers`$$
DROP PROCEDURE IF EXISTS `sp_get_supplier_by_id`$$
DROP PROCEDURE IF EXISTS `sp_search_suppliers`$$
DROP PROCEDURE IF EXISTS `sp_check_email_exists`$$

-- Thủ tục Thêm Nhà Cung Cấp Mới
CREATE PROCEDURE `sp_add_supplier` (
    IN `p_name` VARCHAR(100), 
    IN `p_email` VARCHAR(100), 
    IN `p_phone` VARCHAR(20), 
    IN `p_address` TEXT, 
    IN `p_status` VARCHAR(20), 
    OUT `p_result_id` INT, 
    OUT `p_message` VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_result_id = -1;
        SET p_message = 'Lỗi: Email đã tồn tại hoặc dữ liệu không hợp lệ';
    END;
    
    IF p_name IS NULL OR p_name = '' THEN
        SET p_result_id = -1;
        SET p_message = 'Tên nhà cung cấp không được để trống';
    ELSEIF p_email IS NULL OR p_email = '' THEN
        SET p_result_id = -1;
        SET p_message = 'Email không được để trống';
    ELSE
        INSERT INTO suppliers (supplier_name, contact_email, phone_number, address, status)
        VALUES (p_name, p_email, p_phone, p_address, COALESCE(p_status, 'Active'));
        
        SET p_result_id = LAST_INSERT_ID();
        SET p_message = 'Thêm nhà cung cấp thành công';
    END IF;
END$$

-- Thủ tục Cập nhật Nhà Cung Cấp
CREATE PROCEDURE `sp_update_supplier` (
    IN `p_id` INT, 
    IN `p_name` VARCHAR(100), 
    IN `p_email` VARCHAR(100), 
    IN `p_phone` VARCHAR(20), 
    IN `p_address` TEXT, 
    IN `p_status` VARCHAR(20), 
    OUT `p_success` BOOLEAN, 
    OUT `p_message` VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_success = FALSE;
        SET p_message = 'Lỗi: Email đã tồn tại hoặc dữ liệu không hợp lệ';
    END;
    
    IF NOT EXISTS (SELECT 1 FROM suppliers WHERE supplier_id = p_id) THEN
        SET p_success = FALSE;
        SET p_message = 'Nhà cung cấp không tồn tại';
    ELSE
        UPDATE suppliers
        SET supplier_name = p_name,
            contact_email = p_email,
            phone_number = p_phone,
            address = p_address,
            status = COALESCE(p_status, 'Active')
        WHERE supplier_id = p_id;
        
        SET p_success = TRUE;
        SET p_message = 'Cập nhật nhà cung cấp thành công';
    END IF;
END$$

-- Thủ tục Xóa Nhà Cung Cấp
CREATE PROCEDURE `sp_delete_supplier` (
    IN `p_id` INT, 
    OUT `p_success` BOOLEAN, 
    OUT `p_message` VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM suppliers WHERE supplier_id = p_id) THEN
        SET p_success = FALSE;
        SET p_message = 'Nhà cung cấp không tồn tại';
    ELSE
        DELETE FROM suppliers WHERE supplier_id = p_id;
        SET p_success = TRUE;
        SET p_message = 'Xóa nhà cung cấp thành công';
    END IF;
END$$

-- Thủ tục Lấy tất cả Nhà Cung Cấp
CREATE PROCEDURE `sp_get_all_suppliers` ()
BEGIN
    SELECT supplier_id, supplier_name, contact_email, phone_number, address, status, created_at
    FROM suppliers
    ORDER BY created_at DESC;
END$$

-- Thủ tục Tìm Nhà Cung Cấp Theo ID
CREATE PROCEDURE `sp_get_supplier_by_id` (IN `p_id` INT)
BEGIN
    SELECT supplier_id, supplier_name, contact_email, phone_number, address, status, created_at
    FROM suppliers
    WHERE supplier_id = p_id;
END$$

-- Thủ tục Kiểm tra Email
CREATE PROCEDURE `sp_check_email_exists` (
    IN `p_email` VARCHAR(100), 
    IN `p_exclude_id` INT, 
    OUT `p_exists` BOOLEAN
)
BEGIN
    SELECT COUNT(*) INTO @count
    FROM suppliers
    WHERE contact_email = p_email
    AND (p_exclude_id IS NULL OR supplier_id != p_exclude_id);
    
    SET p_exists = (@count > 0);
END$$

DELIMITER ;

-- ================================================================
-- 4. CHÈN MỘT VÀI DỮ LIỆU MẪU ĐỂ TEST
-- ================================================================

INSERT INTO `users` (`username`, `password`, `full_name`, `role`) VALUES 
('admin', '123456', 'Quản trị viên', 'Admin'),
('staff1', '123456', 'Nhân viên A', 'Staff'),
('staff2', '123456', 'Nhân viên B', 'Staff');

INSERT INTO `suppliers` (`supplier_name`, `contact_email`, `phone_number`, `address`, `status`) VALUES 
('Công ty Gỗ Nhựa', 'gonhua@gmail.com', '0911223344', 'Hà Nội', 'Active'),
('Công ty Thép Xây Dựng', 'thepxd@gmail.com', '0988776655', 'TP.HCM', 'Active'),
('Công ty Xi Măng Việt Nam', 'ximangvn@gmail.com', '0901234567', 'Hải Phòng', 'Active'),
('Công ty Sơn Bả', 'sonba@gmail.com', '0912345678', 'Đà Nẵng', 'Active'),
('Công ty Điện Lạnh', 'dienlanh@gmail.com', '0987654321', 'Cần Thơ', 'Active');

INSERT INTO `materials` (`material_name`, `unit`, `unit_price`, `description`) VALUES 
('Gỗ thông', 'm3', 1500000.00, 'Gỗ thông chất lượng cao'),
('Thép ống', 'kg', 25000.00, 'Thép ống xây dựng'),
('Xi măng', 'bao', 80000.00, 'Xi măng Portland'),
('Sơn nội thất', 'lít', 120000.00, 'Sơn nội thất chống thấm'),
('Máy lạnh', 'cái', 5000000.00, 'Máy lạnh 1HP'),
('Ngói', 'tấm', 10000.00, 'Ngói đất sét'),
('Cửa gỗ', 'bộ', 2000000.00, 'Cửa gỗ MDF'),
('Ống nước', 'm', 50000.00, 'Ống nước PVC');

INSERT INTO `supplier_materials` (`supplier_id`, `material_id`, `supply_price`) VALUES 
(1, 1, 1500000.00),
(1, 7, 2000000.00),
(2, 2, 25000.00),
(2, 8, 50000.00),
(3, 3, 80000.00),
(4, 4, 120000.00),
(5, 5, 5000000.00),
(1, 6, 10000.00);

INSERT INTO `purchase_orders` (`supplier_id`, `admin_id`, `order_date`, `expected_date`, `actual_date`, `total_amount`, `order_status`) VALUES 
(1, 1, '2024-01-15 10:00:00', '2024-01-20 10:00:00', '2024-01-18 10:00:00', 3000000.00, 'Completed'),
(2, 1, '2024-02-10 14:00:00', '2024-02-15 14:00:00', '2024-02-12 14:00:00', 500000.00, 'Completed'),
(3, 2, '2024-03-05 09:00:00', '2024-03-10 09:00:00', '2024-03-08 09:00:00', 1600000.00, 'Completed'),
(4, 2, '2024-04-20 11:00:00', '2024-04-25 11:00:00', '2024-04-22 11:00:00', 3600000.00, 'Completed'),
(5, 1, '2024-05-12 16:00:00', '2024-05-17 16:00:00', '2024-05-15 16:00:00', 5000000.00, 'Completed');

INSERT INTO `order_details` (`order_id`, `material_id`, `quantity`, `unit_price`) VALUES 
(1, 1, 2, 1500000.00),
(2, 2, 20, 25000.00),
(3, 3, 20, 80000.00),
(4, 4, 30, 120000.00),
(5, 5, 1, 5000000.00);