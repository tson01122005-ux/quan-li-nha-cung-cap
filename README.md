# Ứng dụng Quản lý Nhà cung cấp

Ứng dụng PHP thuần này quản lý:
- Nhà cung cấp
- Sản phẩm
- Phiếu nhập hàng
- Tài khoản (Admin / Staff)
- Thống kê nhập hàng theo tháng

## Hướng dẫn cài đặt

1. Import `final_ltweb.sql` vào MySQL trong XAMPP.
2. Đảm bảo tên database là `nhà cung cấp` hoặc sửa `DB_NAME` trong `config.php`.
3. Copy folder vào `htdocs` của XAMPP.
4. Mở trình duyệt truy cập `http://localhost/quan li nha cung cap/login.php`.

## Tài khoản mặc định

- Admin: `admin` / `123456`
- Staff: `staff1` / `password`, `staff2` / `password`

## Quyền truy cập

- **Admin**: Toàn quyền quản lý nhà cung cấp, sản phẩm, phiếu nhập, tài khoản.
- **Staff**: Có thể xem, thêm nhà cung cấp, sản phẩm, phiếu nhập. Không thể sửa/xóa hoặc quản lý tài khoản.

## Lưu ý

Nếu cần chỉnh sửa tên database thành ASCII, sửa `DB_NAME` trong `config.php`.
