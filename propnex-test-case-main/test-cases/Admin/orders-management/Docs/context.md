# Ngữ cảnh kiểm thử — Quản trị Đơn hàng & Hóa đơn (Admin Orders)

## 1. Mô tả phân hệ (Module Description)
Phân hệ Quản trị đơn hàng dành cho Quản trị viên tại `/admin/orders.php`:
1. Quản trị viên đăng nhập vào hệ thống quản trị Dong Khoi Store (`/admin/`).
2. Xem danh sách toàn bộ các đơn hàng do khách đặt từ Storefront.
3. Bộ lọc theo trạng thái:
   - Tất cả (All)
   - Chờ xác nhận (Pending)
   - Đã xác nhận (Confirmed)
   - Đang giao hàng (Shipping)
   - Đã giao thành công (Delivered)
   - Đã hủy (Cancelled)
4. Thao tác trên từng đơn hàng:
   - Xem chi tiết đơn hàng (Sản phẩm, Khách hàng, Địa chỉ giao hàng, Phí ship GHN, Tổng tiền, Phương thức thanh toán).
   - Cập nhật trạng thái đơn hàng.
   - Bấm nút "Đẩy đơn GHN" để gửi thông tin đơn hàng sang cổng đối tác vận chuyển Giao Hàng Nhanh.
   - Bấm nút "In hóa đơn" để xem và in hóa đơn bán hàng tại `/admin/invoice.php?order_id={id}`.

## 2. Tiền điều kiện dữ liệu (Data Preconditions)
- Có ít nhất một đơn hàng ở trạng thái "Chờ xác nhận" (Pending) trong cơ sở dữ liệu.
- Tài khoản quản trị viên có quyền truy cập hợp lệ vào khu vực `/admin/`.
