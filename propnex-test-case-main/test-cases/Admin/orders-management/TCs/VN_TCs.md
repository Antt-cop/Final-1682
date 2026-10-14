# Test cases — Quản lý Đơn hàng & Hóa đơn (Admin Orders)

**Phân hệ:** Admin  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh tính năng hiển thị danh sách đơn hàng, lọc theo trạng thái, cập nhật trạng thái đơn, đồng bộ GHN và xem hóa đơn.

## Phạm vi (Scope)

- Trong phạm vi:
  - Bảng danh sách đơn hàng tại `/admin/orders.php`.
  - Bộ lọc trạng thái đơn hàng (Tất cả, Chờ xác nhận, Đã xác nhận, Đang giao, Đã giao, Đã hủy).
  - Modal hoặc trang chi tiết đơn hàng.
  - Cập nhật trạng thái đơn hàng và gửi đơn sang GHN.
  - Mẫu in hóa đơn bán hàng tại `/admin/invoice.php`.
- Ngoài phạm vi:
  - Xuất báo cáo tài chính excel tổng hợp năm (thuộc `/admin/reports.php`).

## Ma trận Viewport

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Sidebar quản trị cố định bên trái, bảng đơn hàng trải rộng đầy đủ cột |
| Mobile | Có | Nút toggle menu quản trị, bảng đơn hàng hỗ trợ vuốt cuộn ngang |

## Tiền điều kiện (Preconditions)

- [ ] Web server chạy tại `http://localhost/dongkhoistore`.
- [ ] Đã đăng nhập tài khoản Quản trị viên tại `/admin/`.
- [ ] Có ít nhất 3 đơn hàng với các trạng thái khác nhau trong hệ thống.

## Test cases

### 1) Hiển thị Bảng danh sách đơn hàng

- [ ] Xác minh rằng bảng danh sách đơn hàng hiển thị đầy đủ các cột: Mã đơn hàng, Tên khách hàng, Số điện thoại, Tổng tiền, Phương thức thanh toán, Trạng thái, Ngày đặt, Thao tác.
- [ ] Xác minh rằng nhãn trạng thái (Status badge) được hiển thị màu sắc tương ứng (Chờ xác nhận: Vàng, Đã xác nhận: Xanh dương, Đã giao: Xanh lá, Đã hủy: Đỏ).

### 2) Bộ lọc trạng thái đơn hàng

- [ ] Xác minh rằng chọn tab "Chờ xác nhận" chỉ lọc ra các đơn hàng đang ở trạng thái Pending.
- [ ] Xác minh rằng chọn tab "Đã giao" chỉ lọc ra các đơn hàng đã hoàn tất thành công.
- [ ] Xác minh rằng số lượng đếm trên từng tab trạng thái khớp với số lượng đơn hàng thực tế.

### 3) Xem chi tiết đơn hàng & Cập nhật trạng thái

- [ ] Xác minh rằng bấm nút "Xem chi tiết" mở modal hiển thị danh sách từng món hàng đã mua, số lượng, đơn giá, địa chỉ giao hàng và phí vận chuyển GHN.
- [ ] Xác minh rằng quản trị viên có thể đổi trạng thái đơn hàng từ "Chờ xác nhận" sang "Đã xác nhận".
- [ ] Xác minh rằng hệ thống hiển thị thông báo cập nhật thành công và danh sách đơn hàng được làm mới ngay lập tức.

### 4) Thao tác Đẩy đơn GHN & In Hóa đơn

- [ ] Xác minh rằng đối với đơn hàng đủ điều kiện, nút "Đẩy đơn GHN" cho phép gửi thông tin tạo đơn vận chuyển sang GHN và nhận về mã tracking.
- [ ] Xác minh rằng bấm nút "In hóa đơn" mở trang hóa đơn (`/admin/invoice.php?order_id={id}`) với giao diện chuẩn in ấn và nút Print.
- [ ] Xác minh rằng thông tin cửa hàng, thông tin khách hàng và bảng tiền hàng trên hóa đơn trùng khớp với chi tiết đơn.
